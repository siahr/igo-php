<?php
require_once '../lib/Igo.php';

$encode = "UTF-8";
mb_http_output($encode);
mb_internal_encoding($encode);
$igo = new Igo("C:/Users/hirai/ipadic");
$result = $igo->parse("すもももももももものうち");
print_r($result);
?>
