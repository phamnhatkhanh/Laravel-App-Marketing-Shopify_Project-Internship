<?php

namespace App\Jobs;

use App\Events\Database\CreatedModel;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class createDataStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $store, $access_token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store, $access_token)
    {
        $this->store = $store;
        $this->access_token = $access_token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $store = $this->store;
        $access_token = $this->access_token;
        $storeModel = new Store();

        $password = $store['shop']['myshopify_domain'];

        if ($password == "") {
            return false;
        }

        $storeData = [
            "password" => bcrypt($password),
        ];

        data_set($store, '*.password', $storeData);

        $created_at = str_replace(array('T', '+07:00'), array(' ', ''), $store['shop']['created_at']);
        $updated_at = str_replace(array('T', '+07:00'), array(' ', ''), $store['shop']['updated_at']);

        $getData = $store['shop'];

        info("ShopifyRepository save store");
        $data = [
            'id' => $getData['id'],
            'name_merchant' => $getData['name'],
            'email' => $getData['email'],
            'password' => $getData['password']['password'],
            'phone' => $getData['phone'],
            'myshopify_domain' => $getData['myshopify_domain'],
            'domain' => $getData['domain'],
            'access_token' => $access_token,
            'address' => $getData['address1'],
            'province' => $getData['province'],
            'city' => $getData['city'],
            'zip' => $getData['zip'],
            'country_name' => $getData['country_name'],
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];

        $findStore = $storeModel->where('id', $data['id'])->first();
        if (empty($findStore)) {
            info('Save information Shop');
            $connect = ($storeModel->getConnection()->getName());
            event(new CreatedModel($connect, $data, $storeModel->getTable()));
        } else {
            info('Update information Shop');
            $findStore->update($data);
        }
    }
}
