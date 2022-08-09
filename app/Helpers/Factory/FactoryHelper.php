<?php



if (!function_exists('getRandomModelId')) {
    function getRandomModelId(string $model)
    {
        // get model count


        $count = $model::query()->count();
        // $count = $model::all()->random()->id;

        if($count === 0){
            // if model count is 0
            // we should create a new record and retrieve the record id
            return $model::factory()->create()->id;
        }else{
            // generate random number between 1 and model count
            return $model::all()->random()->id;
        }
    }
}
