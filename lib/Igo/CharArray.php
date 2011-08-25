<?php

interface CharArray {
	function get($idx);
}

class CharMemoryArray extends IntMemoryArray implements CharArray {
	public function __construct(&$reader, $count) {
		$this->array = $reader->getCharArray($count);
	}
	public function get($idx) {
		if (EACH_CONVERT_WORD2ID) {
			$i = ($idx * 2) + 1;
			if ($this->array[$i] == 0 && $this->array[$i + 1] == 0) {
				$tmp = null;
			} else {
				$tmp = mb_convert_encoding(pack('C*', $this->array[$i], $this->array[$i + 1]), Igo::$ENCODE, IGO_DICTIONARY_ENCODING);
			}
			return $tmp;
		} else {
			return parent::get($idx);
		}
	}
}

class CharDynamicArray extends IntDynamicArray implements CharArray {

	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("C*", fread($this->fp, 2));
		if ($data[1] == 0 && $data[2] == 0) {
			$tmp = null;
		} else {
			$tmp = mb_convert_encoding(pack('C*', $data[1], $data[2]), Igo::$ENCODE, IGO_DICTIONARY_ENCODING);
		}
		return $tmp;
	}
}
?>
