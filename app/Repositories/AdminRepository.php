<?php
namespace App\Repositories;
use App\Core\Database;use PDOException;
class AdminRepository
{
    public function counts(): array
    {
        $tables=['users','children','doctors','appointments','support_messages','articles','ai_messages'];$out=[];
        foreach($tables as $t){try{$out[$t]=(int)Database::connection()->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();}catch(PDOException $e){$out[$t]=0;}}
        return $out;
    }
    public function recentSupport(): array{try{return Database::connection()->query('SELECT * FROM support_messages ORDER BY id DESC LIMIT 10')->fetchAll();}catch(PDOException $e){return [];}}
    public function recentAppointments(): array{try{return Database::connection()->query('SELECT a.*,u.phone,d.full_name AS doctor_name FROM appointments a JOIN users u ON u.id=a.user_id JOIN doctors d ON d.id=a.doctor_id ORDER BY a.id DESC LIMIT 10')->fetchAll();}catch(PDOException $e){return [];}}
}
