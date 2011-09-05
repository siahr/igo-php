<?php
class KeyStream {
	private $s;
	private $cur;

	public function __construct($key, $start = 0) {
		$this->s = $key;
		$this->cur = $start;
	}

	public function startsWith($prefix, $beg, $len) {
		if (count($this->s) - $this->cur < $len) {
			return false;
		}

		for ($i = 0; $i < $len; $i++) {
			if ($this->s[$this->cur + $i] !== $prefix[$beg + $i]) {
				return false;
			}
		}
		return true;
	}

	public function read() {
		$c = $this->eos() ? 0 : $this->s[$this->cur++];
		return $c;
	}

	public function eos() {
		return $this->cur === count($this->s);
	}

}

?>
