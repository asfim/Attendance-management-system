<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GradeRule;

class GradeRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            ['grade' => 'A+', 'point' => 5.00, 'min_percent' => 80, 'max_percent' => 100],
            ['grade' => 'A',  'point' => 4.00, 'min_percent' => 70, 'max_percent' => 79],
            ['grade' => 'A-', 'point' => 3.50, 'min_percent' => 60, 'max_percent' => 69],
            ['grade' => 'B',  'point' => 3.00, 'min_percent' => 50, 'max_percent' => 59],
            ['grade' => 'C',  'point' => 2.00, 'min_percent' => 40, 'max_percent' => 49],
            ['grade' => 'D',  'point' => 1.00, 'min_percent' => 33, 'max_percent' => 39],
            ['grade' => 'F',  'point' => 0.00, 'min_percent' => 0,  'max_percent' => 32],
        ];

        foreach ($rules as $rule) {
            GradeRule::firstOrCreate(
                ['grade' => $rule['grade']],
                $rule
            );
        }
    }
}
