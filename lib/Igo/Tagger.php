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

define('IGO_RETURN_AS_ARRAY', false);

class Tagger {

	private static $BOS_NODES = array();
	public static $REDUCE = IGO_REDUCE_MODE;
	public static $DIC_ENC;
	private $wdc;
	private $unk;
	private $mtx;
	private $enc;
	private $outEnc;

	/**
	 * バイナリ辞書を読み込んで、形態素解析器のインスタンスを作成する
	 *
	 * @param dataDir
	 *            バイナリ辞書があるディレクトリ
	 */
	public function __construct($dataDir, $outputEncoding) {
		self::$BOS_NODES[0] = ViterbiNode::makeBOSEOS();
		$this->wdc = new WordDic($dataDir);
		$this->unk = new Unknown($dataDir);
		$this->mtx = new Matrix($dataDir);
		$this->outEnc = $outputEncoding;
		if (IGO_LITTLE_ENDIAN) {
			self::$DIC_ENC = "UTF-16LE";
		} else {
			self::$DIC_ENC = "UTF-16BE";
		}
	}

	private function getEnc() {
		if ($this->outEnc != null) {
			return $this->outEnc;
		} else {
			return $this->enc;
		}
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

		$this->enc = mb_detect_encoding($text, IGO_MB_DETECT_ORDER);
		$utf16 = mb_convert_encoding($text, self::$DIC_ENC, $this->enc);
		$source = array_values(unpack("S*", $utf16));

		for ($vn = $this->parseImpl($source); $vn != null; $vn = $vn->prev) {
			$surface = mb_convert_encoding(substr($utf16, $vn->start << 1, $vn->length << 1), $this->getEnc(), self::$DIC_ENC);
			$feature = mb_convert_encoding($this->wdc->wordData($vn->wordId), $this->getEnc(), self::$DIC_ENC);
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

		$this->enc = mb_detect_encoding($text, IGO_MB_DETECT_ORDER);
		$utf16 = mb_convert_encoding($text, self::$DIC_ENC, $this->enc);
		$source = array_values(unpack("S*", $utf16));

		for ($vn = $this->parseImpl($source); $vn != null; $vn = $vn->prev) {
			$result[] = mb_convert_encoding(substr($utf16, $vn->start << 1, $vn->length << 1), $this->getEnc(), self::$DIC_ENC);
		}

		return $result;
	}

	private function parseImpl($text) {
		$len = count($text);
		$nodesAry[] = self::$BOS_NODES;
		for ($i = 1; $i <= $len; $i++) {
			$nodesAry[] = array();
		}

		$fn = new MakeLattice($this, $nodesAry);
		for ($i = 0; $i < $len; $i++) {
			if (count($nodesAry[$i]) !== 0) {
				$fn->set($i);
				$this->wdc->search($text, $i, $fn); // 単語辞書から形態素を検索
				$this->unk->search($text, $i, $this->wdc, $fn); // 未知語辞書から形態素を検索
				unset($nodesAry[$i]);
			}
		}

		$cur = $this->setMincostNode(ViterbiNode::makeBOSEOS(), $nodesAry[$len])->prev;

		// reverse
		$head = null;
		while ($cur->prev !== null) {
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
