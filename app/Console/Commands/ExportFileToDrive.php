<?php

namespace App\Console\Commands;

use App\Exports\CustomerExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu Backup dữ liệu mỗi ngày');

        $locationExport = 'backup/customers/';
        $dateExport = date('d-m-Y_H-i-s');
        $location = $locationExport.'customer_'.$dateExport;
        $fileNameSever = $location.'.csv';
        // Excel::store(new CustomerExport(), $fileNameSever);

        $filePath = storage_path('app/'.$fileNameSever);
        $fileData = File::get($filePath);
        $fileNameDrive = 'customer_'.$dateExport.'.csv';
        Storage::disk('google')->put($fileNameDrive,$fileData);

        Storage::delete($fileNameSever);

        $this->info('Backup dữ liệu mỗi ngày thành công. File được lưu trữ ở SendEmailExport trên GoogleDrive');

        return;
    }
}
