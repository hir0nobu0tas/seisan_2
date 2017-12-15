<?php

//-------------------------------------------------------------------
// Cell_Col_Set
// スケジュールグラフのセルサイズ指定(行) 行の最初に入れる
// (透明画像をサイズ指定で貼付ける事でセルサイズを指定)
//
// 引数   無し
// 戻り値 無し
//-------------------------------------------------------------------
function Cell_Col_Set() {

	print('<tr>');
	for ($i=1; $i<=144; $i++) {
		print('<td><img src="../graphics/bar_dummy.jpg" width="2" height="1"></td>');
	}
	print('</tr>');

}

//-------------------------------------------------------------------
// Cell_Row_Set
// スケジュールグラフのセルサイズ指定(列) 列の最後に入れる
// (透明画像をサイズ指定で貼付ける事でセルサイズを指定)
//
// 引数   無し
// 戻り値 無し
//-------------------------------------------------------------------
function Cell_Row_Set() {

	print('<td><img src="../graphics/bar_dummy.jpg" width="1" height="20"></td>');

}

//-------------------------------------------------------------------
// Dummy_Br
// 擬似改行 <br>タグでは開きすぎるので透明画像をサイズ指定で貼り付けて
// 間隔を微調整する
//
// 引数   $h = 縦サイズ(改行サイズ)
// 戻り値 無し
//-------------------------------------------------------------------
function Dummy_Br($h) {

	print('<td><img src="../graphics/bar_dummy.jpg" width="1" height="');
	print(htmlspecialchars($h));
	print('"></td>');

}

//-------------------------------------------------------------------
// Bar_Disp
// スケジュールの棒グラフ描画
// 基本的にforループが通常終了はしないはず 条件によるブレークで終了
//
// 引数   $cell_set[] = データ配列
//        $cell_span  = セル範囲
//        $order_cnt  = 実装順のカウント値
//        $break      = 次の休憩時間セル数(1セル=5分)
//
//
// 戻り値 $cell_set[] = データ配列(描画時に内容変更有り)
//        $order_cnt  = 実装順のカウント値
//
//-------------------------------------------------------------------
function Bar_Disp($cell_set, $cell_span, $order_cnt, $break) {

	$col1 = $cell_span;

	for ($i=$order_cnt; $i<=100; $i++) {

		// はんだ種別で色の変更
		if ($cell_set[$i]["smt_mc"] != NULL and $cell_set[$i]["smt_fix"] != NULL) {
			$bgcolor1 = '<td bgcolor="#4682b4" colspan=';
			$bgcolor2 = '<td bgcolor="#4682b4" colspan=';
		} elseif ($cell_set[$i]["smt_mc"] != NULL and $cell_set[$i]["smt_fix"] == NULL) {
			$bgcolor1 = '<td bgcolor="#c0c0c0" colspan=';
			$bgcolor2 = '<td bgcolor="#c0c0c0" colspan=';
		} elseif ($cell_set[$i]["smt_mc"] == NULL) {
			if ($cell_set[$i]["solder"] == '2') {
				$bgcolor1 = '<td bgcolor="#ffc0cb" colspan=';
				$bgcolor2 = '<td bgcolor="#f4a460" colspan=';
			} else {
				$bgcolor1 = '<td bgcolor="#87cefe" colspan=';
				$bgcolor2 = '<td bgcolor="#90ee90" colspan=';
			}
		}

		$col2 = $col1 - $cell_set[$i]["span1"];
		if ($col2<=0) {
			print($bgcolor1);
			print(htmlspecialchars($col1));
			print('>');
			print(htmlspecialchars($i));
			print('</td>');

			$cell_set[$i]["span1"] = $cell_set[$i]["span1"] - $col1 - $break;

			$order_cnt = $i;

			// 戻り値用配列
			$bar_disp = array("cell_set"=>$cell_set, "order_cnt"=>$order_cnt);

			return($bar_disp);

			break;
		}

		if ($cell_set[$i]["span1"]!=0) {
			print($bgcolor1);
			print(htmlspecialchars($cell_set[$i]["span1"]));
			print('>');
			print(htmlspecialchars($i));
			print('</td>');

			$cell_set[$i]["span1"] = 0;

			$col1 = $col2;
		}


		$col2 = $col1 - $cell_set[$i]["span2"];
		if ($col2<=0) {
			print($bgcolor2);
			print(htmlspecialchars($col1));
			print('>');
			print(htmlspecialchars($i));
			print('</td>');

			$cell_set[$i]["span2"] = $cell_set[$i]["span2"] - $col1 - $break;

			$order_cnt = $i;

			// 戻り値用配列
			$bar_disp = array("cell_set"=>$cell_set, "order_cnt"=>$order_cnt);

			return($bar_disp);

			break;
		}

		if ($cell_set[$i]["span2"]!=0) {
			print($bgcolor2);
			print(htmlspecialchars($cell_set[$i]["span2"]));
			print('>');
			print(htmlspecialchars($i));
			print('</td>');

			$cell_set[$i]["span2"] = 0;

			$col1 = $col2;
		}

	}

}

?>
