<?php
/*
[生産管理システム]

 *セットアップシート用フォーマット(Excel出力)

  2007/04/11

  2007/06/21
  PEAR::DB -> PEAR::MDB2 など

  2007/07/12
  実際に使用できるレベルにフォーマット等変更中(まだ未完成)

  2007/07/18
  基板一覧から直接Excel出力するように手順を変更したので対応
  出力ファイル名変更(ファイル名[数量])

  2007/08/23
  棚番検索で検索部品名の先頭[%]を削除 期待した棚番が検索出来ていない場合がある

  2007/08/24
  データ更新用ファイルから取り込んだ手書きの旧棚番とそこから逆引きした品名表示
  追加

  2007/08/27
  データの確認状況と確認日の項目追加

  2007/11/29
  細かい修正

  2008/01/15
  RoHS品を検索(共晶品の処理は未解決)

  2008/10/31
  現場からの要望 対応中

  2008/11/12
  やっと現場要望のフォーマットで出力できるようになった(共用部品はまだ)

  2008/11/14
  共用部品 表示追加(暫定版)

  2009/02/09
  CBE品名で棚番が検索可能な場合、通常品名を備考欄に追加

  2009/03/06
  ステーション情報の取得時に [ORDER BY st_id] 追加(シート抜け対策)
  データ更新処理に対応(シート備考はまだ未完成)

  2009/03/11
  棚番検索の「Like検索」を止めて「完全一致」で検索へ変更
  品名(棚番違い)による部品形状違いとのクレームがあったので「完全一致」とする

  2009/03/18
  アドバンス向け対応 実装面種別を -01 → -A1 とする
  専用の棚番リストで棚番検索に対応

  2009/05/13
  現場要望によりフォーマット変更
  CBE品名等の読み替え後の品名を荷姿に代わりに追加 指定品名等もここに表示
  荷姿、はんだの表示廃止
  各シートに構成情報(両面/片面やステーション構成)を追加して欲しいとの要望
  もあったが表示方法など検討中

  2009/05/15
  シート備考対応

  2009/07/03
  棚番検索でRoHS品以外も検索対象とする(共晶オーダーも存在する為)

  2009/07/08
  アドバンス棚番データのテーブル定義変更対応(品名を5種まで登録)

  2009/07/31
  現場要望によりフォーマット変更
  リール数を廃止して1p使用数と使用総数へ変更、項目の順番変更など

  2009/08/07
  現場要望によりフォーマット変更
  備考の表示が切れてしまう場合があるので新棚番の表示を廃止

  2009/09/03
  CBE品名の場合は無条件で通常品名を検索して備考(品名)に出力していたが
  [set_data]の備考2(品名)に入力があった場合は入力された品名を出力する
  ように修正

  2015/08/24
  前回(2009/09/03)の修正が正常に動いていないので再度修正
  修正した時には動いていたはずだが、以降のシステムアップデートで動かなくなった？

  2015/09/15
  出力をExcel97/2000形式指定へ変更してみたが、まだ文字化け有り
  → 元へ戻す

  2015/09/18
  CBE品名の検索で通常品名が検索出来なかった場合にセル書式が設定されていなかったので修正

  2015/10/01
  実装面の備考が文字化けしていたので修正(以前コメントアウトしている所なので何か理由有り？)

  2015/10/27
  新棚番対応

  2015/11/17
  CBE品名の場合、棚番指定で無い時にはDBに登録している通常品名を表示するように修正
  (以前の登録品名とは違う場合があるので現場からの修正要望有り)

  2016/02/01
  高額品(単価500円以上)は棚番セル色をオレンジへ変更


  2016/02/11
  旧棚番対応品(今後の生産が無いので新棚番を採番しない)で仮に旧棚番出力へ変更

  2016/02/18
  棚番指定「R」が表示されていなかったので修正(新棚番対応へ戻した)

  2017/07/28
  日付処理修正
  mb_convert_encoding で変換前の文字コードを指定("UTF-8")


*/

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once '../inc/parameter_inc.php';
include_once "../lib/com_func1.php";

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

// 2017/07/28 追加
date_default_timezone_set('Asia/Tokyo');

//---------------------------------------------------------------

// 引数の取得
$unit_id    = $_GET["unit_id"];
$operate_no = $_GET["operate_no"];
$dash_no    = $_GET["dash_no"];
$mf_qty     = $_GET["mf_qty"];
$sort_sel   = $_GET["sort_sel"];

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
					data12 TEXT,
					CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");

// ユニット情報取得
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

// 面情報 取得
// 2009/03/18 アドバンス向け対応
$name_len = strlen($file_name);
$side_tmp = substr($file_name, $name_len - 3, 3);
if ($side_tmp=='-01') {
	$side = 'オモテ';
	$rack_type = 'FS';
} elseif ($side_tmp=='-02') {
	$side = 'ウラ';
	$rack_type = 'FS';
} elseif ($side_tmp=='-03') {
	$side = 'オモテ';
	$rack_type = 'FS';
} elseif ($side_tmp=='-04') {
	$side = 'ウラ';
	$rack_type = 'FS';
} elseif ($side_tmp=='-A1') {
	$side = 'オモテ';
	$rack_type = 'Advance';
} elseif ($side_tmp=='-A2') {
	$side = 'ウラ';
	$rack_type = 'Advance';
} elseif ($side_tmp=='-A3') {
	$side = 'オモテ';
	$rack_type = 'Advance';
} elseif ($side_tmp=='-A4') {
	$side = 'オモテ';
	$rack_type = 'Advance';
}

// ステーション数
// 2009/03/06 [ORDER BY st_id]追加(これでシート抜けが無くなる)
$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id' ORDER BY st_id");
$res_query = err_check($res_query);
$i = 0;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$st_id[$i] = $row['st_id'];
	$i++;
}

$st_count = count($st_id);


// ステーション種別
for ($st_index=0; $st_index<=($st_count - 1); $st_index++) {

	$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id' AND st_index='$st_index'");
	if (PEAR::isError($st_row)) {
	} else {
		$st_name[$st_index] = substr($st_row[4], 0, 2);
	}

}

// ステーション構成 初期化
unset($F1);
unset($R1);
unset($F2);
unset($MTS);
unset($F3);
unset($R3);
unset($MTC);

// ステーション構成 取得
for ($st=0; $st<=($st_count - 1); $st++) {

	$st_tmp = $st_id[$st];

	if ($st==0) {

		// プログラム名、ステーション名
		$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id' AND st_index='$st'");
		if (PEAR::isError($st_row)) {
		} elseif ($st_row[0]!=NULL) {
			$p_tmp[$st] = $st_row[3];
			$s_tmp[$st] = $st_row[4];
		}

		if ($st_name[$st]=='#1') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F1[0] = 1;		// データ有り=1 無し=NULL?
				$F1[1] = 0;		// st_index(ステーション)
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'R%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$R1[0] = 1;
				$R1[1] = 0;
			}

		} elseif ($st_name[$st]=='#2') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F2[0] = 1;
				$F2[1] = 0;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'MTS%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$MTS[0] = 1;
				$MTS[1] = 0;
			}

		} elseif ($st_name[$st]=='#3') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F3[0] = 1;
				$F3[1] = 0;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'R%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$R3[0] = 1;
				$R3[1] = 0;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'MTC%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$MTC[0] = 1;
				$MTC[1] = 0;
			}

		}

	} elseif ($st==1) {

		// プログラム名、ステーション名
		$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id' AND st_index='$st'");
		if (PEAR::isError($st_row)) {
		} elseif ($st_row[0]!=NULL) {
			$p_tmp[$st] = $st_row[3];
			$s_tmp[$st] = $st_row[4];
		}

		if ($st_name[$st]=='#2') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F2[0] = 1;
				$F2[1] = 1;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'MTS%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$MTS[0] = 1;
				$MTS[1] = 1;
			}

		} elseif ($st_name[$st]=='#3') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F3[0] = 1;
				$F3[1] = 1;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'R%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$R3[0] = 1;
				$R3[1] = 1;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'MTC%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$MTC[0] = 1;
				$MTC[1] = 1;
			}

		}

	} elseif ($st==2) {

		// プログラム名、ステーション名
		$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id' AND st_index='$st'");
		if (PEAR::isError($st_row)) {
		} elseif ($st_row[0]!=NULL) {
			$p_tmp[$st] = $st_row[3];
			$s_tmp[$st] = $st_row[4];
		}

		if ($st_name[$st]=='#3') {

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'F%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$F3[0] = 1;
				$F3[1] = 2;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'R%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$R3[0] = 1;
				$R3[1] = 2;
			}

			$st_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id='$st_tmp' AND hole_no LIKE 'MTC%'");
			if (PEAR::isError($st_row)) {
			} elseif ($st_row[0]!=NULL) {
				$MTC[0] = 1;
				$MTC[1] = 2;
			}

		}

	}

}

// シート構成(データがあるステーションの合計がExcelシート数)
$sh_count = $F1[0] + $R1[0] + $F2[0] + $MTS[0] + $F3[0] + $R3[0] + $MTC[0];

// シート名とプログラム名などの設定
$s_index = 1;
if ($F1[0]==1) {
	$st_name[$s_index] = '1F';
	$p_name[$s_index] = $p_tmp[$F1[1]];
	$s_name[$s_index] = $s_tmp[$F1[1]];
	$s_index++;
}

if ($R1[0]==1) {
	$st_name[$s_index] = '1R';
	$p_name[$s_index] = $p_tmp[$R1[1]];
	$s_name[$s_index] = $s_tmp[$R1[1]];
	$s_index++;
}

if ($F2[0]==1) {
	$st_name[$s_index] = '2F';
	$p_name[$s_index] = $p_tmp[$F2[1]];
	$s_name[$s_index] = $s_tmp[$F2[1]];
	$s_index++;
}

if ($MTS[0]==1) {
	$st_name[$s_index] = 'MTS';
	$p_name[$s_index] = $p_tmp[$MTS[1]];
	$s_name[$s_index] = $s_tmp[$MTS[1]];
	$s_index++;
}

if ($F3[0]==1) {
	$st_name[$s_index] = '3F';
	$p_name[$s_index] = $p_tmp[$F3[1]];
	$s_name[$s_index] = $s_tmp[$F3[1]];
	$s_index++;
}

if ($R3[0]==1) {
	$st_name[$s_index] = '3R';
	$p_name[$s_index] = $p_tmp[$R3[1]];
	$s_name[$s_index] = $s_tmp[$R3[1]];
	$s_index++;
}

if ($MTC[0]==1) {
	$st_name[$s_index] = 'MTC';
	$p_name[$s_index] = $p_tmp[$MTC[1]];
	$s_name[$s_index] = $s_tmp[$MTC[1]];
	$s_index++;
}

// Excelブック出力
//$book_name = "SETUP_SHEET_" . $operate_no . '-' . $dash_no;
$book_name = "SETUP_SHEET_" . $file_name . '[' . $mf_qty . ']';
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// 2015/09/15 Excel97/2000形式指定
//$workbook->setVersion(8);

// 書式
$font_size = array(10, 12, 14, 24);						// フォントサイズ
$cell_size = array(35, 25, 20, 17, 13, 10, 8, 7, 5, 3);	// セルサイズ

// セルフォーマット定義
include '../inc/excel_out_inc2.php';

// シートの追加(ステーション数)
for ($s_index=1; $s_index<=$sh_count; $s_index++) {
	$sheet[$s_index] =& $workbook->addWorksheet($st_name[$s_index]);
}

// シート書式設定
for ($s_index=1; $s_index<=$sh_count; $s_index++) {

	// 2015/09/15 文字コードをUTF-8で指定
	//$sheet[$s_index]->setInputEncoding('utf-8');

	// ページフォーマット指定(A4横)
	$sheet[$s_index]->setPaper(9);
	$sheet[$s_index]->setLandscape();

	// ページ書式
	$sheet[$s_index]->setMarginTop(0.4);	// 上マージン
	$sheet[$s_index]->setMarginBottom(0.4);	// 下マージン
	$sheet[$s_index]->setMarginLeft(0.5);	// 左マージン
	$sheet[$s_index]->setMarginRight(0.2);	// 右マージン
//	$sheet[$s_index]->fitToPages(1,0);		// 横1ページに収める
	$sheet[$s_index]->fitToPages(1,1);		// 縦、横1ページに収める
	$sheet[$s_index]->hideGridlines();		// 枠線を隠す

	// 列幅設定
	$sheet[$s_index]->setColumn( 0,  0, $cell_size[1]);	// 流用
	$sheet[$s_index]->setColumn( 1,  1, $cell_size[9]);	// 共用
	$sheet[$s_index]->setColumn( 2,  2, $cell_size[6]);	// 工番	  プログラム名  穴番号
	$sheet[$s_index]->setColumn( 3,  3, $cell_size[0]);	// ダッシュ	ステーション名	部品名
	$sheet[$s_index]->setColumn( 4,  4, $cell_size[1]);	// 数量				   備考(品名)
	$sheet[$s_index]->setColumn( 5,  5, $cell_size[5]);	//						 棚番
	$sheet[$s_index]->setColumn( 6,  6, $cell_size[8]);	//						 数量
	$sheet[$s_index]->setColumn( 7,  7, $cell_size[7]);	//						 総数
	$sheet[$s_index]->setColumn( 8,  8, $cell_size[3]);	//						 ロット
	$sheet[$s_index]->setColumn( 9,  9, $cell_size[0]);	//						 備考
//	$sheet[$s_index]->setColumn(10, 10, $cell_size[4]);	//						 新棚番

	// タイトル
	$disp1 = "セットアップシート 基板名[ ";
	$disp2 = " ]  工番[ ";
	$disp3 = " ]  ダッシュ[ ";
	$disp4 = " ]  数量[ ";
	$disp5 = " ]  出力日：" . date("Y-m-d H:i");
	$disp = $disp1 . $board . $disp2 . $operate_no . $disp3 . $dash_no . $disp4 . $mf_qty . $disp5;
	$sheet[$s_index]->writeString(0, 0, mb_convert_encoding($disp, "SJIS", "UTF-8"), $f0);
	$sheet[$s_index]->mergeCells(0, 0, 0, 9);

	$sheet[$s_index]->writeString(2, 4, mb_convert_encoding("更新日", "SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(2, 5, mb_convert_encoding($refix_date, "SJIS", "UTF-8"), $f1);
	$sheet[$s_index]->mergeCells(2, 5, 2, 7);

	$sheet[$s_index]->writeString(2, 8, mb_convert_encoding("確認状況", "SJIS", "UTF-8"), $f2);
	//	$sheet[$s_index]->mergeCells(2, 8, 2, 9);
	$sheet[$s_index]->writeString(2, 9, mb_convert_encoding($chk_status, "SJIS", "UTF-8"), $f7);

	$sheet[$s_index]->writeString(3, 8, mb_convert_encoding("確認日", "SJIS", "UTF-8"), $f2);
	//	$sheet[$s_index]->mergeCells(3, 8, 3, 9);
	$sheet[$s_index]->writeString(3, 9, mb_convert_encoding($chk_date, "SJIS", "UTF-8"), $f7);

	// ステーション情報
	$side_name = $side . ' ' . $st_name[$s_index];
	$sheet[$s_index]->writeString(2, 0, mb_convert_encoding($side_name, "SJIS", "UTF-8"), $f10);
	$sheet[$s_index]->mergeCells(2, 0, 3, 1);

	// [項目名] 基礎データ
//	$sheet[$s_index]->writeString(2, 2, mb_convert_encoding("プログラム","SJIS"), $f2);
	$sheet[$s_index]->writeString(2, 2, mb_convert_encoding("PG","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(2, 3, mb_convert_encoding($p_name[$s_index], "SJIS", "UTF-8"), $f1);

//	$sheet[$s_index]->writeString(3, 2, mb_convert_encoding("ステーション","SJIS"), $f2);
	$sheet[$s_index]->writeString(3, 2, mb_convert_encoding("ST","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(3, 3, mb_convert_encoding($s_name[$s_index], "SJIS", "UTF-8"), $f1);

	// [項目名] 項目
	$sheet[$s_index]->writeString(5,  0, mb_convert_encoding("流用","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  1, mb_convert_encoding("共","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  2, mb_convert_encoding("穴番号","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  3, mb_convert_encoding("部品名","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  4, mb_convert_encoding("備考(品名)","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  5, mb_convert_encoding("棚番","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  6, mb_convert_encoding("数量","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  7, mb_convert_encoding("総数","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  8, mb_convert_encoding("ロット","SJIS", "UTF-8"), $f2);
	$sheet[$s_index]->writeString(5,  9, mb_convert_encoding("備考","SJIS", "UTF-8"), $f2);
//	$sheet[$s_index]->writeString(5, 10, mb_convert_encoding("新棚番","SJIS", "UTF-8"), $f2);

}

// データ抽出
$sql_select = "SELECT * FROM set_data T3 JOIN station_data T2 ON(T3.st_id=T2.st_id) JOIN unit_data T1 ON(T2.unit_id=T1.unit_id)";
$sql_where = " WHERE file_name='$file_name' AND u_index='$u_index'";
$sql_order = " ORDER BY set_id";
$search_sql = $sql_select . $sql_where . $sql_order;
$res_query = $mdb2->query($search_sql);
$res_query = err_check($res_query);

// 作業用一時テーブルクリア
$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

//
$cnt = 0;
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$st_index         = $row['st_index'];
	$p_share          = $row['p_share'];
	$st_id[$st_index] = $row['st_id'];

	// 部品データ検索
	$p_data[$cnt] = $row['part'];

	// 数量データ検索
	$qty_row = $mdb2->queryRow("SELECT * FROM qty_data WHERE unit_id='$unit_id' AND q_part='$p_data[$cnt]'");
	if (PEAR::isError($qty_row)) {
	} else {
		$q_part = $qty_row[5];
		$qty    = $qty_row[6];
		$reel   = $qty_row[7];
	}

	// 重複品名の処理
	// 2009/07/31 フォーマット変更の為、修正
	$key = array_search($q_part, $p_data);
	if ($key!=$cnt) {
//		$qty = '--';
//		$reel = '--';
		$qty = '0';
	}
	$cnt++;

	// 棚番データ検索
	// 2007/08/23 先頭の[%]を削除 期待した棚番が検索出来ていない場合がある
	// 2009/03/11 「Like検索」を止めて「完全一致」で検索へ変更
	// 2009/03/18 アドバンス向け対応
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
		$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6];

		// 変数の初期化
		unset($rack_no);
		unset($solder);
		unset($rack_old);
		unset($exp_item);

		// 2008/01/15 RoHS品を検索(共晶品の処理は未解決)
		// 2009/07/03 RoHS品以外も検索対象とする
		// 2016/02/01 高額品($exp_item)取得
		$tmp_query = $mdb2->query($sql[0]);
		$tmp_query = err_check($tmp_query);
		while($tmp_row = $tmp_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			//if ($tmp_row['solder']=='2') {
				$rack_no  = $tmp_row['rack_no'];
				$solder   = $tmp_row['solder'];
				$rack_old = $tmp_row['rack_old'];
				$exp_item = $tmp_row['exp_item'];
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
			$rack_old = $tmp_row['rack_no'];
		}
		unset($sql);

	}

	// 2009/03/02 見直しによる棚番指定がある場合は指定棚番を表示
	// 2015/10/22 新棚番対応
	// 2016/02/11 旧棚番生産品に対応して仮に旧棚番仕様へ戻す
	if ($row['data_sel']=='1') {
		//$rack_old = $row['rack_data'];
		$rack_no = $row['rack_data'];
		$rem_set1 = "棚番指定 " . $row['rem_set1'];
	} else {
		$rack_no = $rack_no;
		$rem_set1 = $row['rem_set1'];
	}

	// 2008/10/31 不明を表示させない為の仮対応
	// 2009/05/13 はんだ表示廃止
//	switch($solder) {
//	case 0:
//		$solder_name = '';
//		break;
//	case 1:
//		$solder_name = '共晶';
//		break;
//	case 2:
//		$solder_name = 'RoHS';
//		break;
//	case 3:
//		$solder_name = '混在';
//		break;
//	}

	// 作業用一時テーブルへ格納
	// 2016/01/29 ロットデータ用に用意していたData9に高額品情報(exp_item)をのせてみる
	$total_qty = (string)((integer)$qty * (integer)$mf_qty);
/* 	$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7, data8, data9, data10, data11, data12) VALUES(
						".$mdb2->quote($st_index, 'Text').",
						".$mdb2->quote($row['set_index'], 'Text').",
						".$mdb2->quote($row['p_share'], 'Text').",
						".$mdb2->quote($row['hole_no'], 'Text').",
						".$mdb2->quote($row['part'], 'Text').",
						".$mdb2->quote($row['rem_set2'], 'Text').",
						".$mdb2->quote($rack_old, 'Text').",
						".$mdb2->quote($qty, 'Text').",
						".$mdb2->quote($total_qty, 'Text').",
						".$mdb2->quote('', 'Text').",
						".$mdb2->quote($rem_set1, 'Text').",
						".$mdb2->quote($rack_no, 'Text').",
						".$mdb2->quote($row['data_sel'], 'Text').")");
 */
	$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7, data8, data9, data10, data11, data12) VALUES(
						".$mdb2->quote($st_index, 'Text').",
						".$mdb2->quote($row['set_index'], 'Text').",
						".$mdb2->quote($row['p_share'], 'Text').",
						".$mdb2->quote($row['hole_no'], 'Text').",
						".$mdb2->quote($row['part'], 'Text').",
						".$mdb2->quote($row['rem_set2'], 'Text').",
						".$mdb2->quote($rack_old, 'Text').",
						".$mdb2->quote($qty, 'Text').",
						".$mdb2->quote($total_qty, 'Text').",
						".$mdb2->quote($exp_item, 'Text').",
						".$mdb2->quote($rem_set1, 'Text').",
						".$mdb2->quote($rack_no, 'Text').",
						".$mdb2->quote($row['data_sel'], 'Text').")");

}

// 2009/05/15 変数の初期化
unset($st_index);	// ステーションindex
unset($sh_index);	// シートindex(種別)
unset($station);
unset($where_tmp);
unset($rem_st);

// 各Excelシートへ部品データの出力
for ($s_index=1; $s_index<=$sh_count; $s_index++) {

	switch($st_name[$s_index]) {
	case '1F':
		$st_index = 0;
		$sh_index = 0;
		$station = $F1[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'F%'";
		break;

	case '1R':
		$st_index = 0;
		$sh_index = 1;
		$station = $R1[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'R%'";
		break;

	case '2F':
		$st_index = 1;
		$sh_index = 0;
		$station = $F2[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'F%'";
		break;

	case 'MTS':
		$st_index = 1;
		$sh_index = 1;
		$station = $MTS[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'MTS%'";
		break;

	case '3F':
		$st_index = 2;
		$sh_index = 0;
		$station = $F3[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'F%'";
		break;

	case '3R':
		$st_index = 2;
		$sh_index = 1;
		$station = $R3[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'R%'";
		break;

	case 'MTC':
		$st_index = 2;
		$sh_index = 2;
		$station = $MTC[1];
		$where_tmp = " WHERE data0='$station' AND data3 LIKE 'MTC%'";
		break;
	}

	switch($sort_sel) {
	case 0:
		break;
	case 1:
		$res_order = " ORDER BY data7, data2";
		break;
	case 2:
		$res_order = " ORDER BY data7, data3";
		break;
	case 3:
		$res_order = " ORDER BY tmp_id";
		break;
	}

	$res_query = $mdb2->query("SELECT * FROM data_tmp" . $where_tmp);
	$res_query = err_check($res_query);

	$r = 6;
	$id_tmp = $s_index + 1;
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		// 2015/08/24 CBE品の場合 通常品名を備考欄へ追加(再修正)
		$part_chk = substr($row['data4'], 0, 3);

		$sheet[$s_index]->writeString($r,  0, mb_convert_encoding("", "SJIS", "UTF-8"), $f5);
		$sheet[$s_index]->writeString($r,  1, mb_convert_encoding($row['data2'], "SJIS", "UTF-8"), $f5);
		$sheet[$s_index]->writeString($r,  2, mb_convert_encoding($row['data3'], "SJIS", "UTF-8"), $f5);
		$sheet[$s_index]->writeString($r,  3, mb_convert_encoding($row['data4'], "SJIS", "UTF-8"), $f3);

		// 2015/08/24 CBE品の場合 通常品名を備考欄へ追加(再修正)
		// 2015/09/18 CBE品名で通常品名を検索出来なかった場合にセル書式がされていなかったので修正
		// 2015/11/13 これまでは品名備考に品名があればそれを優先していたが、CBE品名で棚番指定では無い時には
		//            DBの通常品名を備考欄へ追加
		//if ($part_chk=='CBE' and $row['data5']==NULL) {
		if ($part_chk=='CBE' and $row['data12']!=1) {

			// 2015/10/22 新棚番対応
			// 2016/02/09 旧棚番生産品に対応して仮に旧棚番仕様へ戻す
/* 			$rack_old = $row['data6'];
			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old'");
 */
			$rack_no = $row['data11'];
			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_no='$rack_no'");

			if (PEAR::isError($part_row)) {
			} else {
				$product = $part_row[2];
			}

			if ($product!=NULL) {
				$sheet[$s_index]->writeString($r, 4, mb_convert_encoding($product, "SJIS", "UTF-8"), $f3);
			} else {
				$sheet[$s_index]->writeString($r, 4, mb_convert_encoding('', "SJIS", "UTF-8"), $f3);
			}

 			unset($product);


		} else {

			$sheet[$s_index]->writeString($r,  4, mb_convert_encoding($row['data5'], "SJIS", "UTF-8"), $f3);

		}

		// 2015/10/22 新棚番対応
		// 2015/10/23 棚番指定の場合、新棚番は8桁で旧棚番は8桁まで無いので桁数で判断する
		// 2016/02/09 旧棚番生産品に対応して仮に旧棚番仕様へ戻す(高額品の棚番セル色をオレンジへ)
/* 		if ($row['data9']==1) {

			$sheet[$s_index]->writeString($r,  5, mb_convert_encoding($row['data6'], "SJIS"), $f13);

		} elseif ($row['data9']==0) {

			$sheet[$s_index]->writeString($r,  5, mb_convert_encoding($row['data6'], "SJIS"), $f3);

		}
 */
		// 2016/02/01 高額品の棚番セル色をオレンジへ変更
		// 2016/02/18 棚番指定「R」を表示するように修正
		if ($row['data9']==1) {

			if (strlen($row['data11'])==8) {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding(substr($row['data11'], 2, 5), "SJIS"), $f13);	// 新棚番を表示(棚番部分のみ)
			} elseif ($row['data11']=='ZN' or $row['data11']=='Z' or $row['data11']=='R') {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding($row['data11'], "SJIS", "UTF-8"), $f13);	// ZN, Z, Rの場合はそのまま表示新棚番を表示
			} else {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding('', "SJIS", "UTF-8"), $f13);				// 表示無し
			}

		} elseif ($row['data9']==0) {

			if (strlen($row['data11'])==8) {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding(substr($row['data11'], 2, 5), "SJIS", "UTF-8"), $f3);	// 新棚番を表示(棚番部分のみ)
			} elseif ($row['data11']=='ZN' or $row['data11']=='Z' or $row['data11']=='R') {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding($row['data11'], "SJIS", "UTF-8"), $f3);	// ZN, Z, Rの場合はそのまま表示新棚番を表示
			} else {
				$sheet[$s_index]->writeString($r,  5, mb_convert_encoding('', "SJIS", "UTF-8"), $f3);				// 表示無し
			}

		}

		$sheet[$s_index]->writeString($r,  6, mb_convert_encoding($row['data7'], "SJIS", "UTF-8"), $f4);
		$sheet[$s_index]->writeString($r,  7, mb_convert_encoding($row['data8'], "SJIS", "UTF-8"), $f4);
//		$sheet[$s_index]->writeString($r,  8, mb_convert_encoding($row['data9'], "SJIS"), $f3);
		$sheet[$s_index]->writeString($r,  8, mb_convert_encoding('', "SJIS", "UTF-8"), $f3);
		$sheet[$s_index]->writeString($r,  9, mb_convert_encoding($row['data10'], "SJIS", "UTF-8"), $f3);

		// 2009/02/09 CBE品の場合 通常品名を備考欄へ追加
		// 2009/09/03 備考2(品名)に入力が無い場合に通常品名を検索へ修正
		// 2015/08/24 現在はこの処理ではうまく動作しないので再修正(この部分はコメントアウト)
/* 		$part_chk = substr($row['data4'], 0, 3);

		if ($part_chk=='CBE' and $row['data5']==NULL) {

			$rack_old = $row['data6'];
			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old'");
			if (PEAR::isError($part_row)) {
			} else {
				$product = $part_row[2];
			}

			if ($product!=NULL) {
				$sheet[$s_index]->writeString($r, 4, mb_convert_encoding($product, "SJIS"), $f3);
			}

		}
 */
		unset($part_chk);
		unset($product);
		unset($tmp_rem);
		$r++;
	}

	// 2009/05/15 シート備考 追加
	$station_row = $mdb2->queryRow("SELECT * FROM station_data WHERE st_id='$st_id[$st_index]'");
	if (PEAR::isError($station_row)) {
	} else {
		$rem_st[0] = $station_row[5];
		$rem_st[1] = $station_row[6];
		$rem_st[2] = $station_row[7];
	}

	if ($rem_st[$sh_index]!=NULL ) {

		// 行送り
		$r++;
		$sheet[$s_index]->writeString($r, 0, mb_convert_encoding($rem_st[$sh_index], "SJIS", "UTF-8"), $f12);
		//$sheet[$s_index]->writeString($r, 0, $rem_st[$sh_index], $f12);
		$sheet[$s_index]->mergeCells($r, 0, $r, 9);

	}

}


//ブック出力
$workbook->send("$book_name");
$workbook->close();

// DB切断
db_disconnect($mdb2);

?>
