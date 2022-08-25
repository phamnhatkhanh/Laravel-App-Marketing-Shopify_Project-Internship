<?php

use Illuminate\Support\Facades\Route;
use App\Models\Customer;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('showNotification');
});

Route::get('/ha', function () {
    return Customer::factory()->times(2)->create([
        'id'=> getUniqueId(Customer::class),
        'store_id'=>1]
    );

    return getUniqueId(Customer::class);
    $customerModelBuilder = setConnectDatabaseActived(new Customer());
        $customerModel = $customerModelBuilder->getModel();

        $idMax = $customerModelBuilder->whereRaw('id = (select max(`id`) from '.$customerModelBuilder->getModel()->getTable().')')->first();

        if(is_null($idMax)){
            return 1;
        }else{

            return $idMax->id +1;
        }

        // info("id_customer " . $customerModel->max('id'));
});





