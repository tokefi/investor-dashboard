<?php

use Illuminate\Database\Seeder;
use App\RedemptionStatus;

class RedemptionStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [ 
            ['id' => 1, 'name' => 'Pending'], 
            ['id' => 2, 'name' => 'Partial Accepted'],
            ['id' => 3, 'name' => 'Approved'],
            ['id' => 4, 'name' => 'Rejected']
        ];

        foreach ($statuses as $key => $status) {
            $redemption = new RedemptionStatus;
            $redemption->id = $status['id'];
            $redemption->name = $status['name'];
            $redemption->save();
        }
    }
}
