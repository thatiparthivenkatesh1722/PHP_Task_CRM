<?php

function sendResponse($status, $message = "Success", $data = null, $meta = null)
{
    $response = [
        "status"  => $status,
        "message" => $message,
        "data"    => $data
    ];
    if ($meta !== null) {
        $response["meta"] = $meta;
    }
    echo json_encode($response);
    exit();
}
