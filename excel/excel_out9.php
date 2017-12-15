<?php
/*
[生産管理システム]

 *アドバンス棚 登録一覧 出力(Excel出力)

  2008/08/21
  2010/02/16 備考が正常に出力されていなかったので修正

 */
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include '../inc/parameter_inc.php';
include "../lib/com_func1.php";
include "../lib/com_func2.php";

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

//---------------------------------------------------------------

// Excelブック出力
$book_name = "ADVANCE_" . date("Y-m-d_H_i_s");
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(12, 10, 10);					// フォントサイズ
$cell_size = array(30, 25, 20, 12, 10, 8, 5);	// セルサイズ
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

//// [日付]
//$f1 =& $workbook->addFormat();
//$f1->setFontFamily("MS UI Gothic");
//$f1->setSize($font_size[1]);
//$f1->setBold(1);
//$f1->setAlign('center');
//$f1->setAlign('vcenter');
////$f1->setBorder(1);
//$f1->setColor(0);
//$f1->setFgColor(31);

// [項目]
$f2 =& $workbook->addFormat();
$f2->setFontFamily("MS UI Gothic");
$f2->setSize($font_size[2]);
$f2->setAlign('center');
$f2->setAlign('vcenter');
$f2->setBorder(1);
$f2->setColor(0);
$f2->setFgColor(47);

// [データ(左詰)]
$f3 =& $workbook->addFormat();
$f3->setFontFamily("MS UI Gothic");
$f3->setSize($font_size[2]);
$f3->setAlign ('left');
$f3->setAlign('vcenter');
$f3->setBorder (1);
$f3->setColor(0);
$f3->setFgColor(9);

// [データ(右詰)]
$f4 =& $workbook->addFormat();
$f4->setFontFamily("MS UI Gothic");
$f4->setSize($font_size[2]);
$f4->setAlign ('right');
$f4->setAlign('vcenter');
$f4->setBorder (1);
$f4->setColor(0);
$f4->setFgColor(9);

// [データ(中央)]
$f5 =& $workbook->addFormat();
$f5->setFontFamily("MS UI Gothic");
$f5->setSize($font_size[2]);
$f5->setAlign ('center');
$f5->setAlign('vcenter');
$f5->setBorder (1);
$f5->setColor(0);
$f5->setFgColor(9);

// [データ(PBF)左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[2]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(9);

// シートの追加
$worksheet =& $workbook->addWorksheet("fs_qty");

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
$worksheet->setColumn(0, 0, $cell_size[4]);		// 棚番
$worksheet->setColumn(1, 1, $cell_size[3]);		// 部品番号
$worksheet->setColumn(2, 2, $cell_size[1]);		// 品名1
$worksheet->setColumn(3, 3, $cell_size[1]);		// 品名2
$worksheet->setColumn(4, 4, $cell_size[1]);		// 品名3
$worksheet->setColumn(5, 5, $cell_size[1]);		// 品名4
$worksheet->setColumn(6, 6, $cell_size[1]);		// 品名5
$worksheet->setColumn(7, 7, $cell_size[2]);		// 備考

// タイトル
$disp = "アドバンス専用棚 登録 一覧 作成日：". date("Y-m-d H:i");
$worksheet->writeString($cell_pos[0], $cell_pos[1], mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells($cell_pos[0], $cell_pos[1], $cell_pos[2], $cell_pos[3] );

// 項目
$worksheet->writeString(2, 0, mb_convert_encoding("棚番","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("部品番号","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("品名1","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("品名2","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("品名3","SJIS"), $f2);
$worksheet->writeString(2, 5, mb_convert_encoding("品名4","SJIS"), $f2);
$worksheet->writeString(2, 6, mb_convert_encoding("品名5","SJIS"), $f2);
$worksheet->writeString(2, 7, mb_convert_encoding("備考","SJIS"), $f2);

// DB接続
$mdb2 = db_connect();

$res_query = $mdb2->query("SELECT * FROM part_advance ORDER BY rack_no");
$res_query = err_check($res_query);

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$advance_id  = $row['advance_id'];
	$rack_no     = $row['rack_no'];
	$part_no     = $row['part_no'];
	$part_1      = $row['part_1'];
	$part_2      = $row['part_2'];
	$part_3      = $row['part_3'];
	$part_4      = $row['part_4'];
	$part_5      = $row['part_5'];
	$rem_advance = $row['rem_advance'];

	$worksheet->writeString($r, 0, mb_convert_encoding($rack_no, "SJIS"), $f3);
	$worksheet->writeString($r, 1, mb_convert_encoding($part_no, "SJIS"), $f3);
	$worksheet->writeString($r, 2, mb_convert_encoding($part_1, "SJIS"), $f3);
	$worksheet->writeString($r, 3, mb_convert_encoding($part_2, "SJIS"), $f3);
	$worksheet->writeString($r, 4, mb_convert_encoding($part_3, "SJIS"), $f3);
	$worksheet->writeString($r, 5, mb_convert_encoding($part_4, "SJIS"), $f3);
	$worksheet->writeString($r, 6, mb_convert_encoding($part_5, "SJIS"), $f3);
	$worksheet->writeString($r, 7, mb_convert_encoding($rem_advance, "SJIS"), $f3);

	$r++;

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
