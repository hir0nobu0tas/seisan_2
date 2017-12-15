<?php
/*
[生産管理システム]

 *登録部品一覧(Excel出力)

  2007/09/26
  2007/12/21 新棚番と旧棚番の表示位置入替え(実質、新棚番では運用出来ていない)
             リールサイズと高額品の数値化対応

  2008/01/22 品名の整理の為、18文字以上の品名(AVXに登録出来る文字数が18文字)
             の抽出テスト

  2008/06/24 現場からの要望でSMT部品データ[part_smt]に棚番備考[rem_rack]追加

  2008/07/04 現場からの要望でリールサイズの表記変更
             及び表示項目の整理

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
$book_name = "FS_MASTER_" . date("Y-m-d_H_i_s");
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(12, 10, 10);					// フォントサイズ
$cell_size = array(30, 25, 20, 12, 10, 8, 5);	// セルサイズ
//$cell_pos = array(0, 2, 0, 8);					// タイトルのセル位置設定
$cell_pos = array(0, 0, 0, 7);					// タイトルのセル位置設定

// セルフォーマット定義
include '../inc/excel_out_inc1.php';

// [合計]
$f10 =& $workbook->addFormat();
$f10->setFontFamily("MS UI Gothic");
$f10->setBold(1);
$f10->setSize($font_size[0]);
$f10->setAlign ('center');
$f10->setBorder (1);
$f10->setColor(0);
$f10->setFgColor(51);

// シートの追加
$worksheet =& $workbook->addWorksheet("fs_master");

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
//$worksheet->setColumn(0, 0, $cell_size[3]);	// 現棚番
//$worksheet->setColumn(1, 1, $cell_size[2]);	// 品名
//$worksheet->setColumn(2, 2, $cell_size[6]);	// はんだ
//$worksheet->setColumn(3, 3, $cell_size[2]);	// 新品名
//$worksheet->setColumn(4, 4, $cell_size[2]);	// メーカー品名
//$worksheet->setColumn(5, 5, $cell_size[2]);	// サブ品名
//$worksheet->setColumn(6, 6, $cell_size[2]);	// NEC品名
//$worksheet->setColumn(7, 7, $cell_size[6]);	// リールサイズ
//$worksheet->setColumn(8, 8, $cell_size[6]);	// 高額品
//$worksheet->setColumn(9, 9, $cell_size[3]);	// 新棚番
//$worksheet->setColumn(10, 10, $cell_size[2]);	// 備考
//$worksheet->setColumn(11, 11, $cell_size[2]);	// 棚番備考
//$worksheet->setColumn(12, 12, $cell_size[5]);	// 部品ID
$worksheet->setColumn(0, 0, $cell_size[3]);		// 現棚番
$worksheet->setColumn(1, 1, $cell_size[2]);		// 品名
$worksheet->setColumn(2, 2, $cell_size[6]);		// はんだ
$worksheet->setColumn(3, 3, $cell_size[2]);		// メーカー品名
$worksheet->setColumn(4, 4, $cell_size[4]);		// リールサイズ
$worksheet->setColumn(5, 5, $cell_size[6]);		// 高額品
$worksheet->setColumn(6, 6, $cell_size[2]);		// 備考
$worksheet->setColumn(7, 7, $cell_size[2]);		// 棚番備考

// タイトル
$disp = "SMT(福島ストック品) 登録一覧 作成日：". date("Y-m-d H:i");
$worksheet->writeString($cell_pos[0], $cell_pos[1], mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells($cell_pos[0], $cell_pos[1], $cell_pos[2], $cell_pos[3] );

// 項目
//$worksheet->writeString(2, 0, mb_convert_encoding("現棚番","SJIS"), $f2);
//$worksheet->writeString(2, 1, mb_convert_encoding("品名","SJIS"), $f2);
//$worksheet->writeString(2, 2, mb_convert_encoding("はんだ","SJIS"), $f2);
//$worksheet->writeString(2, 3, mb_convert_encoding("新品名","SJIS"), $f2);
//$worksheet->writeString(2, 4, mb_convert_encoding("メーカー品名","SJIS"), $f2);
//$worksheet->writeString(2, 5, mb_convert_encoding("サブ品名","SJIS"), $f2);
//$worksheet->writeString(2, 6, mb_convert_encoding("NEC品名","SJIS"), $f2);
//$worksheet->writeString(2, 7, mb_convert_encoding("リール","SJIS"), $f2);
//$worksheet->writeString(2, 8, mb_convert_encoding("高額品","SJIS"), $f2);
//$worksheet->writeString(2, 9, mb_convert_encoding("新棚番","SJIS"), $f2);
//$worksheet->writeString(2, 10, mb_convert_encoding("備考","SJIS"), $f2);
//$worksheet->writeString(2, 11, mb_convert_encoding("棚番備考","SJIS"), $f2);
//$worksheet->writeString(2, 12, mb_convert_encoding("部品ID","SJIS"), $f2);
$worksheet->writeString(2, 0, mb_convert_encoding("現棚番","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("品名","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("はんだ","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("メーカー品名","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("リール","SJIS"), $f2);
$worksheet->writeString(2, 5, mb_convert_encoding("高額品","SJIS"), $f2);
$worksheet->writeString(2, 6, mb_convert_encoding("備考","SJIS"), $f2);
$worksheet->writeString(2, 7, mb_convert_encoding("棚番備考","SJIS"), $f2);

// DB接続
$mdb2 = db_connect();

$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY smt_id");
$res_query = err_check($res_query);

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

//	switch ($row['solder']) {
//	case 0:
//		$solder = '不明';
//		break;
//	case 1:
//		$solder = '共晶';
//		break;
//	case 2:
//		$solder = 'RoHS';
//		break;
//	case 3:
//		$solder = '混在';
//		break;
//	}

//	switch ($row['r_size']) {
//	case 0:
//		$r_size = '';
//		break;
//	case 1:
//		$r_size = '小';
//		break;
//	case 2:
//		$r_size = '大';
//		break;
//	}

	switch ($row['exp_item']) {
	case 0:
		$exp_item = '';
		break;
	case 1:
		$exp_item = '高額';
		break;
	}

	if ($row['solder']==2) {
		$cell_fmt = array($f6, $f8);
	} else {
		$cell_fmt = array($f3, $f5);
	}

	// 2008/01/22 19文字以上の品名の抽出
//	$product_len = strlen($row['product']);
//	$p_new_len = strlen($row['p_new']);

//	if ($product_len>=19 or $p_new_len>=19) {
//		$worksheet->writeString($r, 0, mb_convert_encoding($row['rack_old'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 1, mb_convert_encoding($row['product'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 2, mb_convert_encoding(solder_name($row['solder']), "SJIS"), $cell_fmt[1]);
//		$worksheet->writeString($r, 3, mb_convert_encoding($row['p_new'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 4, mb_convert_encoding($row['p_maker'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 5, mb_convert_encoding($row['p_sub'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 6, mb_convert_encoding($row['p_nec'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 7, mb_convert_encoding(r_size_name($row['r_size']), "SJIS"), $cell_fmt[1]);
//		$worksheet->writeString($r, 8, mb_convert_encoding($exp_item, "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 9, mb_convert_encoding($row['rack_no'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 10, mb_convert_encoding($row['rem_part'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 11, mb_convert_encoding($row['rem_rack'], "SJIS"), $cell_fmt[0]);
//		$worksheet->writeString($r, 12, mb_convert_encoding($row['smt_id'], "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 0, mb_convert_encoding($row['rack_old'], "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 1, mb_convert_encoding($row['product'], "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 2, mb_convert_encoding(solder_name($row['solder']), "SJIS"), $cell_fmt[1]);
		$worksheet->writeString($r, 3, mb_convert_encoding($row['p_maker'], "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 4, mb_convert_encoding(r_size_name($row['r_size']), "SJIS"), $cell_fmt[1]);
		$worksheet->writeString($r, 5, mb_convert_encoding($exp_item, "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 6, mb_convert_encoding($row['rem_part'], "SJIS"), $cell_fmt[0]);
		$worksheet->writeString($r, 7, mb_convert_encoding($row['rem_rack'], "SJIS"), $cell_fmt[0]);
		$r++;
//	}

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
