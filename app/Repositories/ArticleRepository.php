<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use DOMDocument;
use DOMXPath;

class ArticleRepository
{
    public function paginate(int $page, int $perPage, ?int $categoryId = null): array
    {
        $page=max(1,$page);$perPage=max(1,$perPage);$offset=($page-1)*$perPage;
        try{
            $where='WHERE is_active = 1';$params=[];
            if($categoryId!==null){$where.=' AND category = :category';$params['category']=$categoryId;}
            $countStmt=Database::connection()->prepare("SELECT COUNT(*) AS cnt FROM articles {$where}");$countStmt->execute($params);$total=(int)($countStmt->fetch()['cnt']??0);
            $stmt=Database::connection()->prepare("SELECT * FROM articles {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");$stmt->execute($params);$items=$stmt->fetchAll();
            foreach($items as &$item){$item=$this->sanitizeRow($item);}unset($item);
            return ['items'=>$items,'total'=>$total];
        }catch(PDOException $e){return ['items'=>[],'total'=>0];}
    }

    public function findBySlug(string $slug): ?array
    {
        try{$stmt=Database::connection()->prepare('SELECT * FROM articles WHERE slug = :slug AND is_active = 1 LIMIT 1');$stmt->execute(['slug'=>$slug]);$row=$stmt->fetch();return $row?$this->sanitizeRow($row):null;}catch(PDOException $e){return null;}
    }

    public function findRelated(int $categoryId,int $excludeId,int $limit=4): array
    {
        try{$stmt=Database::connection()->prepare('SELECT * FROM articles WHERE category = :category AND id != :exclude AND is_active = 1 ORDER BY created_at DESC LIMIT '.max(1,$limit));$stmt->execute(['category'=>$categoryId,'exclude'=>$excludeId]);$rows=$stmt->fetchAll();foreach($rows as &$row){$row=$this->sanitizeRow($row);}unset($row);return $rows;}catch(PDOException $e){return [];}
    }

    private function sanitizeRow(array $row): array
    {
        if(isset($row['content'])){$row['content']=$this->removeGahvarePromotion((string)$row['content']);}
        if(isset($row['cover_image'])&&is_string($row['cover_image'])&&str_contains($row['cover_image'],'gahvare.net')){$row['cover_image']=null;}
        return $row;
    }

    private function removeGahvarePromotion(string $html): string
    {
        if($html===''||!class_exists(DOMDocument::class)){
            $html=(string)preg_replace('/<a\b[^>]*href=["\'][^"\']*gahvare\.net[^"\']*["\'][^>]*>.*?<\/a>/isu','',$html);
            return (string)preg_replace('/[^.]{0,120}(?:اپلیکیشن|سایت)\s*گهواره[^.]{0,220}\.?/u',' ',$html);
        }
        $dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8"><div id="root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);libxml_clear_errors();libxml_use_internal_errors($previous);$xpath=new DOMXPath($dom);
        $nodes=[];
        foreach($xpath->query('//*[@href and contains(@href,"gahvare.net")] | //*[@src and contains(@src,"gahvare.net")]') as $node){$nodes[]=$node;}
        foreach($xpath->query('//*[contains(normalize-space(.),"نصب اپلیکیشن گهواره") or contains(normalize-space(.),"با اپلیکیشن گهواره")]') as $node){if(in_array(strtolower($node->nodeName),['p','div','a'],true))$nodes[]=$node;}
        foreach(array_unique($nodes,SORT_REGULAR) as $node){if($node->parentNode)$node->parentNode->removeChild($node);}
        $root=$dom->getElementById('root');if(!$root)return $html;$out='';foreach($root->childNodes as $child){$out.=$dom->saveHTML($child);}return $out;
    }
}
