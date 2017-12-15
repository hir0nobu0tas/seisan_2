<?php
/*
[生産管理システム]

 *機種毎の使用部品一覧 出力(Excel出力)

  2009/06/18 大体要望通り？
  2009/06/24 ステーションの表示修正(0、1、2 → 1、2、3へ 現場要望)
             入力したオーダー数量が総数に反映されていなかったので修正
  2009/06/25 データの構成により出力NG? 昨日のステーションの表示修正も安易な方法でNG
             → 修正
  2009/07/01 アドバンス専用棚番に対応
  2009/07/06 棚番検索でRoHS品以外も検索対象とする(共晶オーダーも存在する為)
  2009/07/08 アドバンス棚番データのテーブル定義変更対応(品名を5種まで登録)
  2009/07/31 同一部品集計が未完成だったので実装 仮対応(もう少し表示を工夫できるか？)
  2009/08/05 セットアップシートと同様に単数と総数表示に変更(現場要望)
  2015/10/29 新棚番対応 生産中の機種用に旧棚番と新棚番を併記したが、新棚番のみへ修正
  2015/12/07 棚番「R」の処理修正
  2016/02/15 旧棚番対応品(今後の生産が無いので新棚番を採番しない)で仮に旧棚番出力へ変更
  2016/03/11 ファイル名用に品名を取得
  2016/04/18 ダッシュが抜けていたので修正
  2017/07/31 日付処理修正 mb_convert_encoding で変換前の文字コードを指定("UTF-8")

*/
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include '../inc/parameter_inc.php';
include "../lib/com_func1.php";

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

// 2017/07/31 追加
date_default_timezone_set('Asia/Tokyo');

//---------------------------------------------------------------

// 引数の取得
if ($_GET["unit_id0"]!=NULL) {
	$unit_id[0] = $_GET["unit_id0"];
}
if ($_GET["unit_id1"]!=NULL) {
	$unit_id[1] = $_GET["unit_id1"];
}
if ($_GET["unit_id2"]!=NULL) {
	$unit_id[2] = $_GET["unit_id2"];
}
if ($_GET["unit_id3"]!=NULL) {
	$unit_id[3] = $_GET["unit_id3"];
}

// 2016/04/18 修正
$operate_no   = $_GET["operate_no"];
$dash_no      = $_GET["dash_no"];
$product_name = $_GET["product"];
$mf_qty       = $_GET["mf_qty"];

$cnt_unit = count($unit_id) - 1;

// DB接続
$mdb2 = db_connect();

// 作業用一時テーブル作成(品名ソート)
$res = $mdb2->exec("CREATE TEMP TABLE data_tmp (
					tmp_id SERIAL,
					data0 TEXT,
					data1 TEXT,
					data2 TEXT,
					data3 TEXT,
					data4 TEXT,
					data5 TEXT,
					data6 TEXT,
					data7 TEXT,
					CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");


// 作業用一時テーブルクリア
$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

// 2015/10/26 新棚番対応
for ($i=0; $i<=$cnt_unit; $i++) {

	// [unit_data] 取得
	$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id[$i]'");
	if (PEAR::isError($unit_row)) {
	} else {
		$unit_data[$i][0] = $unit_row[0];	// unit_id
		$unit_data[$i][1] = $unit_row[1];	// u _index
		$unit_data[$i][2] = $unit_row[2];	// file_name
		$unit_data[$i][3] = $unit_row[3];	// board
		$unit_data[$i][4] = $unit_row[4];	// product
	}

	$u_index  = $unit_data[$i][1];
	$file_name = $unit_data[$i][2];

	// 棚番種別 取得
	// 2009/07/01 アドバンス向け対応
	$name_len = strlen($file_name);
	$side_tmp = substr($file_name, $name_len - 3, 3);
	if ($side_tmp=='-01') {
		$rack_type = 'FS';
	} elseif ($side_tmp=='-02') {
		$rack_type = 'FS';
	} elseif ($side_tmp=='-03') {
		$rack_type = 'FS';
	} elseif ($side_tmp=='-04') {
		$rack_type = 'FS';
	} elseif ($side_tmp=='-A1') {
		$rack_type = 'Advance';
	} elseif ($side_tmp=='-A2') {
		$rack_type = 'Advance';
	} elseif ($side_tmp=='-A3') {
		$rack_type = 'Advance';
	} elseif ($side_tmp=='-A4') {
		$rack_type = 'Advance';
	}

	$sql_select = "SELECT * FROM set_data T3 JOIN station_data T2 ON(T3.st_id=T2.st_id) JOIN unit_data T1 ON(T2.unit_id=T1.unit_id)";
	$sql_where = " WHERE file_name='$file_name' AND u_index='$u_index'";
	$sql_order = " ORDER BY set_id";
	$search_sql = $sql_select . $sql_where . $sql_order;
	$res_query = $mdb2->query($search_sql);
	$res_query = err_check($res_query);

	//
	$cnt = 0;
	$f_cnt = 1;
	$r_cnt = 1;
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$st_index         = $row['st_index'];
		$st_id[$st_index] = $row['st_id'];
		$p_name           = $row['p_name'];
		$rem_set1         = $row['rem_set1'];
		$rem_set2         = $row['rem_set2'];

		$hole_no          = $row['hole_no'];
		$set_part         = $row['part'];

		// 2009/06/25 ステーション種別の抽出
		$name_len = strlen($p_name);
		$st_item = substr($p_name, $name_len - 1, 1);

		// 部品データ検索
		$p_data[$cnt] = $row['part'];

		// 数量データ検索
		$qty_row = $mdb2->queryRow("SELECT * FROM qty_data WHERE unit_id='$unit_id[$i]' AND q_part='$p_data[$cnt]'");
		if (PEAR::isError($qty_row)) {
		} else {
			$q_part = $qty_row[5];
			$qty    = $qty_row[6];
		}

		// 重複品名の処理
		$key = array_search($q_part, $p_data);
		if ($key!=$cnt) {
			$qty = '0';
		}
		$cnt++;

		// 棚番データ検索
		// 2009/07/01 アドバンス向け対応
		if ($rack_type=='FS') {

			$part =  $row['part'];
			$sql[1] = "SELECT * FROM part_smt";
			$sql[2] = " WHERE product='$part'";
			$sql[3] = " OR p_new='$part'";
			$sql[4] = " OR p_maker='$part'";
			$sql[5] = " OR p_sub='$part'";
			$sql[6] = " OR p_nec='$part'";
			$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6];

			// 変数の初期化
			unset($rack_no);
			unset($solder);
			unset($rack_old);
			unset($rack_no);

			// 棚番(RoHS)を検索(共晶品の処理は未解決)
			// 2009/07/03 RoHS品以外も検索対象とする
			$tmp_query = $mdb2->query($sql[0]);
			$tmp_query = err_check($tmp_query);
			while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				//if ($tmp_row['solder']=='2') {
					$rack_no  = $tmp_row['rack_no'];
					$solder   = $tmp_row['solder'];
					$rack_old = $tmp_row['rack_old'];
					$rack_no  = $tmp_row['rack_no'];
				//}
			}
			unset($sql);

		} elseif ($rack_type=='Advance') {

			$part =  $row['part'];
			$sql[1] = "SELECT * FROM part_advance";
			$sql[2] = " WHERE part_1='$part'";
			$sql[3] = " OR part_2='$part'";
			$sql[4] = " OR part_3='$part'";
			$sql[5] = " OR part_4='$part'";
			$sql[6] = " OR part_5='$part'";
			$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6];

			// 変数の初期化
			unset($rack_no);
			unset($solder);
			unset($rack_old);
			unset($rack_no);

			$tmp_query = $mdb2->query($sql[0]);
			$tmp_query = err_check($tmp_query);
			while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$rack_old = $tmp_row['rack_no'];
				$rack_no  = $tmp_row['rack_no'];
			}
			unset($sql);

		}

		// 2009/06/24 見直しによる棚番指定がある場合は指定棚番を表示
		if ($row['data_sel']=='1') {
			$rack_old = $row['rack_data'];
			$rack_no  = $row['rack_data'];
		}

		// 2009/06/18 表面と裏面での部品連番の生成
		// ソート処理で便利なように4桁のパディング処理とする
		if ($u_index=='1') {

			if ($f_cnt<=9) {
				$index_tmp = 'F00' . $f_cnt;
			} elseif ($f_cnt<=99) {
				$index_tmp = 'F0' . $f_cnt;
			} else {
				$index_tmp = 'F' . $f_cnt;
			}
			$f_cnt++;

		} elseif ($u_index=='2') {

			if ($r_cnt<=9) {
				$index_tmp = 'R00' . $r_cnt;
			} elseif ($r_cnt<=99) {
				$index_tmp = 'R0' . $r_cnt;
			} else {
				$index_tmp = 'R' . $r_cnt;
			}
			$r_cnt++;

		}

		// 作業用一時テーブルへ格納
		//$total_qty = (string)((integer)$qty * (integer)$mf_qty);
/* 		$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7) VALUES(
							".$mdb2->quote($index_tmp, 'Text').",
							".$mdb2->quote(($st_item), 'Text').",
							".$mdb2->quote($hole_no, 'Text').",
							".$mdb2->quote($set_part, 'Text').",
							".$mdb2->quote($qty, 'Text').",
							".$mdb2->quote(($qty * (int)$mf_qty), 'Text').",
							".$mdb2->quote($rack_old, 'Text').",
							".$mdb2->quote($rem_set2, 'Text').")");
 */

		$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7) VALUES(
							".$mdb2->quote($index_tmp, 'Text').",
							".$mdb2->quote(($st_item), 'Text').",
							".$mdb2->quote($hole_no, 'Text').",
							".$mdb2->quote($set_part, 'Text').",
							".$mdb2->quote($qty, 'Text').",
							".$mdb2->quote(($qty * (int)$mf_qty), 'Text').",
							".$mdb2->quote($rack_no, 'Text').",
							".$mdb2->quote($rem_set2, 'Text').")");

	}

}


// Excelブック出力
// 2016/03/11 ファイル名に品名を入れる
//$book_name = "PART_LIST_" . $operate_no . "_" . date("Y-m-d_H_i") . "_" . $mf_qty;
$book_name = "PART_LIST_" . $operate_no . "_" . $product_name . "_" . $mf_qty;
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(10, 12, 14);					// フォントサイズ
$cell_size = array(30, 25, 20, 12, 10, 7, 5);	// セルサイズ
$cell_pos  = array(0, 0, 0, 7);					// タイトルのセル位置設定

// セルフォーマット定義
// [表題]
$f0 =& $workbook->addFormat();
$f0->setFontFamily("MS UI Gothic");
$f0->setSize($font_size[0]);
$f0->setBold(1);
$f0->setAlign('center');
$f0->setAlign('vcenter');
$f0->setColor(0);
$f0->setFgColor(42);
//$f0->setMerge();

// [パラメータ？]
$f1 =& $workbook->addFormat();
$f1->setFontFamily("MS UI Gothic");
$f1->setSize($font_size[0]);
$f1->setBold(1);
$f1->setAlign('center');
$f1->setAlign('vcenter');
//$f1->setBorder(1);
$f1->setColor(0);
$f1->setFgColor(31);

// [項目]
$f2 =& $workbook->addFormat();
$f2->setFontFamily("MS UI Gothic");
$f2->setSize($font_size[0]);
$f2->setAlign('center');
$f2->setAlign('vcenter');
$f2->setBorder(1);
$f2->setColor(0);
$f2->setFgColor(47);

// [データ 左詰]
$f3 =& $workbook->addFormat();
$f3->setFontFamily("MS UI Gothic");
$f3->setSize($font_size[0]);
$f3->setAlign ('left');
$f3->setAlign('vcenter');
$f3->setBorder (1);
$f3->setColor(0);
$f3->setFgColor(9);

// [データ 右詰]
$f4 =& $workbook->addFormat();
$f4->setFontFamily("MS UI Gothic");
$f4->setSize($font_size[0]);
$f4->setAlign ('right');
$f4->setAlign('vcenter');
$f4->setBorder (1);
$f4->setColor(0);
$f4->setFgColor(9);

// [データ 中央]
$f5 =& $workbook->addFormat();
$f5->setFontFamily("MS UI Gothic");
$f5->setSize($font_size[0]);
$f5->setAlign ('center');
$f5->setAlign('vcenter');
$f5->setBorder (1);
$f5->setColor(0);
$f5->setFgColor(9);


// シートの追加
$worksheet =& $workbook->addWorksheet("part_list");

// ページフォーマット指定(A4縦->A4横)
$worksheet->setPaper(9);
$worksheet->setPortrait();
//$worksheet->setLandscape();

// ページ書式
$worksheet->setMarginTop(0.4);		// 上マージン
$worksheet->setMarginBottom(0.4);	// 下マージン
$worksheet->setMarginLeft(0.4);		// 左マージン
$worksheet->setMarginRight(0.2);	// 右マージン
$worksheet->fitToPages(1,0);		// 横1ページに収める
$worksheet->hideGridlines();		// 枠線を隠す

// 列幅設定
$worksheet->setColumn(0, 0, $cell_size[5]);		// No
$worksheet->setColumn(1, 1, $cell_size[6]);		// ST
$worksheet->setColumn(2, 2, $cell_size[4]);		// 穴番号
$worksheet->setColumn(3, 3, $cell_size[0]);		// 部品名
$worksheet->setColumn(4, 4, $cell_size[6]);		// 数量
$worksheet->setColumn(5, 5, $cell_size[5]);		// 数量
//$worksheet->setColumn(6, 6, $cell_size[4]);		// 旧棚番
$worksheet->setColumn(6, 6, $cell_size[4]);		// 新棚番
$worksheet->setColumn(7, 7, $cell_size[0]);		// 備考

// 2009/06/19 製品名は現在未登録の為、ダミーとしておく
//$product = $unit_data[0][0];
$product = "  ";
$board   = $unit_data[0][3];

// 2009/06/25 追加
// [station_data] 取得
$station_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id[0]' AND st_index='0'");
if (PEAR::isError($station_row)) {
} else {
	$p_name = $station_row[3];
}

// [set_data] 取得
//$set_row = $mdb2->queryRow("SELECT * FROM set_data WHERE unit_id='$unit_id[0]' AND set_index='0'");
//if (PEAR::isError($set_row)) {
//} else {
//	$set_id = $set_row[0];
//}


// タイトル
$disp1 = "使用部品一覧：[";
$disp2 = "] 工番[ ";
$disp3 = " ]  ダッシュ[ ";
$disp4 = " ]  数量[ ";
$disp5 = " ]  出力日：" . date("Y-m-d H:i");
$disp = $disp1 . $product . $disp2 . $operate_no . $disp3 . $dash_no . $disp4 . $mf_qty . $disp5;
$worksheet->writeString(0, 0, mb_convert_encoding($disp, "SJIS", "UTF-8"), $f0);
$worksheet->mergeCells(0, 0, 0, 7);
$worksheet->writeString(2, 0, mb_convert_encoding("基板名", "SJIS", "UTF-8"), $f2);
$worksheet->mergeCells(2, 0, 2, 1);
$worksheet->writeString(2, 2, mb_convert_encoding($board, "SJIS", "UTF-8"), $f1);
$worksheet->mergeCells(2, 2, 2, 3);
$worksheet->writeString(2, 4, mb_convert_encoding("プログラム名", "SJIS", "UTF-8"), $f2);
$worksheet->mergeCells(2, 4, 2, 5);
$worksheet->writeString(2, 6, mb_convert_encoding($p_name, "SJIS", "UTF-8"), $f1);
$worksheet->mergeCells(2, 6, 2, 7);

// 項目
$worksheet->writeString(4, 0, mb_convert_encoding("No","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 1, mb_convert_encoding("ST","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 2, mb_convert_encoding("穴番号","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 3, mb_convert_encoding("部品名","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 4, mb_convert_encoding("数量","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 5, mb_convert_encoding("総数","SJIS", "UTF-8"), $f2);
//$worksheet->writeString(4, 6, mb_convert_encoding("旧棚番","SJIS"), $f2);
$worksheet->writeString(4, 6, mb_convert_encoding("棚番","SJIS", "UTF-8"), $f2);
$worksheet->writeString(4, 7, mb_convert_encoding("備考(品名)","SJIS", "UTF-8"), $f2);

//$res_query = $mdb2->query("SELECT * FROM data_tmp ORDER BY tmp_id");
$res_query = $mdb2->query("SELECT * FROM data_tmp ORDER BY data3, data0");
$res_query = err_check($res_query);

$r = 5;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$worksheet->writeString($r, 0, mb_convert_encoding($row['data0'], "SJIS", "UTF-8"), $f3);
	$worksheet->writeString($r, 1, mb_convert_encoding($row['data1'], "SJIS", "UTF-8"), $f5);
	$worksheet->writeString($r, 2, mb_convert_encoding($row['data2'], "SJIS", "UTF-8"), $f3);
	$worksheet->writeString($r, 3, mb_convert_encoding($row['data3'], "SJIS", "UTF-8"), $f3);
	$worksheet->writeNumber($r, 4, mb_convert_encoding($row['data4'], "SJIS", "UTF-8"), $f4);
	$worksheet->writeNumber($r, 5, mb_convert_encoding($row['data5'], "SJIS", "UTF-8"), $f4);

/* 	$worksheet->writeString($r, 6, mb_convert_encoding($row['data6'], "SJIS"), $f3);
 */
	if (strlen($row['data6'])==8) {
		//$worksheet->writeString($r,  6, mb_convert_encoding(substr($row['data6'], 2, 5), "SJIS"), $f3);	// 新棚番を表示(棚番部分のみ)
		$worksheet->writeString($r,  6, mb_convert_encoding($row['data6'], "SJIS", "UTF-8"), $f3);	// 新棚番を表示(8桁すべて)
	} elseif ($row['data6']=='ZN' or $row['data6']=='Z' or $row['data6']=='R') {
		$worksheet->writeString($r,  6, mb_convert_encoding($row['data6'], "SJIS", "UTF-8"), $f3);	// ZNかZかRの場合はそのまま表示新棚番を表示
	} else {
		$worksheet->writeString($r,  6, mb_convert_encoding('', "SJIS", "UTF-8"), $f3);				// 表示無し
	}

	$worksheet->writeString($r, 7, mb_convert_encoding($row['data7'], "SJIS", "UTF-8"), $f3);
	$r++;

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
