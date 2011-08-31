<?php
/*
 * Igo-php : A morphological analyzer. (http://sourceforge.jp/projects/igo-php/)
 * Copyright 2011, Toshio HIRAI. <toshio.hirai@gmail.com>
 * (This software is based on Igo Java Version (c) Takeru Ohta <phjgt308@gmail.com>)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * Contributors:
 *  Takeru Ohta <phjgt308@gmail.com> - original idea
 *  Toshio HIRAI <toshio.hirai@gmail.com> - initial implementation
 *
 */
require_once 'Igo/Tagger.php';

define('IGO_REDUCE_MODE', true);
define('IGO_LITTLE_ENDIAN', true);
define('IGO_MB_DETECT_ORDER', "ASCII,JIS,UTF-8,EUC-JP,SJIS");

if (isset($argc) && $argc > 1) {
	$dataDir = $argv[1];
	if (!is_dir($dataDir)) {
		die('dictionary not found.');
	}
	$text = $argv[2];
	if (is_file($text)) {
		$text = file_get_contents($text);
	}

	$enc = mb_detect_encoding($text, IGO_MB_DETECT_ORDER);
	mb_internal_encoding($enc);
	if ($e = getenv("IGO_OUTPUT_ENCODING")) {
		$enc = $e;
	}
	mb_http_output($enc);

	$igo = new Igo($dataDir, $enc);
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
	private $tagger;

	public function __construct($dataDir, $outputEncoding = null) {
		$this->tagger = new Tagger($dataDir, $outputEncoding);
	}

	public function wakati($text) {
		return $this->tagger->wakati($text);
	}

	public function parse($text) {
		return $this->tagger->parse($text);
	}
}
?>
