<?php
/**
 * 形態素クラス
 */
class Morpheme {
	/**
	 * 形態素の表層形
	 */
	public $surface;
	/**
	 * 形態素の素性
	 */
	public $feature;
	/**
	 * テキスト内での形態素の出現開始位置
	 */
	public $start;

	public function __construct($surface, $feature, $start) {
		$this->surface = $surface;
		$this->feature = $feature;
		$this->start = $start;
	}
}
?>
