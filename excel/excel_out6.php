<?php
/*
[生産管理システム]

 * SMT部品 払い出しリスト出力(Excel出力)

  2008/06/27
  2008/07/02 テスト版


 */
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once '../inc/parameter_inc.php';
include_once '../lib/com_func1.php';
include_once '../lib/regist5_func.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

//---------------------------------------------------------------

// 引数の取得
$sel_process = $_GET["sel_process"];
$set_date    = $_GET["set_date"];

// 日付分解
list($set_y, $set_m, $set_d) = split('[/.-]', $set_date);

// Excelブック出力
$book_name = "REEL_MANAGE_" . date("Y-m-d_H_i_s");
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(12, 10, 10);					// フォントサイズ
$cell_size = array(25, 20, 15, 12, 10, 8, 5);	// セルサイズ
$cell_pos = array(0, 0, 0, 7);					// タイトルのセル位置設定

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

// [未使用]
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

// [集計 左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[0]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(44);

// [集計 右詰]
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[0]);
$f7->setAlign ('right');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(44);

// [集計 中央]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[0]);
$f8->setAlign ('center');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(51);

// シートの追加
$worksheet =& $workbook->addWorksheet("reel_manage");

// ページフォーマット指定(A4縦)
$worksheet->setPaper(9);
$worksheet->setPortrait();		// A4縦
//$worksheet->setLandscape();	// A4横

// ページ書式
$worksheet->setMarginTop(0.4);		// 上マージン
$worksheet->setMarginBottom(0.4);	// 下マージン
$worksheet->setMarginLeft(0.4);		// 左マージン
$worksheet->setMarginRight(0.2);	// 右マージン
$worksheet->fitToPages(1,0);		// 横1ページに収める
$worksheet->hideGridlines();		// 枠線を隠す

// 列幅設定
$worksheet->setColumn(0, 0, $cell_size[3]);		// 払い出し日
$worksheet->setColumn(1, 1, $cell_size[3]);		// 返却日
$worksheet->setColumn(2, 2, $cell_size[4]);		// M/C担当
$worksheet->setColumn(3, 3, $cell_size[3]);		// 管理番号
$worksheet->setColumn(4, 4, $cell_size[0]);		// 品名
$worksheet->setColumn(5, 5, $cell_size[5]);		// はんだ
$worksheet->setColumn(4, 6, $cell_size[5]);		// 棚番
$worksheet->setColumn(4, 7, $cell_size[0]);		// 備考

// タイトル設定
if ($sel_process==1 or $sel_process==2) {
	$disp_date = $set_date;
} elseif ($sel_process==4 or $sel_process==5) {
	$disp_date = $set_y . '-' . $set_m;
}

$process_disp = process_name($sel_process);

$disp = "払い出しリール一覧 [" . $disp_date . "] [" . $process_disp . "] 作成日：". date("Y-m-d H:i");
$worksheet->writeString($cell_pos[0], $cell_pos[1], mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells($cell_pos[0], $cell_pos[1], $cell_pos[2], $cell_pos[3] );

// 項目
$worksheet->writeString(2, 0, mb_convert_encoding("払い出し日","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("返却日","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("M/C担当","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("管理番号","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("品名","SJIS"), $f2);
$worksheet->writeString(2, 5, mb_convert_encoding("はんだ","SJIS"), $f2);
$worksheet->writeString(2, 6, mb_convert_encoding("棚番","SJIS"), $f2);
$worksheet->writeString(2, 7, mb_convert_encoding("備考","SJIS"), $f2);

// DB接続
$mdb2 = db_connect();

// SQL文 設定
$list_sql = list_sql_set($sel_process, $set_date);

$res_query = $mdb2->query($list_sql);
$res_query = err_check($res_query);

$reel_cnt = 0;

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$reel_id    = $row['reel_id'];
	$sel_mc     = $row['sel_mc'];
	$out_date   = $row['out_date'];
	$ret_date   = $row['ret_date'];
	$rem_manage = $row['rem_manage'];
	$mc_disp    = mc_name($sel_mc);

	// 部品リール情報 検索
	$reel_row = $mdb2->queryRow("SELECT * FROM reel_no WHERE reel_id='$reel_id'");
	if (PEAR::isError($reel_row)) {
	} elseif ($reel_row[0]!=NULL) {
		$check_no = $reel_row[2];
		$product  = $reel_row[6];
		$solder   = $reel_row[5];
//		$r_seq    = $reel_row[3];
//		$r_total  = $reel_row[4];
//		$qty      = $reel_row[9];

		switch ($solder) {
		case 0:
			$solder_name = '不明';
			break;
		case 1:
			$solder_name = '共晶';
			break;
		case 2:
			$solder_name = 'RoHS';
			break;
		case 3:
			$solder_name = '混在';
			break;
		}

	} else {
		unset($check_no);
		unset($product);
		unset($solder);
//		unset($r_seq);
//		unset($r_total);
//		unset($qty);
	}
	unset($reel_row);

	$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product'");
	if (PEAR::isError($part_row)) {
	} elseif ($part_row[0]!=NULL) {
		$rack_old = $part_row[10];
	}
	unset($part_row);

	// セル書式 設定
	$cell_fmt = array($f3, $f4, $f5);

	$worksheet->writeString($r, 0, mb_convert_encoding($out_date, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 1, mb_convert_encoding($ret_date, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 2, mb_convert_encoding($mc_disp, "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 3, mb_convert_encoding($check_no, "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 4, mb_convert_encoding($product, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 5, mb_convert_encoding($solder_name, "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 6, mb_convert_encoding($rack_old, "SJIS"), $cell_fmt[0]);
//	$worksheet->writeNumber($r, 7, mb_convert_encoding($rem_manage, "SJIS"), $cell_fmt[1]);
	$worksheet->writeString($r, 7, mb_convert_encoding($rem_manage, "SJIS"), $cell_fmt[0]);
	$r++;

	$reel_cnt++;
}

// リール集計数
//$worksheet->writeString($r, 0, mb_convert_encoding("", "SJIS"), $f6);
//$worksheet->writeString($r, 1, mb_convert_encoding("", "SJIS"), $f6);
//$worksheet->writeString($r, 2, mb_convert_encoding("", "SJIS"), $f6);
//$worksheet->writeString($r, 3, mb_convert_encoding("", "SJIS"), $f6);
//$worksheet->writeString($r, 4, mb_convert_encoding("", "SJIS"), $f6);
$worksheet->writeString($r, 5, mb_convert_encoding("計", "SJIS"), $f7);
$worksheet->writeNumber($r, 6, mb_convert_encoding($reel_cnt, "SJIS"), $f8);
$worksheet->writeString($r, 7, mb_convert_encoding("巻", "SJIS"), $f6);

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
