<?php

class FileMappedInputStream {

	private $cur;
	private $file;
	private $fileName;

	public function __construct($fileName) {
		$this->cur = 0;
		$this->file = fopen($fileName, "rb");
		if (!$this->file) {
			die("dictionary reading failed.");
		}
		$this->fileName = $fileName;
	}

	public function getInt() {
		$this->cur += 4;
		$data = unpack("l*", fread($this->file, 4));
		return $data[1];
	}

	public function getIntArray($count) {
		$this->cur += ($count * 4);
		return array_values(unpack("l*", fread($this->file, $count * 4)));
	}

	public function getIntArrayInstance($count) {
		if (Tagger::$REDUCE) {
			$i = new IntDynamicArray($this->fileName, $this->cur);
			fseek($this->file, $this->cur + $count * 4);
			$this->cur += ($count * 4);
		} else {
			$i = new IntMemoryArray($this, $count);
		}
		return $i;
	}

	public static function _getIntArray($fileName) {
		$fmis = new FileMappedInputStream($fileName);
		$array = $fmis->getIntArray($fmis->size() / 4);
		$fmis->close();
		return $array;
	}

	public function getShortArray($count) {
		$this->cur += ($count * 2);
		return array_values(unpack("s*", fread($this->file, $count * 2)));
	}

	public function getShortArrayInstance($count) {
		if (Tagger::$REDUCE) {
			$s = new ShortDynamicArray($this->fileName, $this->cur);
			fseek($this->file, $this->cur + $count * 2);
			$this->cur += ($count * 2);
		} else {
			$s = new ShortMemoryArray($this, $count);
		}
		return $s;
	}

	public function getCharArrayInstance($count) {
		if (Tagger::$REDUCE) {
			$c = new CharDynamicArray($this->fileName, $this->cur);
			fseek($this->file, $this->cur + $count * 2);
			$this->cur += ($count * 2);
		} else {
			$c = new CharMemoryArray($this, $count);
		}
		return $c;
	}

	public function getCharArray($count) {
		$this->cur += ($count * 2);
		$data = array_values(unpack("S*", fread($this->file, $count * 2)));
		return $data;
	}

	public function getString($count) {
		return fread($this->file, $count * 2);
	}

	public static function _getString($fileName) {
		$fmis = new FileMappedInputStream($fileName);
		$str = $fmis->getString($fmis->size() / 2);
		$fmis->close();
		return $str;
	}

	public function size() {
		return filesize($this->fileName);
	}

	public function close() {
		return fclose($this->file);
	}
}
?>
