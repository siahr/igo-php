<?php

interface CharArray {
	function get($idx);
}

class CharMemoryArray extends IntMemoryArray implements CharArray {
	public function __construct(&$reader, $count) {
		$this->array = $reader->getCharArray($count);
	}
	public function get($idx) {
		$i = ($idx * 2) + 1;
		if ($this->array[$i] == 0 && $this->array[$i + 1] == 0) {
			$tmp = null;
		} else {
			$tmp = pack('C*', $this->array[$i], $this->array[$i + 1]);
		}
		return $tmp;
	}
}

class CharDynamicArray extends IntDynamicArray implements CharArray {

	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("C*", fread($this->fp, 2));
		if ($data[1] == 0 && $data[2] == 0) {
			$tmp = null;
		} else {
			$tmp = pack('C*', $data[1], $data[2]);
		}
		return $tmp;
	}
}
?>
