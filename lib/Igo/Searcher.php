<?php

class Searcher {
	private $keySetSize;
	private $base;
	private $chck;
	private $begs;
	private $lens;
	private $tail;

	public function __construct($filePath) {
		$fmis = new FileMappedInputStream($filePath);

		$nodeSz = $fmis->getInt();
		$tindSz = $fmis->getInt();
		$tailSz = $fmis->getInt();

		$this->keySetSize = $tindSz;
		$this->begs = $fmis->getIntArrayInstance($tindSz);
		$this->base = $fmis->getIntArrayInstance($nodeSz);
		$this->lens = $fmis->getShortArrayInstance($tindSz);
		$this->chck = $fmis->getCharArrayInstance($nodeSz);
		$this->tail = $fmis->getString($tailSz);

		$fmis->close();
	}

	public function size() {
		return $this->keySetSize;
	}
	public static function ID($id) {
		return $id * -1 - 1;
	}

	public function eachCommonPrefix($key, $start, $fn) {
		$node = $this->base->get(0);
		$offset = 0;
		$in = new KeyStream($key, $start);

		for ($code = $in->read();; $code = $in->read(), $offset++) {
			$terminalIdx = $node;
			$c = $this->chck->get($terminalIdx);
			if (empty($c)) {
				$fn->call($start, $offset, self::ID($this->base->get($terminalIdx)));
				if (empty($code)) {
					return;
				}
			}

			$idx = $node + $this->codePoint($code);
			$node = $this->base->get($idx);
			$c = $this->chck->get($idx);
			if ($c == $code) {
				if ($node >= 0) {
					continue;
				} else {
					$this->call_if_keyIncluding($in, $node, $start, $offset, $fn);
				}
			}
			return;
		}
	}

	public static function codePoint($str) {
		$s = unpack("S*", $str);
		if (empty($s[1])) {
			$n = 32;
		} else {
			$n = $s[1];
		}

		return $n;
	}

	private function call_if_keyIncluding($in, $node, $start, $offset, $fn) {
		$id = self::ID($node);
		if ($in->startsWith($this->tail, $this->begs->get($id), $this->lens->get($id))) {
			$fn->call($start, $offset + $this->lens->get($id) + 1, $id);
		}
	}

}
?>
