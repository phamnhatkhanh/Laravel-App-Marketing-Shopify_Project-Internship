<?php

if (!function_exists('formatJsonRepsone')) {
    function formatJsonRepsone($status=200,$message=[],$data=[],$err=[])
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'err' => $err
        ];
    }
}
