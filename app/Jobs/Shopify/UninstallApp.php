<?php

namespace App\Jobs\Shopify;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UninstallApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Uninstall App will update status Store into uninstalled
     *
     * @return void
     */
    public function handle()
    {
        $storeModel = getConnectDatabaseActived(new Store());
        $store = $storeModel->getModel();

        $status = 'uninstalled';

        data_set($store, '*.status', $status);

        $data = [
            'status' => $status,
        ];

        $findStore = $store->where('id', $this->payload['id'])->first();
        if (!empty($findStore)){
            $findStore->update($data);
            $connect = ($findStore->getConnection()->getName());
            SyncDatabaseAfterUpdatedModel($connect,$findStore);
        }
    }
}
