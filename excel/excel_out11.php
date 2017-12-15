<?php
/*
[生産管理システム]

 *生産計画の使用部品集計(Excel出力)

  2009/09/

  2010-06-04
  SMT S品の在庫予測の為に修正開始

  1.棚番は棚番一覧を検索するのではなく、セットアップシートの見直し棚番を参照
  2.各棚番の在庫数データを追加
  3.現在の部品名ソートから棚番ソートに変更しようとしたが、それだと「Z」品が
    正常に集計出来無い!ので部品名ソートのまま
  4.使用数の集計
  → 一応差引在庫の数字までは表示されるようになったが、「Z」品の所も在庫数が表示
     されるのでまだ処理がおかしい ここで時間切れ


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

//---------------------------------------------------------------

// 引数の取得
if ($_GET["csv_file"]!=NULL) {
	$csv_file = $_GET["csv_file"];
}

// DB接続
$mdb2 = db_connect();

// 作業用一時テーブル作成(品名ソート)
$res = $mdb2->exec("CREATE TEMP TABLE csv_tmp (
					csv_id SERIAL,
					data0 TEXT,
					data1 TEXT,
					data2 TEXT,
					data3 TEXT,
					data4 TEXT,
					data5 TEXT,
					CONSTRAINT csv_tmp_pkey PRIMARY KEY (csv_id))");

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
					data8 TEXT,
					CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");

// 作業用一時テーブルクリア
//$res = $mdb2->exec("TRUNCATE TABLE csv_tmp");
//$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

if ($csv_file!=NULL) {

	// 相対パス指定注意!
	$data_csv = "../../data/total/" . $csv_file;

	$fp = fopen($data_csv, "r");

	// 生産計画日の初期化
	unset($mf_plan);

	//
	while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

			// Trimによる前後スペース削除
			for ($i=0; $i<=3; $i++) {
				$data[$i] = Trim($data[$i]);
			}

			$mf_date = $data[0];
			$mf_no   = $data[1];
			$mf_file = $data[2];
			$mf_qty  = $data[3];

			// 一覧の生産計画日の最初と最後を取得
			if ($mf_plan[0]==NULL) {
				$mf_plan[0] = $mf_date;
			}

			$mf_plan[1] = $mf_date;

			$res_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$mf_file'");
			if (PEAR::isError($res_row)) {
			} elseif ($res_row[0]!=NULL) {

				$unit_id = $res_row[0];
				$board   = $res_row[3];
				$mdb2->query("INSERT INTO csv_tmp(data0, data1, data2, data3, data4, data5) VALUES(
							".$mdb2->quote($mf_date, 'Text').",
							".$mdb2->quote($mf_no, 'Text').",
							".$mdb2->quote($unit_id, 'Text').",
							".$mdb2->quote($mf_file, 'Text').",
							".$mdb2->quote($board, 'Text').",
							".$mdb2->quote($mf_qty, 'Integer').")");

			}

	}

}

$res_csv = $mdb2->query("SELECT * FROM csv_tmp ORDER BY csv_id");
$res_csv = err_check($res_csv);

while($row_csv = $res_csv->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$mf_date = $row_csv['data0'];
	$mf_no   = $row_csv['data1'];
	$unit_id = $row_csv['data2'];
	$mf_file = $row_csv['data3'];
	$board   = $row_csv['data4'];
	$mf_qty  = $row_csv['data5'];

	$search_sql = "SELECT * FROM qty_data WHERE unit_id='$unit_id' ORDER BY qty_id";
	$res_qty = $mdb2->query($search_sql);
	$res_qty = err_check($res_qty);

	while($row_qty = $res_qty->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$unit_id = $row_qty['unit_id'];
		$q_part  = $row_qty['q_part'];
		$qty     = $row_qty['qty'];
		$use_qty = (int)($qty * $mf_qty);

//		// 棚番検索
//		$sql[1] = "SELECT * FROM part_smt";
//		$sql[2] = " WHERE product='$q_part'";
//		$sql[3] = " OR p_new='$q_part'";
//		$sql[4] = " OR p_maker='$q_part'";
//		$sql[5] = " OR p_sub='$q_part'";
//		$sql[6] = " OR p_nec='$q_part'";
//		$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6];
//
//		// 変数の初期化
//		unset($rack_no);
//		unset($solder);
//		unset($rack_old);
//
//		$tmp_query = $mdb2->query($sql[0]);
//		$tmp_query = err_check($tmp_query);
//		while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
//			$rack_no  = $tmp_row['rack_no'];
//			$solder   = $tmp_row['solder'];
//			$rack_old = $tmp_row['rack_old'];
//		}
//		unset($sql);

//		$mdb2->query("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5) VALUES(
//					".$mdb2->quote($mf_no, 'Text').",
//					".$mdb2->quote($unit_id, 'Text').",
//					".$mdb2->quote($q_part, 'Text').",
//					".$mdb2->quote($qty, 'Integer').",
//					".$mdb2->quote($use_qty, 'Integer').",
//					".$mdb2->quote($rack_old, 'Text').")");

		// 2010-06-04 セットアップデータの見直し棚番を検索
		$tmp_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id IN(SELECT st_id FROM station_data WHERE unit_id='$unit_id') AND part='$q_part'");
		if (PEAR::isError($tmp_row)) {
		} elseif ($tmp_row[0]!=NULL) {
			$rack_data = $tmp_row[6];
			$data_sel  = $tmp_row[7];
		}
		unset($tmp_row);

		// 2010-06-04 見直し棚番から登録されている在庫数を検索
		$tmp_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id IN(SELECT smt_id FROM part_smt WHERE rack_old='$rack_data')");
		if (PEAR::isError($tmp_row)) {
		} elseif ($tmp_row[0]!=NULL) {
			$qty_stock = $tmp_row[3];
			$up_date   = $tmp_row[4];
		}
		unset($tmp_row);

		$forecast_stock = (INT)($qty_stock - $use_qty);

		$mdb2->query("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7, data8) VALUES(
					".$mdb2->quote($mf_no, 'Text').",
					".$mdb2->quote($unit_id, 'Text').",
					".$mdb2->quote($q_part, 'Text').",
					".$mdb2->quote($qty, 'Integer').",
					".$mdb2->quote($use_qty, 'Integer').",
					".$mdb2->quote($rack_data, 'Text').",
					".$mdb2->quote($qty_stock, 'Integer').",
					".$mdb2->quote($up_date, 'Text').",
					".$mdb2->quote($forecast_stock, 'Integer').")");

	}

}

//-----------------------------------------------------------------------------
// Excelブック出力
//$book_name = "PLAN_PART_" . $mf_plan[0] . "_" . date("Y-m-d_H_i");
$book_name = "PLAN_PART_" . $mf_plan[0] . "_" . date("y-m-d_H_i");
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

//-----------------------------------------------------------------------------
// シート追加
$worksheet =& $workbook->addWorksheet("file_list");

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
$worksheet->setColumn(0, 0, $cell_size[3]);		// 計画日
$worksheet->setColumn(1, 1, $cell_size[4]);		// 工番
$worksheet->setColumn(2, 2, $cell_size[5]);		// ID
$worksheet->setColumn(3, 3, $cell_size[0]);		// ファイル名
$worksheet->setColumn(4, 4, $cell_size[0]);		// 品名
$worksheet->setColumn(5, 5, $cell_size[5]);		// 数量


// タイトル
$disp1 = "生産計画 使用部品一覧：[ ";
$disp2 = " ] <-> [ ";
$disp3 = " ]  出力日：" . date("Y-m-d H:i");
$disp = $disp1 . $mf_plan[0] . $disp2 . $mf_plan[1] . $disp3;
$worksheet->writeString(0, 0, mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells(0, 0, 0, 5);

// 項目
$worksheet->writeString(2, 0, mb_convert_encoding("計画日","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("工番","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("Unit ID","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("ファイル名","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("品名","SJIS"), $f2);
$worksheet->writeString(2, 5, mb_convert_encoding("数量","SJIS"), $f2);

$res_query = $mdb2->query("SELECT * FROM csv_tmp ORDER BY csv_id");
$res_query = err_check($res_query);

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$mf_date = $row['data0'];
	$mf_no   = $row['data1'];
	$unit_id = $row['data2'];
	$mf_file = $row['data3'];
	$borad   = $row['data4'];
	$mf_qty  = $row['data5'];

	$worksheet->writeString($r, 0, mb_convert_encoding($mf_date, "SJIS"), $f5);
	$worksheet->writeString($r, 1, mb_convert_encoding($mf_no, "SJIS"), $f5);
	$worksheet->writeString($r, 2, mb_convert_encoding($unit_id, "SJIS"), $f4);
	$worksheet->writeString($r, 3, mb_convert_encoding($mf_file, "SJIS"), $f3);
	$worksheet->writeString($r, 4, mb_convert_encoding($borad, "SJIS"), $f3);
	$worksheet->writeNumber($r, 5, mb_convert_encoding($mf_qty, "SJIS"), $f4);
	$r++;

}

//-----------------------------------------------------------------------------
// シート追加
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
$worksheet->setColumn(0, 0, $cell_size[3]);		// 工番
$worksheet->setColumn(1, 1, $cell_size[5]);		// Unit ID
$worksheet->setColumn(2, 2, $cell_size[0]);		// 部品名
$worksheet->setColumn(3, 3, $cell_size[5]);		// 数量
$worksheet->setColumn(4, 4, $cell_size[5]);		// 総数
$worksheet->setColumn(5, 5, $cell_size[3]);		// 棚番(セットアップデータの見直し棚番)
$worksheet->setColumn(6, 6, $cell_size[4]);		// 登録在庫数
$worksheet->setColumn(7, 7, $cell_size[3]);		// 在庫数 更新日
$worksheet->setColumn(8, 8, $cell_size[4]);		// 差引在庫 予測

// タイトル
//$disp1 = "生産計画 使用部品一覧：[ ";
//$disp2 = " ] <-> [ ";
//$disp3 = " ]  出力日：" . date("Y-m-d H:i");
//$disp = $disp1 . $mf_plan[0] . $disp2 . $mf_plan[1] . $disp3;
//$worksheet->writeString(0, 0, mb_convert_encoding($disp, "SJIS"), $f0);
//$worksheet->mergeCells(0, 0, 0, 7);

// 項目
$worksheet->writeString(0, 0, mb_convert_encoding("工番","SJIS"), $f2);
$worksheet->writeString(0, 1, mb_convert_encoding("Unit ID","SJIS"), $f2);
$worksheet->writeString(0, 2, mb_convert_encoding("部品名","SJIS"), $f2);
$worksheet->writeString(0, 3, mb_convert_encoding("数量","SJIS"), $f2);
$worksheet->writeString(0, 4, mb_convert_encoding("総数","SJIS"), $f2);
$worksheet->writeString(0, 5, mb_convert_encoding("棚番","SJIS"), $f2);
$worksheet->writeString(0, 6, mb_convert_encoding("登録在庫数","SJIS"), $f2);
$worksheet->writeString(0, 7, mb_convert_encoding("在庫数 更新日","SJIS"), $f2);
$worksheet->writeString(0, 8, mb_convert_encoding("差引在庫","SJIS"), $f2);

$res_query = $mdb2->query("SELECT * FROM data_tmp ORDER BY data2");
$res_query = err_check($res_query);

$r = 1;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$worksheet->writeString($r, 0, mb_convert_encoding($row['data0'], "SJIS"), $f5);
	$worksheet->writeString($r, 1, mb_convert_encoding($row['data1'], "SJIS"), $f4);
	$worksheet->writeString($r, 2, mb_convert_encoding($row['data2'], "SJIS"), $f3);
	$worksheet->writeNumber($r, 3, mb_convert_encoding($row['data3'], "SJIS"), $f4);
	$worksheet->writeNumber($r, 4, mb_convert_encoding($row['data4'], "SJIS"), $f4);
	$worksheet->writeString($r, 5, mb_convert_encoding($row['data5'], "SJIS"), $f3);
	$worksheet->writeNumber($r, 6, mb_convert_encoding($row['data6'], "SJIS"), $f4);
	$worksheet->writeString($r, 7, mb_convert_encoding($row['data7'], "SJIS"), $f5);
	$worksheet->writeString($r, 8, mb_convert_encoding($row['data8'], "SJIS"), $f4);
	$r++;

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();


// DB切断
db_disconnect($mdb2);

?>
