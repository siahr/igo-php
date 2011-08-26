<?php
require_once '../lib/Igo.php';

$igo = new Igo("C:/Users/hirai/ipadic", "UTF-8");
$result = $igo->parse("english context.");
print_r($result);
?>
