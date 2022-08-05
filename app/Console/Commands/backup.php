<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $exportFile =  Customer::orderBy('id')->chunk(50,function ($customer, $i = 1){
            $filename = 'backup/'.date('d-m-Y H-i-s')." - product".$i.".csv";
            $handle = fopen($filename, 'w');
            fputcsv($handle, array('id', 'name', 'last name'));
            foreach($customer as $item){
                fputcsv($handle, array($item->id, $item->first_name, $item->last_name));
            }
            fclose($handle);
            $headers = array(
                'Content-Type' => 'text/csv',
            );
            $i++;
        });
        $this->info('Backup dữ liệu thành công!');
    }
}
