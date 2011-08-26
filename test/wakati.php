<?php
require_once '../lib/Igo.php';

$encode = "UTF-8";
mb_http_output($encode);
mb_internal_encoding($encode);
$igo = new Igo("C:/Users/hirai/ipadic");
$result = $igo->wakati("すもももももももものうち");
print_r($result);
echo memory_get_peak_usage(), "\n";
?>
