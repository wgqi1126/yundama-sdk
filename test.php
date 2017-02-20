<?php
/**
 * User: echo
 * Date: 17/2/17
 * Time: 下午6:57
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

$sdk = new \wgqi1126\YunDaMa\YunDaMa(USERNAME, PASSWORD, APP_ID, APP_KEY);

$uid = $sdk->login();
echo "uid: ";
var_dump($uid);

$balance = $sdk->balance();
echo "balance: ";
var_dump($balance);

$file = __DIR__ . '/demo.gif';
$timeout = 60;
$result = $sdk->upload($file, 4004, $timeout);
echo "upload-result: ";
var_dump($result);

if (!$result->text) {
    $cid = $result->cid;
    for ($i = 0; $i < $timeout; $i++) {
        sleep(1);
        try {
            $result = $sdk->result($cid);
        } catch (Exception $e) {
            if ($e->getCode() == -3002) {
                echo "decoding...\n";
                continue;
            } else {
                throw $e;
            }
        }
        echo "result: ";
        var_dump($result);
        if ($result->text) {
            echo "Done\n";
            break;
        }
    }
}
