<?php
require_once '../lib/Igo.php';

$encode = "UTF-8";
ini_set("memory_limit", "1073741824"); //1024^3

$text = mb_convert_encoding(file_get_contents("C:/Users/hirai/sanshodayu.txt"), $encode, "Shift_JIS");

$igo = new Igo("C:/Users/hirai/ipadic");
$bench = new benchmark();
$bench->start();
$result = $igo->parse($text);
$bench->end();
print_r("score: " . $bench->score);
print_r("\n");
$fp = fopen("C:/Users/hirai/php-igo.result", "w");
foreach ($result as $res) {
	$buf = "";
	$buf .= $res->surface;
	$buf .= ",";
	$buf .= $res->feature;
	$buf .= ",";
	$buf .= $res->start;
	$buf .= "\r\n";
	fwrite($fp, $buf);
}
fclose($fp);
echo memory_get_peak_usage(), "\n";

class benchmark {

	var $start;
	var $end;
	var $score;

	function start() {
		$this->start = $this->_now();
	}
	function end() {
		$this->end = $this->_now();
		$this->score = round($this->end - $this->start, 5);
	}
	function _now() {
		list($msec, $sec) = explode(' ', microtime());
		return ((float) $msec + (float) $sec);
	}
}
?>
