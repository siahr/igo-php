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
		$data = unpack("i*", fread($this->file, 4));
		return $data[1];
	}

	public function getIntArray($count) {
		$this->cur += ($count * 4);
		return unpack("i*", fread($this->file, $count * 4));
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
		return unpack("s*", fread($this->file, $count * 2));
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
		$data = unpack("C*", fread($this->file, $count * 2));
		if (EACH_CONVERT_WORD2ID) {
			return $data;
		} else {
			$tmp = array();
			$j = SO;
			for ($i = 1; $i <= count($data); $i++) {
				if ($i % 2 != 0) {
					if ($data[$i + 1] == 0 && $data[$i] == 0) {
						$tmp[$j++] = null;
					} else {
						$tmp[$j++] = mb_convert_encoding(pack('C*', $data[$i], $data[$i + 1]), Igo::$ENCODE, IGO_DICTIONARY_ENCODING);
					}
				}
			}
			return $tmp;
		}
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
