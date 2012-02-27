<?php

/**
 * 形態素の連接コスト表を扱うクラス
 */
class Matrix {
	private $leftSize;
	private $rightSize;
	private $matrix;

	public function __construct($dataDir) {
		$fmis = new FileMappedInputStream($dataDir . "/matrix.bin");
		$this->leftSize = $fmis->getInt();
		$this->rightSize = $fmis->getInt();
		$this->matrix = $fmis->getShortArrayInstance($this->leftSize * $this->rightSize);
		$fmis->close();
	}

	/**
	 * 形態素同士の連接コストを求める
	 */
	public function linkCost($leftId, $rightId) {
		return $this->matrix->get($rightId * $this->leftSize + $leftId);
	}

}
?>
