<?php
require_once '../lib/Igo.php';

$igo = new Igo("C:/Users/hirai/ipadic", "UTF-8");
$result = $igo->parse("すもももももももものうち");
print_r($result);
?>
