<?php

class CharCategory {
	private $categories;
	private $char2id;
	private $eqlMasks;

	public function __construct($dataDir) {
		$this->categories = $this->readCategorys($dataDir);

		$fmis = new FileMappedInputStream($dataDir . "/code2category");
		$this->char2id = $fmis->getIntArrayInstance($fmis->size() / 4 / 2);
		$this->eqlMasks = $fmis->getIntArrayInstance($fmis->size() / 4 / 2);
		$fmis->close();
	}

	public function category($code) {
		return $this->categories[$this->char2id->get($code)];
	}

	public function isCompatible($code1, $code2) {
		return ($this->eqlMasks->get($code1) & $this->eqlMasks->get($code2)) != 0;
	}

	private function readCategorys($dataDir) {
		$data = FileMappedInputStream::_getIntArray($dataDir . "/char.category");
		$size = count($data) / 4;

		$ary = array();
		for ($i = 0; $i < $size; $i++) {
			$ary[$i] = new Category($data[$i * 4], $data[$i * 4 + 1], $data[$i * 4 + 2] == 1, $data[$i * 4 + 3] == 1);
		}
		return $ary;
	}
}

class Category {
	public $id;
	public $length;
	public $invoke;
	public $group;

	public function __construct($i, $l, $iv, $g) {
		$this->id = $i;
		$this->length = $l;
		$this->invoke = $iv;
		$this->group = $g;
	}
}

?>
