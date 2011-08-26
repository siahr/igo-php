<?php
class KeyStream {
	private $s;
	private $cur;

	public function __construct($key, $start = 0) {
		$this->s = $key;
		$this->cur = $start;
	}

	public function compareTo($ks) {
		$r = $this->rest();
		if ($r == $ks->rest()) {
			return 0;
		} elseif ($r < $ks->rest()) {
			return -1;
		} else {
			return 1;
		}
	}

	public function startsWith($prefix, $beg, $len) {
		if (self::mb_strlen($this->s) - $this->cur < $len) {
			return false;
		}

		for ($i = 0; $i < $len; $i++) {
			if (self::mb_substr($this->s, $this->cur + $i, 1) != self::mb_substr($prefix, $beg + $i, 1)) {
				return false;
			}
		}
		return true;
	}

	public function rest() {
		return self::mb_substr($this->s, $this->cur, self::mb_strlen($this->s));
	}

	public function read() {
		$c = $this->eos() ? NODE_CHECK_TERMINATE_CODE : self::mb_substr($this->s, $this->cur++, 1);
		return $c;
	}

	public function eos() {
		return $this->cur == self::mb_strlen($this->s);
	}

	public static function mb_substr($text, $s, $l) {
		return substr($text, $s * 2, $l * 2);
	}

	public static function mb_strlen($text) {
		return strlen($text) / 2;
	}

}

?>
