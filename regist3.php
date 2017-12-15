<?php
// セッション開始
session_start();
if ($_GET['mode_regist3']=='input') {

	$mode = $_GET["mode_regist3"];

} elseif ($_SESSION["mode_regist3"]!=NULL) {

	$mode = $_SESSION["mode_regist3"];

} else {

	$mode = $_GET["mode_regist3"];

}
?>

<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>生産管理システム</title>
	<script type="text/javascript" src="lib/colorful.js"></script>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<basefont size="5">

<body>

<!--
[生産管理システム]

 * SMT部品 棚番検索
  サーバにアップロードした使用部品の集計データに棚番を付加して出力

  2007/11/09

  2007/11/26
  一時テーブルでExcel出力も処理しようとしたが、正常に処理出来ていなかった
  通常テーブルで処理するように修正
  サブ品名、NEC品名も検索範囲に入れる

  2007/12/18
  データディレクトリ修正

  2008/01/11
  棚番検索処理を修正

  2008/01/15
  棚番が検索出来なかった場合の初期化が抜けていたので修正

  2008/03/24
  一覧に在庫数情報を追加 ディレクトリ修正

  2009/08/24
  使用していない表示データの整理

  2010/07/08
  ブラウザ表示の一覧を修正(在庫数、在庫更新日を表示)

  2010/07/27
  session_start()を先頭に移動

  2011/01/25
  検索するデータを部品名のみにしたので読み込み処理を修正
  数量データが無いとPHP5では「Undefined offset」が出てしまう

  2013-05-30
  数量データを読み込むように元に戻す
  久しぶりに使ってみたら在庫数との確認では所要数が必要

  2017/07/13
  文字コードをUTF-8へ

-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once 'inc/parameter_inc.php';
include_once 'lib/com_func1.php';
include_once 'lib/com_func2.php';

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_regist3"]);
	unset($_SESSION["set_y"]);
	unset($_SESSION["set_m"]);
	unset($_SESSION["set_d"]);
}

// 入力検証OK 処理
function showForm($values) {
	print_r($values);
}

// フォーム入力の整形(空白削除と小文字->大文字変換)
function constForm($values) {
	return strtoupper(trim($values));
}

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


// セッション破棄
unset($_SESSION["mode_regist3"]);

switch($mode) {
case "select":

	$data_dir = "../data/stock/use/";
	$data_file = getdirtree($data_dir);

	// ファイル一覧の分解(ファイル名のみとフルパス名)
	$data_cnt = count($data_file);

	if ($data_cnt!=NULL) {
		for ($k=0; $k<$data_cnt; $k++) {
			list($csv[$k], $csv_full[$k]) = each($data_file);
		}
	}

	$form = new HTML_QuickForm("select", "POST");
	$form -> addElement("header", "title", "使用部品(SMT)集計データ選択:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "data_csv", "部品集計データ:", $csv);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("submit", "send", "検索開始");
	$form->display();

	$_SESSION["mode_regist3"] = 'regist';
	$_SESSION["csv"] = $csv;
	$_SESSION["csv_full"] = $csv_full;

	break;


case "regist":

	// DB接続
	$mdb2 = db_connect();

	$csv_full = $_SESSION['csv_full'];
	$cnt_csv  = count($csv_full) - 1;
	$csv      = $_SESSION['csv'];
	$csv_file = $csv[$_POST['data_csv']];

	$data_csv = "../data/stock/use/" . $csv_file;

	$fp = fopen($data_csv, "r");

	// 作業用一時テーブル作成
//	$res = $mdb2->exec("CREATE TEMP TABLE use_smt (
//							use_id SERIAL,
//							use_part TEXT,
//							use_qty TEXT,
//							CONSTRAINT use_smt_pkey PRIMARY KEY (use_id))");

	// 作業用テーブル 削除
	$res = $mdb2->exec("DROP TABLE use_smt");

	// 作業用テーブル 作成
	$res = $mdb2->exec("CREATE TABLE use_smt (
							use_id SERIAL,
							use_part TEXT,
							use_qty TEXT,
							CONSTRAINT use_smt_pkey PRIMARY KEY (use_id))");

	// 作業用テーブルへ格納
	while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

		// 2011-01-25 検索するデータを部品名のみにしたので修正
		// 2013-05-30 数量も確認したいので元へ戻す
		// Trimによる前後スペース削除
		for ($i=0; $i<=1; $i++) {
			$data[$i] = Trim($data[$i]);
		}

		$mdb2->query("INSERT INTO use_smt(use_part, use_qty) VALUES(
						".$mdb2->quote($data[0], 'Text').",
						".$mdb2->quote($data[1], 'Text').")");

//		// Trimによる前後スペース削除
//		$data[0] = Trim($data[0]);

//		$mdb2->query("INSERT INTO use_smt(use_part) VALUES(
//						".$mdb2->quote($data[0], 'Text').")");

	}

	// CSVファイルを閉じる
	fclose($fp);

	// 登録データ確認一覧表示
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('使用部品(SMT)集計データ [ ');
	print($csv_file);
	print(' ] 作成日：');
	print(date("Y-m-d H:i"));
	print('<br>');
	print('<a href="excel/excel_out5.php?csv_file=');
	print($csv_file);
	print('" target="result"><img src="../graphics/seisan_excel_out.png" border="0"></a>');
	print('</b></font></div>');

	print('<table border="1" align=center width="95%">');

	// 項目名
	print('<tr bgcolor="#cccccc">');
	print('<th>棚番</th>');
	print('<th>品名</th>');
	print('<th>数量</th>');
	print('<th>はんだ</th>');
	print('<th>新棚番</th>');
	print('<th>高額品</th>');
//	print('<th>リール数</th>');
	print('<th>在庫数</th>');
	print('<th>更新日</th>');
	print('</tr>');

	// 2008/04/07 確認用の表示なので出力件数を100件までに制限
	$res_query = $mdb2->query("SELECT * FROM use_smt ORDER BY use_id LIMIT 100 OFFSET 1");
	$res_query = err_check($res_query);

//	$bgcolor = '#dcdcdc';
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		// 部品名で検索
		$use_part = $row['use_part'];

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

		// 在庫数 検索
//		if ($smt_id!=NULL) {
//			$res_row = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id'");
//			if (PEAR::isError($res_row)) {
//			} elseif ($res_row[0]!=NULL) {
//				$stk_reel  = $res_row[2];
//				$stk_qty   = $res_row[3];
//				$up_date   = $res_row[4];
//				$rem_stock = $res_row[5];
//			} else {
//				unset($stk_reel);
//				unset($stk_qty);
//				unset($up_date);
//				unset($rem_stock);
//			}
//			unset($res_row);
//		} else {
//			unset($stk_reel);
//			unset($stk_qty);
//			unset($up_date);
//			unset($rem_stock);
//		}

		if ($smt_id!=NULL) {
			$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id'");
			if (PEAR::isError($res_row)) {
			} elseif ($res_row[0]!=NULL) {
				$qty_stock = $res_row[3];
				$up_date   = $res_row[4];
			}
		}

		// セル色の設定
		// 1.検索出来ない物 -> 水色
		// 2.在庫数がNULL又は0では無く在庫数が所要数×1.2より多い場合   -> 白
		// 3.                         在庫数が所要数×1.2より少ない場合 -> ピンク
		// 4.                         リール数が1より多い               -> 白
		// 5.                         リール数が1以下                   -> ピンク
		if ($rack_no==NULL or $rack_old==NULL) {

			$bgcolor = '#f8f8ff';

		} else {

//			if ($stk_qty!=NULL or $stk_qty!=0) {
//
//				if ($stk_qty>($use_qty*1.2)) {
//					$bgcolor = '#f8f8ff';
//				} elseif ($stk_reel>1) {
//					$bgcolor = '#f8f8ff';
//				} else {
//					$bgcolor = '#f8f8ff';
//				}
//
//			} else {
//				$bgcolor = '#f8f8ff';
//			}
			$bgcolor = '#f8f8ff';
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		print($color_set);
		if ($rack_old!=NULL) {
			print($rack_old);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		print($use_part);
		print('</td>');

		print($color_set);
		if ($row['use_qty']!=NULL) {
			print($row['use_qty']);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		print($solder_name);
		print('</td>');

		print($color_set);
		if ($rack_no!=NULL) {
			print($rack_no);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		if ($exp_item!=NULL) {
			if ($exp_item=='1') {
				print("高額品");
			} elseif ($exp_item=='0') {
				print('<br>');
			}
		} else {
			print('<br>');
		}
		print('</td>');

//		print($color_set);
//		if ($stk_reel!=NULL) {
//			print($stk_reel);
//		} else {
//			print('<br>');
//		}
//		print('</td>');

//		print($color_set);
//		if ($stk_qty!=NULL) {
//			print($stk_qty);
//		} else {
//			print('<br>');
//		}
//		print('</td>');
		print($color_set);
		if ($qty_stock!=NULL) {
			print($qty_stock);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		if ($up_date!=NULL) {
			print($up_date);
		} else {
			print('<br>');
		}
		print('</td>');

		print('</tr>');

	}

	print('</table></td>');

	// DB切断
	db_disconnect($mdb2);

	$_SESSION["mode_regist3"] = 'select';

	break;

}

?>
</body>
</html>
