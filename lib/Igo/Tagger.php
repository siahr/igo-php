<?php
require_once 'Morpheme.php';
require_once 'ViterbiNode.php';
require_once 'WordDic.php';
require_once 'Unknown.php';
require_once 'Matrix.php';
require_once 'CharCategory.php';
require_once 'FileMappedInputStream.php';
require_once 'IntArray.php';
require_once 'ShortArray.php';
require_once 'CharArray.php';
require_once 'KeyStream.php';
require_once 'Searcher.php';

define('NODE_BASE_INIT_VALUE', ~PHP_INT_MAX);
define('NODE_CHECK_TERMINATE_CODE', null);
define('SO', 1); //special offset
define('EACH_CONVERT_WORD2ID', true);
define('IGO_RETURN_AS_ARRAY', false);

class Tagger {

	private static $BOS_NODES = array();
	public static $REDUCE = IGO_REDUCE_MODE;
	private $wdc;
	private $unk;
	private $mtx;

	/**
	 * バイナリ辞書を読み込んで、形態素解析器のインスタンスを作成する
	 *
	 * @param dataDir
	 *            バイナリ辞書があるディレクトリ
	 */
	public function __construct($dataDir) {
		self::$BOS_NODES[0] = ViterbiNode::makeBOSEOS();
		$this->wdc = new WordDic($dataDir);
		$this->unk = new Unknown($dataDir);
		$this->mtx = new Matrix($dataDir);
	}

	/**
	 * 形態素解析を行う
	 *
	 * @param text
	 *            解析対象テキスト
	 * @param result
	 *            解析結果の形態素が追加されるリスト
	 * @return 解析結果の形態素リスト. {@code parse(text,result)=result}
	 */
	public function parse($text, $result = null) {
		if ($result == null) {
			$result = array();
		}

		for ($vn = $this->parseImpl($text); $vn != null; $vn = $vn->prev) {
			$surface = mb_substr($text, $vn->start, $vn->length, Igo::$ENCODE);
			$feature = $this->wdc->wordData($vn->wordId);
			if (!IGO_RETURN_AS_ARRAY) {
				$result[] = new Morpheme($surface, $feature, $vn->start);
			} else {
				$result[] = array("surface" => $surface, "feature" => $feature, "start" => $vn->start);
			}
		}

		return $result;
	}

	/**
	 * 分かち書きを行う
	 *
	 * @param text
	 *            分かち書きされるテキスト
	 * @param result
	 *            分かち書き結果の文字列が追加されるリスト
	 * @return 分かち書きされた文字列のリスト.
	 */
	public function wakati($text, $result = null) {
		if ($result == null) {
			$result = array();
		}

		for ($vn = $this->parseImpl($text); $vn != null; $vn = $vn->prev) {
			$result[] = mb_substr($text, $vn->start, $vn->length, Igo::$ENCODE);
		}

		return $result;
	}

	private function parseImpl($text) {
		$len = mb_strlen($text, Igo::$ENCODE);
		$nodesAry[] = self::$BOS_NODES;
		$utf16 = mb_convert_encoding($text, IGO_DICTIONARY_ENCODING, Igo::$ENCODE);

		for ($i = 1; $i <= $len; $i++) {
			$nodesAry[] = array();
		}

		$fn = new MakeLattice($this, $nodesAry);
		for ($i = 0; $i < $len; $i++) {
			if (count($nodesAry[$i]) != 0) {
				$fn->set($i);
				$this->wdc->search($text, $i, $fn); // 単語辞書から形態素を検索
				$this->unk->search($utf16, $i, $this->wdc, $fn); // 未知語辞書から形態素を検索
			}
		}

		$cur = $this->setMincostNode(ViterbiNode::makeBOSEOS(), $nodesAry[$len])->prev;

		// reverse
		$head = null;
		while ($cur->prev != null) {
			$tmp = $cur->prev;
			$cur->prev = $head;
			$head = $cur;
			$cur = $tmp;
		}
		return $head;

	}
	public function setMincostNode($vn, $prevs) {
		$f = $vn->prev = $prevs[0];
		$minCost = $f->cost + $this->mtx->linkCost($f->rightId, $vn->leftId);

		for ($i = 1; $i < count($prevs); $i++) {
			$p = $prevs[$i];
			$cost = $p->cost + $this->mtx->linkCost($p->rightId, $vn->leftId);
			if ($cost < $minCost) {
				$minCost = $cost;
				$vn->prev = $p;
			}
		}
		$vn->cost += $minCost;

		return $vn;
	}
}

class MakeLattice {
	private $tagger;
	private $nodesAry;
	private $i;
	private $prevs;
	private $empty = true;

	public function __construct($tagger, &$nodesAry) {
		$this->tagger = $tagger;
		$this->nodesAry = &$nodesAry;
	}

	public function set($i) {
		$this->i = $i;
		$this->prevs = $this->nodesAry[$i];
		$this->empty = true;
	}

	public function call($vn) {
		$this->empty = false;

		if ($vn->isSpace) {
			$this->nodesAry[$this->i + $vn->length] = $this->prevs;
		} else {
			$this->nodesAry[$this->i + $vn->length][] = $this->tagger->setMincostNode($vn, $this->prevs);
		}
	}

	public function isEmpty() {
		return $this->empty;
	}
}

?>
