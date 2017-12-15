<?php
//-------------------------------------------------------------------
// Break_Time (休憩時間設定 UNIXタイム 生産計画時間計算用)
//
// 引数   $ty     = 年
//        $tm     = 月
//        $td     = 日
//
// 戻り値 return  = $break_time[]
//-------------------------------------------------------------------
function Break_Time($ty, $tm, $td) {

	$break_time = array(0815 => mktime(8, 15, 0, $tm, $td, $ty),
						1005 => mktime(10, 5, 0, $tm, $td, $ty),
						1010 => mktime(10, 10, 0, $tm, $td, $ty),
						1200 => mktime(12, 0, 0, $tm, $td, $ty),
						1245 => mktime(12, 45, 0, $tm, $td, $ty),
						1505 => mktime(15, 5, 0, $tm, $td, $ty),
						1515 => mktime(15, 15, 0, $tm, $td, $ty),
						1845 => mktime(18, 45, 0, $tm, $td, $ty),
						1915 => mktime(19, 15, 0, $tm, $td, $ty),
						2000 => mktime(20, 0, 0, $tm, $td, $ty),
						2100 => mktime(21, 0, 0, $tm, $td, $ty),
						2330 => mktime(23, 30, 0, $tm, $td, $ty),
						0000 => mktime(0, 0, 0, $tm, $td + 1, $ty),
						0330 => mktime(3, 30, 0, $tm, $td + 1, $ty),
						0400 => mktime(4, 0, 0, $tm, $td + 1, $ty),
						0800 => mktime(8, 0, 0, $tm, $td + 1, $ty),
						0814 => mktime(8, 15, 0, $tm, $td + 1, $ty));

	return($break_time);

}

//-------------------------------------------------------------------
// Work_Time (作業時間計算 UNIXタイム 生産計画時間計算用)
//
// 引数   $break_time = 休憩時間(UNIXタイム)配列
//        $start_tmp  = 開始時間
//        $end_tmp    = 終了時間
//
// 戻り値 return  = $end_tmp(休憩時間補正済み 終了時間)
//
// ＊問題点
//   休み時間を2つ以上含む作業時間の場合、正常に処理出来ない
//   現在は作業時間内に1つの休み時間のみ正常に処理出来る
//-------------------------------------------------------------------
function Work_Time($break_time, $start_tmp, $end_tmp) {

//print('<pre>');
//	var_dump(date('H:i:s', $start_tmp));
//	var_dump(date('H:i:s', $end_tmp));
//print('</pre>');

	// 10時休憩
	if ($start_tmp<=$break_time[1005] and $end_tmp>=$break_time[1010]) {
		$end_tmp = $end_tmp + ($break_time[1010] - $break_time[1005]);
	} elseif ($end_tmp>=$break_time[1005] and $end_tmp<=$break_time[1010]) {
		$end_tmp = $end_tmp + ($break_time[1010] - $break_time[1005]);
	}

	// 昼休み
	if ($start_tmp<=$break_time[1200] and $end_tmp>=$break_time[1245]) {
		$end_tmp = $end_tmp + ($break_time[1245] - $break_time[1200]);
	} elseif ($end_tmp>=$break_time[1200] and $end_tmp<=$break_time[1245]) {
		$end_tmp = $end_tmp + ($break_time[1245] - $break_time[1200]);
	}

	// 15時休憩
	if ($start_tmp<=$break_time[1505] and $end_tmp>=$break_time[1515]) {
		$end_tmp = $end_tmp + ($break_time[1515] - $break_time[1505]);
	} elseif ($end_tmp>=$break_time[1505] and $end_tmp<=$break_time[1515]) {
		$end_tmp = $end_tmp + ($break_time[1515] - $break_time[1505]);
	}

	// 18時休憩
	if ($start_tmp<=$break_time[1845] and $end_tmp>=$break_time[1915]) {
		$end_tmp = $end_tmp + ($break_time[1915] - $break_time[1845]);
	} elseif ($end_tmp>=$break_time[1845] and $end_tmp<=$break_time[1915]) {
		$end_tmp = $end_tmp + ($break_time[1915] - $break_time[1845]);
	}

	// 20時--21時予備時間(2006/07/19追加)
	if ($start_tmp<=$break_time[2000] and $end_tmp>=$break_time[2100]) {
		$end_tmp = $end_tmp + ($break_time[2100] - $break_time[2000]);
	} elseif ($end_tmp>=$break_time[2000] and $end_tmp<=$break_time[2100]) {
		$end_tmp = $end_tmp + ($break_time[2100] - $break_time[2000]);
	}

	// 23時休憩
	if ($start_tmp<=$break_time[2330] and $end_tmp>=$break_time[0000]) {
		$end_tmp = $end_tmp + ($break_time[0000] - $break_time[2330]);
	} elseif ($end_tmp>=$break_time[2330] and $end_tmp<=$break_time[0000]) {
		$end_tmp = $end_tmp + ($break_time[0000] - $break_time[2330]);
	}

	// (翌日)3時休憩
	if ($start_tmp<=$break_time[0330] and $end_tmp>=$break_time[0400]) {
		$end_tmp = $end_tmp + ($break_time[0400] - $break_time[0330]);
	} elseif ($end_tmp>=$break_time[0330] and $end_tmp<=$break_time[0400]) {
		$end_tmp = $end_tmp + ($break_time[0400] - $break_time[0330]);
	}

	// (翌日)8時休憩
	if ($start_tmp<=$break_time[0800] and $end_tmp>=$break_time[0814]) {
		$end_tmp = $end_tmp + ($break_time[0814] - $break_time[0800]);
	} elseif ($end_tmp>=$break_time[0800] and $end_tmp<=$break_time[0814]) {
		$end_tmp = $end_tmp + ($break_time[0814] - $break_time[0800]);
	}

//print('<pre>');
//	var_dump(date('H:i:s', $start_tmp));
//	var_dump(date('H:i:s', $end_tmp));
//print('</pre>');

	return($end_tmp);

}

//-------------------------------------------------------------------
// Cell_Pos (棒グラフ表示位置計算)
//
// この関数の戻り値を使用して棒グラフを描画するので引数そのままを戻り値
// として渡す要素がある
//
// 引数   $solder  = はんだ(0=不明, 1=共晶, 2=PBF)
//        $s_set   = 段取開始
//        $e_set   = 段取終了
//        $s_fit   = 実装開始
//        $e_fit   = 実装終了
//        $smt_mc  = 実装完了時間
//        $smt_fix = 修正完了時間
//
// 戻り値 return  = $cell_pos[]
//        $cell_pos['solder']   = はんだ((0=不明, 1=共晶, 2=PBF)(引数)
//        $cell_pos['span1']    = 段取作業セル数
//        $cell_pos['span2']    = 実装作業セル数
//        $cell_pos['smt_mc']   = 実装完了時間(引数)
//        $cell_pos['smt_fix']  = 修正完了時間(引数)
//-------------------------------------------------------------------
function Cell_Pos($solder, $s_set, $e_set, $s_fit, $e_fit, $smt_mc, $smt_fix) {

	// 2006/07/25 切り上げ -> 丸め込み -> 切り捨て
	//$span1 = ceil(($e_set - $s_set) / 300);
	//$span2 = ceil(($e_fit - $s_fit) / 300);
	//$span1 = round(($e_set - $s_set) / 300, 0);
	//$span2 = round(($e_fit - $s_fit) / 300, 0);
	$span1 = floor(($e_set - $s_set) / 300);
	$span2 = floor(($e_fit - $s_fit) / 300);


	$return_tmp = array('solder'=>$solder,
		   				'span1'=>$span1,
		   				'span2'=>$span2,
		   				'smt_mc'=>$smt_mc,
		   				'smt_fix'=>$smt_fix);

	return($return_tmp);

}

//-------------------------------------------------------------------
// Recess (休憩時間設定 UNIXタイム 作業実績時間計算用)
//
// 引数   $ty     = 年
//        $tm     = 月
//        $td     = 日
//
// 戻り値 return  = $recess[] 当日及び翌日分までの時間ブロックNo
//-------------------------------------------------------------------
function Recess($ty, $tm, $td) {

	$recess = array(0 => mktime(0, 0, 0, $tm, $td, $ty),
					1 => mktime(3, 30, 0, $tm, $td, $ty),
					2 => mktime(3, 30, 1, $tm, $td, $ty),
					3 => mktime(3, 59, 59, $tm, $td, $ty),
					4 => mktime(4, 0, 0, $tm, $td, $ty),
					5 => mktime(8, 0, 0, $tm, $td, $ty),
					6 => mktime(8, 0, 1, $tm, $td, $ty),
					7 => mktime(8, 14, 59, $tm, $td, $ty),
					8 => mktime(8, 15, 0, $tm, $td, $ty),
					9 => mktime(10, 5, 0, $tm, $td, $ty),
					10 => mktime(10, 5, 1, $tm, $td, $ty),
					11 => mktime(10, 9, 59, $tm, $td, $ty),
					12 => mktime(10, 10, 0, $tm, $td, $ty),
					13 => mktime(12, 0, 0, $tm, $td, $ty),
					14 => mktime(12, 0, 1, $tm, $td, $ty),
					15 => mktime(12, 44, 59, $tm, $td, $ty),
					16 => mktime(12, 45, 0, $tm, $td, $ty),
					17 => mktime(15, 5, 0, $tm, $td, $ty),
					18 => mktime(15, 5, 1, $tm, $td, $ty),
					19 => mktime(15, 14, 59, $tm, $td, $ty),
					20 => mktime(15, 15, 0, $tm, $td, $ty),
					21 => mktime(18, 45, 0, $tm, $td, $ty),
					22 => mktime(18, 45, 1, $tm, $td, $ty),
					23 => mktime(19, 14, 59, $tm, $td, $ty),
					24 => mktime(19, 15, 0, $tm, $td, $ty),
					25 => mktime(23, 30, 0, $tm, $td, $ty),
					26 => mktime(23, 30, 1, $tm, $td, $ty),
					27 => mktime(23, 59, 59, $tm, $td, $ty),

					28 => mktime(0, 0, 0, $tm, $td+1, $ty),
					29 => mktime(3, 30, 0, $tm, $td+1, $ty),
					30 => mktime(3, 30, 1, $tm, $td+1, $ty),
					31 => mktime(3, 59, 59, $tm, $td+1, $ty),
					32 => mktime(4, 0, 0, $tm, $td+1, $ty),
					33 => mktime(8, 0, 0, $tm, $td+1, $ty),
					34 => mktime(8, 0, 1, $tm, $td+1, $ty),
					35 => mktime(8, 14, 59, $tm, $td+1, $ty),
					36 => mktime(8, 15, 0, $tm, $td+1, $ty),
					37 => mktime(10, 5, 0, $tm, $td+1, $ty),
					38 => mktime(10, 5, 1, $tm, $td+1, $ty),
					39 => mktime(10, 9, 59, $tm, $td+1, $ty),
					40 => mktime(10, 10, 0, $tm, $td+1, $ty),
					41 => mktime(12, 0, 0, $tm, $td+1, $ty),
					42 => mktime(12, 0, 1, $tm, $td+1, $ty),
					43 => mktime(12, 44, 59, $tm, $td+1, $ty),
					44 => mktime(12, 45, 0, $tm, $td+1, $ty),
					45 => mktime(15, 5, 0, $tm, $td+1, $ty),
					46 => mktime(15, 5, 1, $tm, $td+1, $ty),
					47 => mktime(15, 14, 59, $tm, $td+1, $ty),
					48 => mktime(15, 15, 0, $tm, $td+1, $ty),
					49 => mktime(18, 45, 0, $tm, $td+1, $ty),
					50 => mktime(18, 45, 1, $tm, $td+1, $ty),
					51 => mktime(19, 14, 59, $tm, $td+1, $ty),
					52 => mktime(19, 15, 0, $tm, $td+1, $ty),
					53 => mktime(23, 30, 0, $tm, $td+1, $ty),
					54 => mktime(23, 30, 1, $tm, $td+1, $ty),
					55 => mktime(23, 59, 59, $tm, $td+1, $ty));

	return($recess);

}

//-------------------------------------------------------------------
// Offset_Time (休憩時間補正 UNIXタイム 作業実績時間計算用)
//
// 引数   $recess    = 休憩時間(UNIXタイム)配列
//        $start_tmp = 開始時間
//        $end_tmp   = 終了時間
//
// 戻り値 return  = $offset_tmp[0](開始時間ブロック)
//					$offset_tmp[1](終了時間ブロック)
//					$offset_tmp[2](休憩時間補正値 NULL = 休憩時間に開始/完了)
//												  1    = 同じ時間ブロック 休憩時間含まず
//												  以外 = 補正値(秒)
//
// ＊休憩時間を4回まで含む作業時間対応
// ＊休憩時間中に開始又は終了が登録された場合は未対応(計算しない)
//
//-------------------------------------------------------------------
function Offset_Time($recess, $start_tmp, $end_tmp) {

	// UNIXタイムへ変換
	$start_tmp = strtotime($start_tmp);
	$end_tmp = strtotime($end_tmp);

	// 当日及び翌日まで
	for ($i=0; $i<=57; $i++) {

		if ($start_tmp>=$recess[$i] and $start_tmp<=$recess[$i + 1]) {
			$offset_tmp[0] = $i;
		}

		if ($end_tmp>=$recess[$i] and $end_tmp<=$recess[$i + 1]) {
			$offset_tmp[1] = $i;
		}

	}

	// 変数名を簡易にするだけ
	$chk[0] = $offset_tmp[0];
	$chk[1] = $offset_tmp[1];

	if ($chk[0]==$chk[1]) {		// 休憩時間含まず

		// 0だとNULLと同等になるのでダミーで1とする(1秒なので無視出来る)
		$offset_tmp[2] = 1;

	} elseif (($chk[0] + 4)==$chk[1]) {	// 1回の休憩時間を含む

		if ($chk[0]==0 or $chk[0]==20 or $chk[0]==24 or $chk[0]==28 or $chk[0]==48 or $chk[0]==52) {

			$offset_tmp[2] = 1800;	// 30分休憩

		} elseif ($chk[0]==4 or $chk[0]==32) {

			$offset_tmp[2] = 900;	// 15分休憩

		} elseif ($chk[0]==8 or $chk[0]==36) {

			$offset_tmp[2] = 300;	// 5分休憩

		} elseif ($chk[0]==12 or $chk[0]==40) {

			$offset_tmp[2] = 2700;	// 45分休憩

		} elseif ($chk[0]==16 or $chk[0]==44) {

			$offset_tmp[2] = 600;	// 10分休憩

		}

	} elseif (($chk[0] + 8)==$chk[1]) {	// 2回の休憩時間を含む

		if ($chk[0]==0 or $chk[0]==28) {

			$offset_tmp[2] = 2700;	// 45分休憩

		} elseif ($chk[0]==4 or $chk[0]==32) {

			$offset_tmp[2] = 1200;	// 20分休憩

		} elseif ($chk[0]==8 or $chk[0]==36) {

			$offset_tmp[2] = 3000;	// 50分休憩

		} elseif ($chk[0]==12 or $chk[0]==40) {

			$offset_tmp[2] = 3300;	// 55分休憩

		} elseif ($chk[0]==16 or $chk[0]==44) {

			$offset_tmp[2] = 2400;	// 40分休憩

		} elseif ($chk[0]==20 or $chk[0]==24 or $chk[0]==48 or $chk[0]==52) {

			$offset_tmp[2] = 3600;	// 60分休憩

		}

	} elseif (($chk[0] + 12)==$chk[1]) {	// 3回の休憩時間を含む

		if ($chk[0]==0 or $chk[0]==28) {

			$offset_tmp[2] = 3000;	// 50分休憩

		} elseif ($chk[0]==4 or $chk[0]==32) {

			$offset_tmp[2] = 3900;	// 65分休憩

		} elseif ($chk[0]==8 or $chk[0]==36) {

			$offset_tmp[2] = 3600;	// 60分休憩

		} elseif ($chk[0]==12 or $chk[0]==40) {

			$offset_tmp[2] = 5100;	// 85分休憩

		} elseif ($chk[0]==16 or $chk[0]==44) {

			$offset_tmp[2] = 4200;	// 70分休憩

		} elseif ($chk[0]==20 or $chk[0]==48) {

			$offset_tmp[2] = 5400;	// 90分休憩

		} elseif ($chk[0]==24 or $chk[0]==52) {

			$offset_tmp[2] = 4500;	// 75分休憩

		}

	} elseif (($chk[0] + 16)==$chk[1]) {	// 4回の休憩時間を含む

		if ($chk[0]==0 or $chk[0]==28) {

			$offset_tmp[2] = 5700;	// 95分休憩

		} elseif ($chk[0]==4 or $chk[0]==32) {

			$offset_tmp[2] = 4500;	// 75分休憩

		} elseif ($chk[0]==8 or $chk[0]==36) {

			$offset_tmp[2] = 5400;	// 90分休憩

		} elseif ($chk[0]==12 or $chk[0]==40) {

			$offset_tmp[2] = 6900;	// 115分休憩

		} elseif ($chk[0]==16 or $chk[0]==44) {

			$offset_tmp[2] = 6000;	// 100分休憩

		} elseif ($chk[0]==20 or $chk[0]==48) {

			$offset_tmp[2] = 6300;	// 105分休憩

		} elseif ($chk[0]==24 or $chk[0]==52) {

			$offset_tmp[2] = 4800;	// 80分休憩

		}

	}

	return($offset_tmp);

}

?>
