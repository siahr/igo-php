<?php

/**
 * 未知語の検索を行うクラス
 */
class Unknown {
	private $category; // 文字カテゴリ管理クラス
	private $spaceId; // 文字カテゴリがSPACEの文字のID

	public function __construct($dataDir) {
		$this->category = new CharCategory($dataDir);
		$this->spaceId = $this->category->category(32)->id; // NOTE: ' 'の文字カテゴリはSPACEに予約されている

	}

	public function search($text, $start, $wdic, $fn) {
		$ch = $text[$start];
		$ct = $this->category->category($ch);

		if ($fn->isEmpty() === false && $ct->invoke === false) {
			return;
		}

		$isSpace = $ct->id === $this->spaceId;
		$limit = min(count($text), $ct->length + $start);
		$i = $start;
		for (; $i < $limit; $i++) {
			$wdic->searchFromTrieId($ct->id, $start, ($i - $start) + 1, $isSpace, $fn);
			if ($i + 1 !== $limit && $this->category->isCompatible($ch, $text[$i + 1]) === false) {
				return;
			}
		}

		if ($ct->group && $i < count($text)) {
			$limit = count($text);
			for (; $i < $limit; $i++)
				if ($this->category->isCompatible($ch, $text[$i]) === false) {
					$wdic->searchFromTrieId($ct->id, $start, $i - $start, $isSpace, $fn);
					return;
				}
			$wdic->searchFromTrieId($ct->id, $start, count($text) - $start, $isSpace, $fn);
		}
	}
}
?>
