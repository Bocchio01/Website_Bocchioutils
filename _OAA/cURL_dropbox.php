<?php

include_once "../_setting.php";


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token=bJ2MTUHoO2oAAAAAAAAAAW4V8a-Rkf727c6PnPE2BJzaHfOFknZxZsLYR7Vx1fW8");
curl_setopt($ch, CURLOPT_USERPWD, 'e0ia56nopu9a99z' . ':' . 'gx5kpi2bls59f4z');

$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

$result = json_decode($result, true);
print_r($result);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

$headers = array();
$headers[] = 'Authorization: Bearer ' . $result['access_token'];
$headers[] = 'Dropbox-API-Arg: {"path":"/mapData.json"}';
$headers[] = 'Content-Type: text/plain';

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
curl_close($ch);

var_dump($result);
