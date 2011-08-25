<?php
/*
 * Igo-php : A morphological analyzer. (http://igo-php.sourceforge.jp/)
 * Copyright 2011, Infinite Corporation. (http://www.infinite.jp)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * Contributors:
 *   Toshio HIRAI <toshio.hirai@gmail.com> - initial implementation
 *
 */
require_once 'Igo/Tagger.php';

define('IGO_REDUCE_MODE', true);
define('IGO_DICTIONARY_ENCODING', "UTF-16LE");

if ($argc > 1) {
	$outputEnc = getenv("IGO_OUTPUT_ENCODING");
	if ($outputEnc) {
		mb_http_output($outputEnc);
	}
	$dataDir = $argv[1];
	$text = $argv[2];
	if (is_file($text)) {
		$text = file_get_contents($text);
	}
	$enc = mb_detect_encoding($text);
	$text = mb_convert_encoding($text, mb_internal_encoding(), $enc);

	$igo = new Igo($dataDir);
	$result = $igo->parse($text);
	foreach($result as $res) {
		$buf = "";
		$buf .= $res->surface;
		$buf .= "\t";
		$buf .= $res->feature;
		$buf .= ",";
		$buf .= $res->start;
		$buf .= PHP_EOL;
		echo $buf;
	}
}

class Igo {
	public static $ENCODE;
	private $tagger;

	public function __construct($dataDir) {
		self::$ENCODE = mb_internal_encoding();
		$this->tagger = new Tagger($dataDir);
	}

	public function wakati($text) {
		return $this->tagger->wakati($text);
	}

	public function parse($text) {
		return $this->tagger->parse($text);
	}

}
?>
