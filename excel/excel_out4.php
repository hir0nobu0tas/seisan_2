<?php
/*
[生産管理システム]

 *在庫数一覧 出力(Excel出力)

  2008/03/18
  2008/03/26 在庫データの検索処理 追加
  2008/03/27 リール管理番号リストを検索して本数を追加(テスト)
  2008/04/02
  2008/07/94 テスト用に出力件数を500件に制限
  2009/02/12 表示項目整理 在庫はトータルでの在庫数のみとする
  2009/02/14 出力フォーマット変更(表示項目追加)
  2009/08/24 在庫備考 表示削除
  2009/10/15 在庫数処理 修正
  2015/10/05 新棚番 表示追加

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
$book_name = "FS_QTY_" . date("Y-m-d_H_i_s");
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(12, 10, 10);					// フォントサイズ
$cell_size = array(30, 25, 20, 12, 10, 8, 5);	// セルサイズ
$cell_pos = array(0, 0, 0, 11);					// タイトルのセル位置設定

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

// [データ(共晶)左詰]
$f3 =& $workbook->addFormat();
$f3->setFontFamily("MS UI Gothic");
$f3->setSize($font_size[2]);
$f3->setAlign ('left');
$f3->setAlign('vcenter');
$f3->setBorder (1);
$f3->setColor(0);
$f3->setFgColor(9);

// [データ(共晶)右詰]
$f4 =& $workbook->addFormat();
$f4->setFontFamily("MS UI Gothic");
$f4->setSize($font_size[2]);
$f4->setAlign ('right');
$f4->setAlign('vcenter');
$f4->setBorder (1);
$f4->setColor(0);
$f4->setFgColor(9);

// [データ(共晶)中央]
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
$f6->setFgColor(43);

// [データ(PBF)右詰]
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[2]);
$f7->setAlign ('right');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(43);

// [データ(PBF)中央]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[2]);
$f8->setAlign ('center');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(43);

// [項目 在庫数]
$f9 =& $workbook->addFormat();
$f9->setFontFamily("MS UI Gothic");
$f9->setSize($font_size[2]);
$f9->setAlign('center');
$f9->setAlign('vcenter');
$f9->setBorder(1);
$f9->setColor(0);
$f9->setFgColor(24);

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
$worksheet->setColumn(0, 0, $cell_size[4]);		// 旧棚番
$worksheet->setColumn(1, 1, $cell_size[4]);		// 新棚番
$worksheet->setColumn(2, 2, $cell_size[1]);		// 品名
$worksheet->setColumn(3, 3, $cell_size[5]);		// はんだ
$worksheet->setColumn(4, 4, $cell_size[1]);		// メーカー品名
$worksheet->setColumn(5, 5, $cell_size[4]);		// リール
$worksheet->setColumn(6, 6, $cell_size[5]);		// 高額品
$worksheet->setColumn(7, 7, $cell_size[2]);		// 部品備考
$worksheet->setColumn(8, 8, $cell_size[2]);		// 棚番備考
$worksheet->setColumn(9, 9, $cell_size[5]);		// 在庫数
$worksheet->setColumn(10, 10, $cell_size[4]);	// 更新日
$worksheet->setColumn(11, 11, $cell_size[3]);	// 在庫備考

// タイトル
$disp = "SMT(福島ストック品) 登録品 一覧 作成日：". date("Y-m-d H:i");
$worksheet->writeString($cell_pos[0], $cell_pos[1], mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells($cell_pos[0], $cell_pos[1], $cell_pos[2], $cell_pos[3] );

// 項目
$worksheet->writeString(2, 0, mb_convert_encoding("旧棚番","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("新棚番","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("品名","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("はんだ","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("メーカー品名","SJIS"), $f2);
$worksheet->writeString(2, 5, mb_convert_encoding("リール","SJIS"), $f2);
$worksheet->writeString(2, 6, mb_convert_encoding("高額品","SJIS"), $f2);
$worksheet->writeString(2, 7, mb_convert_encoding("部品備考","SJIS"), $f2);
$worksheet->writeString(2, 8, mb_convert_encoding("棚番備考","SJIS"), $f2);
$worksheet->writeString(2, 9, mb_convert_encoding("在庫数","SJIS"), $f9);
$worksheet->writeString(2, 10, mb_convert_encoding("更新日","SJIS"), $f9);
$worksheet->writeString(2, 11, mb_convert_encoding("在庫備考","SJIS"), $f9);

// DB接続
$mdb2 = db_connect();

// 2008/12/08 2000件に変更
// 2009/02/12
//$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY rack_old LIMIT 2000 OFFSET 1");
$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY rack_old");
$res_query = err_check($res_query);

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id   = $row['smt_id'];
	$rack_old = $row['rack_old'];
	$rack_no  = $row['rack_no'];
	$product  = $row['product'];
	$solder   = $row['solder'];
	$p_maker  = $row['p_maker'];
	$r_size   = $row['r_size'];
	$exp_item = $row['exp_item'];
	$rem_part = $row['rem_part'];
	$rem_rack = $row['rem_rack'];

	if ($solder==2) {
		$cell_fmt = array($f6, $f7, $f8);
	} else {
		$cell_fmt = array($f3, $f4, $f5);
	}

	// 在庫数 検索
	if ($smt_id!=NULL) {
		$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} elseif ($res_row[0]!=NULL) {
			$qty_stock = $res_row[3];
			$up_date   = $res_row[4];
			$rem_stock = $res_row[5];
		} else {
			unset($qty_stock);
			unset($up_date);
			unset($rem_stock);
		}
		unset($res_row);
	} else {
		unset($qty_stock);
		unset($up_date);
		unset($rem_stock);
	}

/*
	// 登録リールの総数と使用数のカウント
	$product_tmp = $product . '%';
//	$reel_cnt = $mdb2->queryOne("SELECT COUNT(*) FROM reel_no WHERE product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp'");
//	$reel_cnt = err_check($reel_cnt);
//	$reel_cmp = $mdb2->queryOne("SELECT COUNT(*) FROM reel_no WHERE (product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp') AND end_date IS NOT NULL");
//	$reel_cmp = err_check($reel_cmp);

	$res_tmp = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp' ORDER BY reel_id");
	$res_tmp = err_check($res_tmp);

	$cnt = 0;
	$cmp = 0;
	while($row_tmp = $res_tmp->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		// 登録全数
		$r_cnt[$cnt] = $row_tmp['reel_id'];
		$cnt++;

		// 使用完数
		if ($row_tmp['end_date']!=NULL) {
			$r_cmp[$cmp] = $row_tmp['reel_id'];
			$cmp++;
		}

	}

	$reel_cnt = count($r_cnt);
	$reel_cmp = count($r_cmp);
	unset($r_cnt);
	unset($r_cmp);
*/

	$worksheet->writeString($r, 0, mb_convert_encoding($rack_old, "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 1, mb_convert_encoding($rack_no, "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 2, mb_convert_encoding($product, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 3, mb_convert_encoding(solder_name($solder), "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 4, mb_convert_encoding($p_maker, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 5, mb_convert_encoding(r_size_name($r_size), "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 6, mb_convert_encoding(exp_item_name($exp_item), "SJIS"), $cell_fmt[2]);
	$worksheet->writeString($r, 7, mb_convert_encoding($rem_part, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 8, mb_convert_encoding($rem_rack, "SJIS"), $cell_fmt[0]);
	$worksheet->writeNumber($r, 9, mb_convert_encoding($qty_stock, "SJIS"), $cell_fmt[1]);
	$worksheet->writeString($r, 10, mb_convert_encoding($up_date, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 11, mb_convert_encoding($rem_stock, "SJIS"), $cell_fmt[0]);

	$r++;

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
