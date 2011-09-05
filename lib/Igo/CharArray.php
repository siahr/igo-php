<?php

interface CharArray {
	function get($idx);
}

class CharMemoryArray extends IntMemoryArray implements CharArray {
	public function __construct(&$reader, $count) {
		$this->array = $reader->getCharArray($count);
	}

	public function get($idx) {
		return parent::get($idx);
	}
}

class CharDynamicArray extends IntDynamicArray implements CharArray {
	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("S*", fread($this->fp, 2));
		return $data[1];
	}
}
?>
