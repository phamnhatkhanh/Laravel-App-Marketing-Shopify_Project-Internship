<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

	public function register()
	{

        $this->app->bind(
            'App\Repositories\Contracts\ProductRepositoryInterface',
            'App\Repositories\Eloquents\ProductRepository',

            'App\Repositories\Contracts\CampaignRepositoryInterface',
            'App\Repositories\Eloquents\CampaignRepository',

            'App\Repositories\Contracts\CustomerRepositoryInterface',
            'App\Repositories\Eloquents\CustomerRepository',

            'App\Repositories\Contracts\ShopifyRepositoryInterface',
            'App\Repositories\Eloquents\ShopifyRepository',

            'App\Repositories\Contracts\WebHookRepositoryInterface',
            'App\Repositories\Eloquents\WebHookRepository',

        );

	}
}
