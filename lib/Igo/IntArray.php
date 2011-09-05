<?php
interface IntArray {
	function get($idx);
}

class IntMemoryArray implements IntArray {
	protected $array;

	public function __construct(&$reader, $count) {
		$this->array = $reader->getIntArray($count);
	}

	public function get($idx) {
		return $this->array[$idx];
	}
}

class IntDynamicArray implements IntArray {
	protected $start;
	protected $fileName;
	protected $fp;

	public function __construct($fileName, $start) {
		$this->fileName = $fileName;
		$this->start = $start;
		$this->fp = fopen($this->fileName, "rb");
	}
	public function __destruct() {
		fclose($this->fp);
	}

	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 4));
		$data = unpack("l*", fread($this->fp, 4));
		return $data[1];
	}
}

?>
