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
		if (self::mb_strlen($this->s, Igo::$ENCODE) - $this->cur < $len) {
			return false;
		}

		for ($i = 0; $i < $len; $i++) {
			if (self::mb_substr($this->s, $this->cur + $i, 1, Igo::$ENCODE) != self::mb_substr($prefix, $beg + $i, 1, Igo::$ENCODE)) {
				return false;
			}
		}
		return true;
	}

	public function rest() {
		return self::mb_substr($this->s, $this->cur, self::mb_strlen($this->s, Igo::$ENCODE), Igo::$ENCODE);
	}

	public function read() {
		$c = $this->eos() ? NODE_CHECK_TERMINATE_CODE : self::mb_substr($this->s, $this->cur++, 1, Igo::$ENCODE);
		return $c;
	}

	public function eos() {
		return $this->cur == self::mb_strlen($this->s, Igo::$ENCODE);
	}

	public static function mb_substr($text, $s, $l, $encode) {
		$str = substr($text, $s * 2, $l * 2);
		return mb_convert_encoding($str, $encode, IGO_DICTIONARY_ENCODING);
	}

	public static function mb_strlen($text, $encode) {
		return strlen($text) / 2;
	}

}

?>
