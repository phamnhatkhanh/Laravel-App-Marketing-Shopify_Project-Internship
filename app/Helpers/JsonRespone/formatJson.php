<?php


namespace App\Helpers\JsonRespone;


// use Illuminate\Database\Eloquent\Factories\HasFactory;

class formatJson
{
    /**
     * This function will get a random model id from the database.
     * @param string | HasFactory $model
     */
    public static function format($status=200,$message=[],$data=[],$err=[])
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'err' => $err
        ];
    }
}
