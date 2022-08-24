<?php

namespace App\Console\Commands;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Customer;
use App\Services\Customers\CustomerService;

class ExportFileToDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'exportFileToDrive';

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
     * Upload File Backup Data Customers to Google Drive
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu Backup dữ liệu mỗi ngày');

//        $storeID = GetStoreID();
        $locationExport = storage_path('app/backup/customers/');
        $dateExport = date('d-m-Y_H-i-s');
        $location = $locationExport.'customer_'.$dateExport;
        $fileName = $location.'.csv';

        $getCustomer = Customer::get();
        CustomerService::exportCustomer($fileName, $getCustomer);

        $fileData = File::get($fileName);
        $fileNameDrive = 'customer_'.$dateExport.'.csv';
        Storage::disk('google')->put($fileNameDrive,$fileData);

        unlink($fileName);

        $this->info('Backup dữ liệu mỗi ngày thành công. File được lưu trữ ở SendEmailExport trên GoogleDrive');

        return;
    }
}
