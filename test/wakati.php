<?php
require_once '../lib/Igo.php';

$igo = new Igo("C:/Users/hirai/ipadic");
$result = $igo->wakati("すもももももももものうち");
print_r($result);
echo memory_get_peak_usage(), "\n";
?>
