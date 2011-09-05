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
		$this->tail = array_values(unpack("S*", $fmis->getString($tailSz)));

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
			if ($this->chck->get($terminalIdx) === 0) {
				$fn->call($start, $offset, self::ID($this->base->get($terminalIdx)));
				if ($code === 0) {
					return;
				}
			}

			$idx = $node + $code;
			$node = $this->base->get($idx);
			if ($this->chck->get($idx) === $code) {
				if ($node >= 0) {
					continue;
				} else {
					$this->call_if_keyIncluding($in, $node, $start, $offset, $fn);
				}
			}
			return;
		}
	}

	private function call_if_keyIncluding($in, $node, $start, $offset, $fn) {
		$id = self::ID($node);
		if ($in->startsWith($this->tail, $this->begs->get($id), $this->lens->get($id))) {
			$fn->call($start, $offset + $this->lens->get($id) + 1, $id);
		}
	}

}
?>
