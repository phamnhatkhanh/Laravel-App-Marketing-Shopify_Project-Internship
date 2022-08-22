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

        $this->info("Create and setup database...");
        $listDBConnectionMysql = config('database.connections');
        foreach ($listDBConnectionMysql as $key => $value) {
            DbStatus::create(['name' => $key, 'status' => 'actived']);
        }

        $path = app_path() . "/Models";
        $listPathModels = getListModels($path);

        foreach ($listPathModels as $pathModel) {
            $model = new $pathModel();
            $connectModelDefault = getDiverDafault($model);
            if ($connectModelDefault != "mysql") {
                //  dd($connectModelDefault);
                $getListDriver =  DbStatus::where(function ($query) use ($connectModelDefault) {
                    $query->where('name', 'like', $connectModelDefault . '%')
                        ->where('model_name', '=', null);
                })->get();
                foreach ($getListDriver as $driver) {
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
