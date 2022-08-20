<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Models\DbStatus;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setupDB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and fake data table in database.';

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
        $this->info("Run Command: php artisan migrate:refresh");
        Artisan::call('migrate:refresh');

        // Artisan
        $this->info("Create and setup database...");
        $listNameConnectionMysql = config('database.connections');
        foreach ($listNameConnectionMysql as $key => $value) {
            DbStatus::create(['name' => $key, 'status' => 'actived']);
        }
        $path = app_path() . "/Models";
        $listPathModel = getListModels($path);
        // dd($listPathModel);
        foreach ($listPathModel as $pathModel) {
            $model = new $pathModel();
            // dd($model);
            $driverDefaultModel = getDiverDafault($model);
            if ($driverDefaultModel != "mysql") {
                //  dd($driverDefaultModel);
                $get_list_driver =  DbStatus::where(function ($query) use ($driverDefaultModel) {
                    $query->where('name', 'like', $driverDefaultModel . '%')
                        ->where('model_name', '=', null);
                })->get();
                // dd($get_list_driver);
                foreach ($get_list_driver as $driver) {
                    // info($driver->name);
                    if (Schema::connection($driver->name)->hasTable($model->getTable())) {
                        DbStatus::create(['name' => $driver->name, 'status' => 'actived', 'model_name' => $model->getTable()]);
                    }
                }
            }
        }
        $this->info("Faker data in database...");
        DbStatus::where('model_name', '=', null)
            ->orWhereNull('model_name')->delete();

        Artisan::call('db:seed');

        $this->info("Setup database done!!");

    }
}
