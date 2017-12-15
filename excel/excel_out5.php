<?php
/*
[生産管理システム]

 * SMT部品 使用数リスト出力(Excel出力)

  2007/07/26 前回の残骸から次回も使える程度に復活
  2007/09/22 細かい修正
  2007/10/04 項目[Index]コメントアウト(別にいらない？)
  2007/11/26 サブ品名、NEC品名も検索範囲に入れる
  2007/12/18 高額品 変換処理追加
  2008/01/11 最終入庫リール情報を追加 棚番検索処理を修正
  2008/01/15 棚番が検索出来なかった場合の初期化が抜けていたので修正
  2008/03/24 在庫数情報 追加
  2008/03/27 在庫数によるセル色変更は一旦コメントアウト(未完成の為)
  2008/04/07 登録された管理番号から集計したデータを追加
  2009/08/24 使用していない表示データの整理
  2010/03/04 現在の集計済み在庫数の表示を追加

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

// 棚番検索
function rackno_search($mdb2, $search_sql) {

	$res_row = $mdb2->queryRow($search_sql);
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]!=NULL) {
		$part_data[0] = $res_row[0];	// $smt_id
		$part_data[1] = $res_row[1];	// $rack_no
		$part_data[2] = $res_row[3];	// $solder
		$part_data[3] = $res_row[10];	// $rack_old
		$part_data[4] = $res_row[9];	// $exp_item
	} else {
		unset($part_data);
	}

	return ($part_data);

}

// ファイル名の取得
$csv_file = $_GET["csv_file"];

// Excelブック出力
$book_name = "SMT_USE_" . date("Y-m-d_H_i_s");
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(12, 10, 10);					// フォントサイズ
$cell_size = array(25, 20, 15, 12, 10, 8, 5);	// セルサイズ
$cell_pos = array(0, 0, 0, 10);					// タイトルのセル位置設定

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

// [日付]
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

// [不明データ 左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[0]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(41);

// [不明データ 右詰]
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[0]);
$f7->setAlign ('right');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(41);

// [不明データ 中央]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[0]);
$f8->setAlign ('center');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(41);

// [不足？データ 左詰]
$f9 =& $workbook->addFormat();
$f9->setFontFamily("MS UI Gothic");
$f9->setSize($font_size[0]);
$f9->setAlign ('left');
$f9->setAlign('vcenter');
$f9->setBorder (1);
$f9->setColor(0);
$f9->setFgColor(45);

// [不足？データ 右詰]
$f10 =& $workbook->addFormat();
$f10->setFontFamily("MS UI Gothic");
$f10->setSize($font_size[0]);
$f10->setAlign ('right');
$f10->setAlign('vcenter');
$f10->setBorder (1);
$f10->setColor(0);
$f10->setFgColor(45);

// [不足？データ 中央]
$f11 =& $workbook->addFormat();
$f11->setFontFamily("MS UI Gothic");
$f11->setSize($font_size[0]);
$f11->setAlign ('center');
$f11->setAlign('vcenter');
$f11->setBorder (1);
$f11->setColor(0);
$f11->setFgColor(45);

// [項目 在庫数 情報(安斎H 集計データ]
$f12 =& $workbook->addFormat();
$f12->setFontFamily("MS UI Gothic");
$f12->setSize($font_size[0]);
$f12->setAlign('center');
$f12->setAlign('vcenter');
$f12->setBorder(1);
$f12->setColor(0);
$f12->setFgColor(24);

// [項目 リール情報(登録データ)]
$f13 =& $workbook->addFormat();
$f13->setFontFamily("MS UI Gothic");
$f13->setSize($font_size[0]);
$f13->setAlign('center');
$f13->setAlign('vcenter');
$f13->setBorder(1);
$f13->setColor(0);
$f13->setFgColor(51);

// [項目 管理番号 集計データ(登録データ)]
$f14 =& $workbook->addFormat();
$f14->setFontFamily("MS UI Gothic");
$f14->setSize($font_size[0]);
$f14->setAlign('center');
$f14->setAlign('vcenter');
$f14->setBorder(1);
$f14->setColor(0);
$f14->setFgColor(11);


// シートの追加
$worksheet =& $workbook->addWorksheet("smt_use");

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
$worksheet->setColumn(1, 1, $cell_size[0]);		// 品名
$worksheet->setColumn(2, 2, $cell_size[5]);		// 数量
$worksheet->setColumn(3, 3, $cell_size[4]);		// はんだ
//$worksheet->setColumn(4, 4, $cell_size[2]);	// 新棚番
$worksheet->setColumn(4, 4, $cell_size[5]);		// 高額品

$worksheet->setColumn(4, 5, $cell_size[5]);		// 在庫数
$worksheet->setColumn(4, 6, $cell_size[2]);		// 更新日

//$worksheet->setColumn(4, 5, $cell_size[5]);	// 使用完リール(管理番号からの集計データ)
//$worksheet->setColumn(4, 6, $cell_size[5]);	// リール総数

$worksheet->setColumn(5, 7, $cell_size[3]);		// 管理番号
$worksheet->setColumn(6, 8, $cell_size[0]);		// リール品名
$worksheet->setColumn(7, 9, $cell_size[5]);		// 入庫数量
$worksheet->setColumn(8, 10, $cell_size[2]);	// 入庫日
$worksheet->setColumn(9, 11, $cell_size[6]);	//

// タイトル
$disp = "使用部品(SMT)集計データ [" . $csv_file . "] 作成日：". date("Y-m-d H:i");
$worksheet->writeString($cell_pos[0], $cell_pos[1], mb_convert_encoding($disp, "SJIS"), $f0);
$worksheet->mergeCells($cell_pos[0], $cell_pos[1], $cell_pos[2], $cell_pos[3] );

// 項目
$worksheet->writeString(2, 0, mb_convert_encoding("棚番","SJIS"), $f2);
$worksheet->writeString(2, 1, mb_convert_encoding("品名","SJIS"), $f2);
$worksheet->writeString(2, 2, mb_convert_encoding("数量","SJIS"), $f2);
$worksheet->writeString(2, 3, mb_convert_encoding("はんだ","SJIS"), $f2);
//$worksheet->writeString(2, 4, mb_convert_encoding("新棚番","SJIS"), $f2);
$worksheet->writeString(2, 4, mb_convert_encoding("高額品","SJIS"), $f2);

$worksheet->writeString(2, 5, mb_convert_encoding("在庫数","SJIS"), $f12);
$worksheet->writeString(2, 6, mb_convert_encoding("更新日","SJIS"), $f12);

//$worksheet->writeString(2, 5, mb_convert_encoding("使用完","SJIS"), $f14);
//$worksheet->writeString(2, 6, mb_convert_encoding("登録数","SJIS"), $f14);

$worksheet->writeString(2, 7, mb_convert_encoding("管理番号","SJIS"), $f13);
$worksheet->writeString(2, 8, mb_convert_encoding("リール品名","SJIS"), $f13);
$worksheet->writeString(2, 9, mb_convert_encoding("数量","SJIS"), $f13);
$worksheet->writeString(2, 10, mb_convert_encoding("入庫日","SJIS"), $f13);

// DB接続
$mdb2 = db_connect();

$res_query = $mdb2->query("SELECT * FROM use_smt ORDER BY use_id");
$res_query = err_check($res_query);

$r = 3;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	// 部品名で検索
	$use_part = $row['use_part'];
	$use_qty  = $row['use-qty'];

	$part_data = rackno_search($mdb2, "SELECT * FROM part_smt WHERE product='$use_part'");
	if ($part_data==NULL) {

		$part_data = rackno_search($mdb2, "SELECT * FROM part_smt WHERE p_new='$use_part'");
		if ($part_data==NULL) {

			$part_data = rackno_search($mdb2, "SELECT * FROM part_smt WHERE p_maker='$use_part'");
			if ($part_data==NULL) {

				$part_data = rackno_search($mdb2, "SELECT * FROM part_smt WHERE p_sub='$use_part'");
				if ($part_data==NULL) {

					$part_data = rackno_search($mdb2, "SELECT * FROM part_smt WHERE p_nec='$use_part'");
					if ($part_data==NULL) {

					}
				}
			}
		}
	}

	if ($part_data!=NULL) {
		$smt_id   = $part_data[0];
		$rack_no  = $part_data[1];
		$solder   = $part_data[2];
		$rack_old = $part_data[3];
		$exp_item = $part_data[4];
	} else {
		unset($smt_id);
		unset($rack_no);
		unset($solder);
		unset($rack_old);
		unset($exp_item);
	}

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

	switch ($exp_item) {
	case 0:
		$exp_item = '';
		break;
	case 1:
		$exp_item = '高額';
		break;
	}

	// 部品リール情報 検索
	$part_tmp = $use_part . '%';
//	$res_tmp = $mdb2->queryRow("SELECT * FROM reel_no WHERE product LIKE '$use_part' OR p_maker LIKE '$use_part' ORDER BY due_date, check_no, r_seq, r_total desc");
	$res_row = $mdb2->queryRow("SELECT * FROM reel_no WHERE product LIKE '$part_tmp' ORDER BY due_date DESC");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]!=NULL) {
		$due_date = $res_row[1];
		$check_no = $res_row[2];
		$product  = $res_row[6];
		$reel_qty = $res_row[9];
	} else {
		unset($due_date);
		unset($check_no);
		unset($product);
		unset($reel_qty);
	}
	unset($res_row);

	// 在庫数 検索
//	if ($smt_id!=NULL) {
//		$res_row = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id'");
//		if (PEAR::isError($res_row)) {
//		} elseif ($res_row[0]!=NULL) {
//			$stk_reel  = $res_row[2];
//			$stk_qty   = $res_row[3];
//			$up_date   = $res_row[4];
//			$rem_stock = $res_row[5];
//		} else {
//			unset($stk_reel);
//			unset($stk_qty);
//			unset($up_date);
//			unset($rem_stock);
//		}
//		unset($res_row);
//	} else {
//		unset($stk_reel);
//		unset($stk_qty);
//		unset($up_date);
//		unset($rem_stock);
//	}

	if ($smt_id!=NULL) {
		$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} elseif ($res_row[0]!=NULL) {
			$unit_cost = $res_row[2];
			$qty_stock = $res_row[3];
			$up_date   = $res_row[4];
			$rem_stock = $res_row[5];
		} else {
			unset($unit_cost);
			unset($qty_stock);
			unset($up_date);
			unset($rem_stock);
		}
		unset($res_row);
	} else {
		unset($unit_cost);
		unset($qty_stock);
		unset($up_date);
		unset($rem_stock);
	}

	// 2008/04/07 登録リールの総数と使用数のカウント
	$part_tmp = $use_part . '%';
	$res_tmp = $mdb2->query("SELECT * FROM reel_no WHERE product LIKE '$part_tmp' OR p_maker LIKE '$part_tmp' ORDER BY reel_id");
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
	unset($row_tmp);
	unset($r_cnt);
	unset($r_cmp);

	// セル色の設定
	// 1.検索出来ない物 -> 水色
	// 2.在庫数がNULL又は0では無く在庫数が所要数×1.2より多い場合   -> 白
	// 3.                         在庫数が所要数×1.2より少ない場合 -> ピンク
	// 4.                         リール数が1より多い               -> 白
	// 5.                         リール数が1以下                   -> ピンク
	if ($rack_no==NULL or $rack_old==NULL) {

		$cell_fmt = array($f6, $f7, $f8);

	} else {

//		if ($stk_qty!=NULL or $stk_qty!=0) {
//
//			if ($stk_qty>($use_qty*1.2)) {
//
//				$cell_fmt = array($f3, $f4, $f5);
//
//			} elseif ($stk_reel>1) {
//
//				$cell_fmt = array($f3, $f4, $f5);
//
//			} else {
//
//				$cell_fmt = array($f9, $f10, $f11);
//
//			}
//
//		} else {
//
//			$cell_fmt = array($f3, $f4, $f5);
//
//		}

		$cell_fmt = array($f3, $f4, $f5);
	}

	$worksheet->writeString($r, 0, mb_convert_encoding($rack_old, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 1, mb_convert_encoding($use_part, "SJIS"), $cell_fmt[0]);
	$worksheet->writeNumber($r, 2, mb_convert_encoding($row['use_qty'], "SJIS"), $cell_fmt[1]);
	$worksheet->writeString($r, 3, mb_convert_encoding($solder_name, "SJIS"), $cell_fmt[2]);
//	$worksheet->writeString($r, 4, mb_convert_encoding($rack_no, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 4, mb_convert_encoding($exp_item, "SJIS"), $cell_fmt[2]);

	$worksheet->writeNumber($r, 5, mb_convert_encoding($qty_stock, "SJIS"), $cell_fmt[1]);
	$worksheet->writeString($r, 6, mb_convert_encoding($up_date, "SJIS"), $cell_fmt[2]);

//	$worksheet->writeNumber($r, 5, mb_convert_encoding($reel_cmp, "SJIS"), $cell_fmt[1]);
//	$worksheet->writeNumber($r, 6, mb_convert_encoding($reel_cnt, "SJIS"), $cell_fmt[1]);

	$worksheet->writeString($r, 7, mb_convert_encoding($check_no, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 8, mb_convert_encoding($product, "SJIS"), $cell_fmt[0]);
	$worksheet->writeString($r, 9, mb_convert_encoding($reel_qty, "SJIS"), $cell_fmt[1]);
	$worksheet->writeString($r, 10, mb_convert_encoding($due_date, "SJIS"), $cell_fmt[2]);

	if ($rack_no==NULL) {
		$worksheet->writeString($r, 11, mb_convert_encoding('*', "SJIS"), $cell_fmt[2]);
	}
	$r++;

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
