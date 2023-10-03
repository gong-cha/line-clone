<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, content-type, Authorization');
//header('Connection: keep-alive');
require_once 'load.php';
session_start();
$user_id = $_SESSION['user_id'];
// https://gist.github.com/yano3nora/a08b20aea47f509d8947e6bb63dcb9e3
session_write_close();
$message = DB::get_latest_message($_GET['receiver_user_id'], $user_id);
// クライアントが接続を中止したら（ページを閉じたら）ループから抜ける
while (!connection_aborted()) {
    $s = microtime(true);
    $latest_message = DB::get_latest_message($_GET['receiver_user_id'], $user_id);
    if ($latest_message && $latest_message['message_id'] > $message['message_id']) {
        $message = $latest_message;
        printf("data: %s\n\n", json_encode($message));
    }
    $e = microtime(true);
    echo "event: ping\n";
    $curDate = $e - $s;
    echo 'data: {"time": "' . $curDate . '"}';
    echo "\n\n";
    if(ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    usleep(100000);
}
