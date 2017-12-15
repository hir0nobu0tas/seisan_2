<?php
/*
[生産管理システム]

 *フィードバック用フォーマット(Excel出力)

  2007/07/17
  手書きデータのフィードバック用にデータを取込みやすいフォーマットで
  ダンプする(予定)

  2007/08/23
  棚番検索で検索部品名の先頭[%]を削除 期待した棚番が検索出来ていない場合がある

  2007/08/27
  データの確認状況と確認日の項目追加
  確認状況

  2007/11/29
  細かい修正

  2008/01/15
  RoHS品を検索(共晶品の処理は未解決)

  2008/10/23
  フォーマット変更

  2008/10/24
  「共用」追加

  2009/03/05
  ステーション備考追加

  2009/03/11
  棚番検索の「Like検索」を止めて「完全一致」で検索へ変更
  品名(棚番違い)による部品形状違いとのクレームがあったので「完全一致」とする

  2009/03/13
  はんだ種別で「不明」対応(「不明」は表示しない)

  2009/05/13
  品名備考追加(指定品名やCBE品名等の読み替え後の品名の登録用)
  フォーマット変更

  2009/05/19
  シート備考対応

  2009/06/04
  アドバンス向け対応 実装面種別が -01 → -A1 の場合、専用の棚番リストで棚番検索
  に対応

  2009/07/08
  アドバンス棚番データのテーブル定義変更対応(品名を5種まで登録)

  2015/08/24
  CBE品名の場合は通常品名を備考(品名)に出力するように修正
  セットアップシートと処理と同等にする

  2015/09/18
  CBE品名の検索で通常品名が検索出来なかった場合にセル書式が設定されていなかったので修正

  2015/10/29
  新棚番対応

  2015/11/17
  CBE品名の場合、棚番指定で無い時にはDBに登録している通常品名を表示するように修正
  (以前の登録品名とは違う場合があるので現場からの修正要望有り)

  2017/07/31
  日付処理修正
  mb_convert_encoding で変換前の文字コードを指定("UTF-8")

 */
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once '../inc/parameter_inc.php';
include_once '../lib/com_func1.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

// 2017/07/31 追加
date_default_timezone_set('Asia/Tokyo');

//---------------------------------------------------------------

// 引数の取得
$unit_id = $_GET["id"];

// DB接続
$mdb2 = db_connect();

// 作業用一時テーブル作成(棚番ソートの為)
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
					data9 TEXT,
					data10 TEXT,
					data11 TEXT,
					CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");

// ステーション数＝Excelシート数
//$res_one = $mdb2->queryOne("SELECT COUNT(*) FROM station_data WHERE unit_id='$unit_id'");
//if (PEAR::isError($res_one)) {
//} else {
//	$sh_cnt = $res_one;
//}

$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id'");
if (PEAR::isError($unit_row)) {
} else {
	$u_index    = $unit_row[1];
	$file_name  = $unit_row[2];
	$board      = $unit_row[3];
	$product    = $unit_row[4];
	$refix_date = $unit_row[5];
	$chk_status = $unit_row[6];
	$chk_date   = substr($unit_row[7], 0, 10);	// 年月のみ抽出
	$rem_unit   = $unit_row[8];
}

// 2009/06/04 アドバンス向け対応
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
} elseif ($side_tmp=='-A3') {
	$rack_type = 'Advance';
}

// Excelブック出力
//$book_name = "UPDATE_" . $file_name . '-' . $dash_no;
$book_name = "UPDATE_" . $file_name;
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 書式
$font_size = array(10, 12, 14, 26);				// フォントサイズ
$cell_size = array(30, 25, 20, 13, 10, 8, 5);	// セルサイズ

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

// [基本データ 中央]
$f1 =& $workbook->addFormat();
$f1->setFontFamily("MS UI Gothic");
$f1->setSize($font_size[0]);
//$f1->setAlign('left');
$f1->setAlign('center');
$f1->setAlign('vcenter');
$f1->setBorder(1);
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

// [入力データ 左詰]
$f6 =& $workbook->addFormat();
$f6->setFontFamily("MS UI Gothic");
$f6->setSize($font_size[0]);
$f6->setAlign ('left');
$f6->setAlign('vcenter');
$f6->setBorder (1);
$f6->setColor(0);
$f6->setFgColor(26);

// [入力データ 中央]
$f7 =& $workbook->addFormat();
$f7->setFontFamily("MS UI Gothic");
$f7->setSize($font_size[0]);
$f7->setAlign ('center');
$f7->setAlign('vcenter');
$f7->setBorder (1);
$f7->setColor(0);
$f7->setFgColor(26);

// [基礎データ 左詰]
$f8 =& $workbook->addFormat();
$f8->setFontFamily("MS UI Gothic");
$f8->setSize($font_size[0]);
$f8->setAlign ('left');
$f8->setAlign('vcenter');
$f8->setBorder (1);
$f8->setColor(0);
$f8->setFgColor(27);

// [データ 左詰](旧棚番表示 セル色 赤)
$f9 =& $workbook->addFormat();
$f9->setFontFamily("MS UI Gothic");
$f9->setSize($font_size[0]);
$f9->setAlign ('left');
$f9->setAlign('vcenter');
$f9->setBorder (1);
$f9->setColor(0);
$f9->setFgColor(2);

// シートの追加
$sheet =& $workbook->addWorksheet("update_data");

// シート書式設定
// ページフォーマット指定(A4横)
$sheet->setPaper(9);
$sheet->setPortrait();
//$sheet->setLandscape();

// ページ書式
$sheet->setMarginTop(0.4);		// 上マージン
$sheet->setMarginBottom(0.4);	// 下マージン
//$sheet->setMarginLeft(0.4);	// 左マージン
$sheet->setMarginLeft(0.5);		// 左マージン
$sheet->setMarginRight(0.2);	// 右マージン
$sheet->fitToPages(1,0);		// 横1ページに収める
$sheet->hideGridlines();		// 枠線を隠す

// 列幅設定
$sheet->setColumn( 0,  0, $cell_size[5]);	// UNIT			ST			 SET
$sheet->setColumn( 1,  1, $cell_size[5]);	// 	 						 穴番号
$sheet->setColumn( 2,  2, $cell_size[0]);	// 基板名       プログラム   部品名
$sheet->setColumn( 3,  3, $cell_size[4]);	// データ更新日  ステーション 荷姿
$sheet->setColumn( 4,  4, $cell_size[3]);	// 確認状況					 新棚番
$sheet->setColumn( 5,  5, $cell_size[5]);	// 確認日					 はんだ
$sheet->setColumn( 6,  6, $cell_size[4]);	//							 棚番
$sheet->setColumn( 7,  7, $cell_size[4]);	//							 旧データ
$sheet->setColumn( 8,  8, $cell_size[6]);	//							 共用
$sheet->setColumn( 9,  9, $cell_size[6]);	//							 適用
$sheet->setColumn(10, 10, $cell_size[0]);	//							 備考
$sheet->setColumn(11, 11, $cell_size[0]);	//							 備考(部品)

// タイトル
//$disp1 = "データ修正用 基板名[ ";
//$disp2 = " ]  工番[ ";
//$disp3 = " ]  ダッシュ[ ";
//$disp4 = " ]  数量[ ";
//$disp5 = " ]  出力日：" . date("Y-m-d H:i");
//$disp = $disp1 . $board . $disp2 . $operate_no . $disp3 . $dash_no . $disp4 . $mf_qty . $disp5;
//$sheet->writeString(0, 0, mb_convert_encoding($disp, "SJIS"), $f0);
//$sheet->mergeCells(0, 0, 0, 10);
//
//$sheet->writeString(2, 3, mb_convert_encoding("更新日", "SJIS"), $f2);
//$sheet->writeString(2, 4, mb_convert_encoding($refix_date, "SJIS"), $f1);
//$sheet->mergeCells(2, 4, 2, 5);

$sheet->writeString(0, 0, mb_convert_encoding("UNIT_ID","SJIS", "UTF-8"), $f2);
$sheet->writeString(0, 2, mb_convert_encoding("基板名","SJIS", "UTF-8"), $f2);
$sheet->writeString(0, 3, mb_convert_encoding("マシンデータ更新日","SJIS", "UTF-8"), $f2);
$sheet->mergeCells(0, 3, 0, 4);
$sheet->writeString(0, 5, mb_convert_encoding("確認状況", "SJIS", "UTF-8"), $f2);
$sheet->writeString(0, 6, mb_convert_encoding("確認日", "SJIS", "UTF-8"), $f2);
$sheet->mergeCells(0, 6, 0, 7);

$sheet->writeString(1, 0, mb_convert_encoding($unit_id,"SJIS", "UTF-8"), $f1);
$sheet->writeString(1, 2, mb_convert_encoding($board,"SJIS", "UTF-8"), $f8);
$sheet->writeString(1, 3, mb_convert_encoding($refix_date,"SJIS", "UTF-8"), $f8);
$sheet->mergeCells(1, 3, 1, 4);
$sheet->writeString(1, 5, mb_convert_encoding($chk_status, "SJIS", "UTF-8"), $f8);
$sheet->writeString(1, 6, mb_convert_encoding($chk_date,"SJIS", "UTF-8"), $f8);
$sheet->mergeCells(1, 6, 1, 7);

//
$sql_select = "SELECT * FROM set_data T3 JOIN station_data T2 ON(T3.st_id=T2.st_id) JOIN unit_data T1 ON(T2.unit_id=T1.unit_id)";
//$sql_where = " WHERE board='$board' AND u_index='$u_index'";
$sql_where = " WHERE file_name='$file_name' AND u_index='$u_index'";
$sql_order = " ORDER BY set_id";
$search_sql = $sql_select . $sql_where . $sql_order;
$res_query = $mdb2->query($search_sql);
$res_query = err_check($res_query);

$r = 2;
$cnt = 0;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$chk[0] = $row['st_index'];
	$sh_index = $row['st_index'] + 1;
//	if ($row['data_sel']==0) {
//		$rack_sel = '新';
//	} elseif ($row['data_sel']==1) {
//		$rack_sel = '旧';
//	}

//	$rack_sel = '';

	// 2009/05/12修正
	if ($row['data_sel']==1) {
		$rack_sel = '1';
	} else {
		$rack_sel = '';
	}

	// 部品データ検索
	$p_data[$cnt] = $row['part'];

	$qty_row = $mdb2->queryRow("SELECT * FROM qty_data WHERE unit_id='$unit_id' AND q_part='$p_data[$cnt]'");
	if (PEAR::isError($qty_row)) {
	} else {
		$q_part = $qty_row[5];
		$qty    = $qty_row[6];
		$reel   = $qty_row[7];
	}

	// 重複品名の処理
	$key = array_search($q_part, $p_data);
	if ($key!=$cnt) {
		$qty = '--';
		$reel = '--';
	}
	$cnt++;

	// 棚番データ検索
	// 2007/08/23 先頭の[%]を削除 期待した棚番が検索出来ていない場合がある
	// 2009/03/11 「Like検索」を止めて「完全一致」で検索(現場での棚番確認が不十分？)
	// 2009/06/04 アドバンス向け対応
	if ($rack_type=='FS') {

//		$part =  '%' . $row['part'] . '%';
//		$part =  $row['part'] . '%';
//		$sql[1] = "SELECT * FROM part_smt";
//		$sql[2] = " WHERE product like '$part'";
//		$sql[3] = " OR p_new like '$part'";
//		$sql[4] = " OR p_maker like '$part'";
//		$sql[5] = " OR p_sub like '$part'";
//		$sql[6] = " OR p_nec like '$part'";
		$part =  $row['part'];
		$sql[1] = "SELECT * FROM part_smt";
		$sql[2] = " WHERE product='$part'";
		$sql[3] = " OR p_new='$part'";
		$sql[4] = " OR p_maker='$part'";
		$sql[5] = " OR p_sub='$part'";
		$sql[6] = " OR p_nec='$part'";
		$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6] . $sql[7];

		// 変数の初期化
		unset($rack_no);
		unset($solder);
		unset($rack_old);

		// 2008/01/15 RoHS品を検索(共晶品の処理は未解決)
		// 2009/07/10 RoHS品以外も検索へ変更
		$tmp_query = $mdb2->query($sql[0]);
		$tmp_query = err_check($tmp_query);
		while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			//if ($tmp_row['solder']=='2') {
				$rack_no  = $tmp_row['rack_no'];
				$solder   = $tmp_row['solder'];
				$rack_old = $tmp_row['rack_old'];
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

		$tmp_query = $mdb2->query($sql[0]);
		$tmp_query = err_check($tmp_query);
		while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$rack_old  = $tmp_row['rack_no'];
			$rack_no   = $tmp_row['rack_no'];
		}
		unset($sql);

	}

	// 2009/03/13 不明を表示させない為の仮対応
	switch($solder) {
	case 0:
		$solder_name = '';
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

	// 旧データ(旧棚番) -> 部品 検索
	// 2007/11/30 代わりに旧データ(備考)を入れる
//	$old_data = $row['old_data'];
//	unset($old_part);
//
//	if ($old_data!=NULL and $old_data!='NO DATA') {
//		$set_row = $mdb2->queryRow("SELECT * FROM smt_part WHERE rack_old like '$old_data' ORDER BY smt_id");
//		if (PEAR::isError($set_row)) {
//		} else {
//			$old_part = $set_row[2];
//		}
//	}

	// 旧データ(備考)
	$rem_set1 = $row['rem_set1'];
	$rem_set2 = $row['rem_set2'];

	// データ選択 2015/11/17追加
	$data_sel = $row['data_sel'];

	if ($chk[0]!=$chk[1]) {

		// 項目
		$r++;
		$sheet->writeString($r, 0, mb_convert_encoding("ST_ID","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r, 2, mb_convert_encoding("プログラム","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r, 3, mb_convert_encoding("ステーション","SJIS", "UTF-8"), $f2);
		$sheet->mergeCells($r, 3, $r, 5);
		$sheet->writeString($r, 6, mb_convert_encoding("備考(1)","SJIS", "UTF-8"), $f2);
		$sheet->mergeCells($r, 6, $r, 9);
		$sheet->writeString($r, 10, mb_convert_encoding("備考(2)","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r, 11, mb_convert_encoding("備考(3)","SJIS", "UTF-8"), $f2);
		$r++;

		$sheet->writeString($r, 0, mb_convert_encoding($row['st_id'], "SJIS", "UTF-8"), $f1);
		$sheet->writeString($r, 2, mb_convert_encoding($row['p_name'], "SJIS", "UTF-8"), $f8);
		$sheet->writeString($r, 3, mb_convert_encoding($row['s_name'], "SJIS", "UTF-8"), $f8);
		$sheet->mergeCells($r, 3, $r, 5);

		$sheet->writeString($r, 6, mb_convert_encoding($row['rem_st1'],"SJIS", "UTF-8"), $f6);
		$sheet->mergeCells($r, 6, $r, 9);
		$sheet->writeString($r, 10, mb_convert_encoding($row['rem_st2'],"SJIS", "UTF-8"), $f6);
		$sheet->writeString($r, 11, mb_convert_encoding($row['rem_st3'],"SJIS", "UTF-8"), $f6);
		$r++;

		// 項目
		$sheet->writeString($r,  0, mb_convert_encoding("SET_ID","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  1, mb_convert_encoding("穴番号","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  2, mb_convert_encoding("部品名","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  3, mb_convert_encoding("荷姿","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  4, mb_convert_encoding("旧棚番","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  5, mb_convert_encoding("はんだ","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  6, mb_convert_encoding("棚番","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  7, mb_convert_encoding("棚番データ","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  8, mb_convert_encoding("共用","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r,  9, mb_convert_encoding("適用","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r, 10, mb_convert_encoding("備考","SJIS", "UTF-8"), $f2);
		$sheet->writeString($r, 11, mb_convert_encoding("備考(品名)","SJIS", "UTF-8"), $f2);
		$r++;
	}

	// 2015/08/24 CBE品の場合 通常品名を備考欄へ追加(セットアップシートと同等処理)
	// 2015/10/26 新棚番対応
	$part_chk = substr($row['part'], 0, 3);

	$sheet->writeString($r,  0, mb_convert_encoding($row['set_id'], "SJIS", "UTF-8"), $f1);
	$sheet->writeString($r,  1, mb_convert_encoding($row['hole_no'], "SJIS", "UTF-8"), $f5);
	$sheet->writeString($r,  2, mb_convert_encoding($row['part'], "SJIS", "UTF-8"), $f3);
	$sheet->writeString($r,  3, mb_convert_encoding($row['packing'], "SJIS", "UTF-8"), $f5);
	$sheet->writeString($r,  4, mb_convert_encoding($rack_old, "SJIS", "UTF-8"),$f3);
	$sheet->writeString($r,  5, mb_convert_encoding($solder_name, "SJIS", "UTF-8"), $f5);
	$sheet->writeString($r,  6, mb_convert_encoding($rack_no, "SJIS", "UTF-8"), $f3);

	if (strlen($row['rack_data'])==8) {
		$sheet->writeString($r,  7, mb_convert_encoding($row['rack_data'], "SJIS", "UTF-8"), $f3);	// 新棚番を表示

	} elseif ($row['rack_data']=='ZN' or $row['rack_data']=='Z') {
		$sheet->writeString($r,  7, mb_convert_encoding($row['rack_data'], "SJIS", "UTF-8"), $f3);	// ZNかZの場合はそのまま表示新棚番を表示

	} elseif ($rack_sel=='1' and $row['rack_data']!='') {
		$sheet->writeString($r,  7, mb_convert_encoding($row['rack_data'], "SJIS", "UTF-8"), $f9);	// 旧棚番表示(セル色 赤)

	} else {
		$sheet->writeString($r,  7, mb_convert_encoding('', "SJIS", "UTF-8"), $f3);					// 棚番表示無し
	}

	//$sheet->writeString($r,  7, mb_convert_encoding($row['rack_data'], "SJIS"), $f3);	// 新棚番を表示

	$sheet->writeString($r,  8, mb_convert_encoding($row['p_share'], "SJIS", "UTF-8"),$f7);
	$sheet->writeString($r,  9, mb_convert_encoding($rack_sel, "SJIS", "UTF-8"),$f7);
	$sheet->writeString($r, 10, mb_convert_encoding($rem_set1, "SJIS", "UTF-8"),$f6);

	// 2015/08/24 CBE品の場合 通常品名を備考欄へ追加(セットアップシートと同等処理)
	// 2015/09/18 CBE品名で通常品名を検索出来なかった場合にセル書式がされていなかったので修正
	// 2015/11/17 新棚番は稼動品のみなのでここでは旧棚番で検索する 新規品は[YYYYMM_XX]の書式で採番している
	//if ($part_chk=='CBE' and $rem_set2==NULL) {
	if ($part_chk=='CBE' and $data_sel!=1) {

		$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old'");
		if (PEAR::isError($part_row)) {
		} else {
			$product = $part_row[2];
		}

		if ($product!=NULL) {
			$sheet->writeString($r, 11, mb_convert_encoding($product, "SJIS", "UTF-8"), $f6);
		} else {
			$sheet->writeString($r, 11, mb_convert_encoding('', "SJIS", "UTF-8"), $f6);
		}

		unset($product);

	} else {

		$sheet->writeString($r, 11, mb_convert_encoding($rem_set2, "SJIS", "UTF-8"),$f6);

	}

	$r++;

	$chk[1] = $row['st_index'];

}

//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
