<?php

interface ShortArray {
	function get($idx);
}

class ShortMemoryArray extends IntMemoryArray implements ShortArray {
	public function __construct(&$reader, $count) {
		$this->array = $reader->getShortArray($count);
	}

	public function get($idx) {
		return parent::get($idx);
	}
}

class ShortDynamicArray extends IntDynamicArray implements ShortArray {
	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("s*", fread($this->fp, 2));
		return $data[1];
	}
}
?>
