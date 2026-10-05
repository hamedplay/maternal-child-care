<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class ChildCareRepository
{
    public function nutritionByAge(int $months): array { return $this->rangeRows('child_nutrition', $months, 'age_start_months', 'age_end_months'); }
    public function supplementsByAge(int $months): array { return $this->rangeRows('child_supplements', $months, 'age_start_months', 'age_end_months', true); }

    public function vaccinesByAge(int $months): array
    {
        try {
            $stmt=Database::connection()->prepare('SELECT * FROM vaccination_schedule WHERE age_min_months <= :m AND age_max_months >= :m ORDER BY age_in_months, dose_number');
            $stmt->execute(['m'=>$months]); return $stmt->fetchAll();
        } catch(PDOException $e){ return []; }
    }

    public function allVaccines(): array { return $this->all('vaccination_schedule','age_in_months, dose_number'); }
    public function allNutrition(): array { return $this->all('child_nutrition','age_start_months'); }
    public function allMilestones(): array { return $this->all('development_milestones','age_months_start, category'); }
    public function allConditions(): array { return $this->all('common_conditions','id'); }
    public function allSupplements(): array { return $this->all('child_supplements','age_start_months'); }

    private function all(string $table,string $order): array
    {
        $allowed=['vaccination_schedule','child_nutrition','development_milestones','common_conditions','child_supplements'];
        if(!in_array($table,$allowed,true)) return [];
        try{return Database::connection()->query("SELECT * FROM {$table} ORDER BY {$order}")->fetchAll();}catch(PDOException $e){return [];}
    }

    private function rangeRows(string $table,int $months,string $start,string $end,bool $nullEnd=false): array
    {
        $allowed=['child_nutrition','child_supplements']; if(!in_array($table,$allowed,true)) return [];
        try{
            $endCond=$nullEnd?"({$end} IS NULL OR {$end} >= :m2)":"{$end} >= :m2";
            $stmt=Database::connection()->prepare("SELECT * FROM {$table} WHERE {$start} <= :m1 AND {$endCond} ORDER BY {$start}");
            $stmt->execute(['m1'=>$months,'m2'=>$months]);return $stmt->fetchAll();
        }catch(PDOException $e){return [];}
    }
}
