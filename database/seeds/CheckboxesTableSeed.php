<?php
use App\Checkbox;
use Illuminate\Database\Seeder;

class CheckboxesTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Checkbox::Create(['checkbox_name'=>'Individual Investor']);
        Checkbox::Create(['checkbox_name'=>'Joint Investor']);
        Checkbox::Create(['checkbox_name'=>'Company, Trust or SMSF']);
    }
}
