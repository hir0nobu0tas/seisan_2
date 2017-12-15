<?php
//-------------------------------------------------------------------
// calendar.php
// カレンダー表示
// 休日設定は[holiday.php]で行う
//
// 2006/10/
//-------------------------------------------------------------------

class calendar {

	var $wfrom;
	var $beforeandafterday;

	var $link = array();
	var $style = array();

	var $kind;
	var $bgcolor;
	var $week;
	var $holiday;
	var $holiday_name;

	/**
	 * コンストラクタ
	 *
	 * @param int $arg1
	 * @param int $arg2
	 * @return void
	 */
	function calendar($arg1 = 0, $arg2 = 0) {

		// 開始曜日（0-日曜, 6-土曜）
		$this->wfrom = $arg1;

		// 当月以外の日付を表示するかどうか（0-表示しない 1-表示する）
		$this->beforeandafterday = $arg2;

		// --- 以下、表示設定 ---
		// スタイルの設定
		//$this->style["table"] = " class=\"calendar\"";
		$this->style["table"] = " class=\"calendar\" border=1 bgcolor=\"#b0c4de\"";
		$this->style["th"] = "";
		$this->style["tr"] = "";
		$this->style["td"] = "";
		$this->style["tf"] = " class=\"tf\"";

		// 曜日に対する背景色の設定（0-平日, 1-土, 2-日祝日, 3-当月以外の平日, 4-当日）
		$this->kind = array(2, 0, 0, 0, 0, 0, 1);
		//$this->bgcolor = array("#eeeeee", "#ccffff", "#ffcccc", "#ffffff", "#ffffcc");
		$this->bgcolor = array("#eeeeee", "#ccffff", "#ffcccc", "#ffffff", "#ffff55");

		// 曜日の名前
		$this->week = array("日", "月", "火", "水", "木", "金", "土");

	}

	/**
	 * 設定された内容でカレンダーを表示します
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 */
	function show_calendar($year, $month, $day = 0) {

		// 休日の算出
		if(!isset($this->set_holiday)) $this->set_holiday($year, $month);

		// その月の開始とする数値を取得
		$from = 1;
		while(date("w", mktime(0, 0, 0, $month, $from, $year)) <> $this->wfrom) {
			$from--;
		}

		// 前月と次月の年月を取得
		list($ny, $nm, $nj) = explode("-", date("Y-n-j", mktime(0, 0, 0, $month+1, 1, $year)));
		list($by, $bm, $bj) = explode("-", date("Y-n-j", mktime(0, 0, 0, $month-1, 1, $year)));

		// 当日取得
		$arr = getdate();

		// 表示開始
		echo "<table".$this->style["table"]." summary=\"カレンダー\">\n";
		echo "<tr>\n";
		echo "<th".$this->style["th"]." colspan=\"7\">\n";
		echo $year."年".$month."月\n";
		echo "</th>\n";
		echo "</tr>\n";

		// 曜日表示
		echo "<tr".$this->style["tr"]." style=\"text-align:center\">\n";
		for($i = 0; $i < 7; $i++) {
			$wk = ($this->wfrom + $i) % 7;
			echo "<td".$this->style["td"]." bgcolor=\"".$this->bgcolor[$this->kind[$wk]]."\">".$this->week[$wk]."</td>\n";
		}
		echo "</tr>\n";

		// $dayがその月の日数を超えるまでループ
		$tday = $from;
		$mday = date("t", mktime(0, 0, 0, $month, 1, $year));
		while($tday <= $mday) {

			echo "<tr".$this->style["tr"].">\n";

			for($i = 0; $i < 7; $i++) {
				$fstyle = "";
				$wk = ($this->wfrom + $i) % 7;
				$bgcolor = $this->bgcolor[$this->kind[$wk]];

				// 当月判定
				if($tday >= 1 && $tday <= $mday) {
					if($arr["year"] == $year && $arr["mon"] == $month && $arr["mday"] == $tday) {
						// 当日
						$bgcolor = $this->bgcolor[4];
					} else if($this->holiday[$tday] == 1) {
						// 祝日
						$bgcolor = $this->bgcolor[2];
					}

					// 指定日
					if($day == $tday) {
						$fstyle = " style=\"font-weight:bold\"";
					}
				} else {
					// 当月以外の平日
					if($wk > 0 && $wk < 6) $bgcolor = $this->bgcolor[3];
				}

				echo "<td".$this->style["td"]." bgcolor=\"".$bgcolor."\"".$fstyle.">\n";
				list($lyear, $lmonth, $lday) = explode("-", date("Y-n-j", mktime(0, 0, 0, $month, $tday, $year)));

				// データ部分表示
				if(($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
					if(isset($this->link[$tday])) {
						echo "<a href=\"".$this->link[$tday]["url"]."\" title=\"".$this->link[$tday]["title"]."\">".$lday."</a>";
					} else {
						echo $lday;
					}
					echo "</td>\n";
				} else {
					echo "&nbsp;";
				}

				$tday++;
			}

			echo "</tr>\n";
		}

		//echo "<tr>\n";
		//echo "<td".$this->style["tf"]." colspan=\"7\">\n";
		//echo "本日：".$arr["year"]."年".$arr["mon"]."月".$arr["mday"]."日\n";
		//echo "</td>\n";
		//echo "</tr>\n";
		echo "</table>\n";
	}

	/**
	 * 指定された日に対してリンクを設定
	 *
	 * @param int $day
	 * @param string $url
	 * @param string $title
	 */
	function set_link($day, $url, $title) {
		$this->link[$day]["url"] = $url;
		$this->link[$day]["title"] = $title;
	}

	/**
	 * 現在設定されているリンクを全て解除
	 *
	 */
	function clear_link() {
		$this->link = array();
	}

	/**
	 * 休日計算
	 *
	 * @param int $year
	 * @param int $month
	 */
	function set_holiday($year, $month) {

		// その月の最初の月曜日が何日かを算出
		$day = 1;
		while(date("w",mktime(0 ,0 ,0 , $month, $day, $year)) <> 1) {
			$day++;
		}

		// 祝日、休日は別ファイル化
		include("holiday.php");

	}

}

?>
