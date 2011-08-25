<?php

/**
 * Viterbiアルゴリズムで使用されるノード
 */
class ViterbiNode {
	public $cost; // 始点からノードまでの総コスト
	public $prev = null; // コスト最小の前方のノードへのリンク
	public $wordId; // 単語ID
	public $leftId; // 左文脈ID
	public $rightId; // 右文脈ID
	public $start; // 入力テキスト内での形態素の開始位置
	public $length; // 形態素の表層形の長さ(文字数)
	public $isSpace; // 形態素の文字種(文字カテゴリ)が空白文字かどうか

	public function __construct($wid, $beg, $len, $wordCost, $l, $r, $space) {
		$this->wordId = $wid;
		$this->leftId = $l;
		$this->rightId = $r;
		$this->length = $len;
		$this->cost = $wordCost;
		$this->isSpace = $space;
		$this->start = $beg;
	}

	public static function makeBOSEOS() {
		return new ViterbiNode(0, 0, 0, 0, 0, 0, false);
	}

}
?>
