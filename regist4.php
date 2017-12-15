<?php
// セッション開始
session_start();

if ($_GET['mode_regist4']=='input') {

	$mode     = $_GET["mode_regist4"];
	$rack_sel = $_GET["rack_sel"];

} elseif ($_SESSION["mode_regist4"]!=NULL) {

	$mode     = $_SESSION["mode_regist4"];
	$rack_sel = $_SESSION["rack_sel"];

} else {

	$mode     = $_GET["mode_regist4"];
	$rack_sel = $_GET["rack_sel"];

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

<body>

<!--
[生産管理システム]

 * 新規部品登録

  2007/12/27

  2008/03/22 在庫数データ[fs_stock]対応 データは仮登録とする

  2008/06/24 現場からの要望でSMT部品データ[part_smt]に棚番備考[rem_rack]追加

  2008/07/04 現場からの要望でリールサイズの表記を修正
             「混在」を廃止し「RoHS」と「共晶」に分ける為に登録処理の修正
             (はんだのみを変更してもうまく登録出来なかった)

  2009/02/12 SMT在庫[stock_smt]追加 在庫数データ[fs_stock]は廃止方向
             シンプルにトータルの在庫数で管理

  2009/08/03 アドバンス専用棚[part_advance] 対応開始

  2009/10/09 在庫数[stock_smt]に単価[unit_cost]追加

  2010/02/16 データ更新日のフォーマットを修正

  2010/07/27 session_start()を先頭に移動

  2015/10/26 新棚番対応 リールサイズ修正など

  2017/07/13 文字コードをUTF-8へ

 -->

<?php

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once 'inc/parameter_inc.php';
include_once "lib/com_func1.php";
include_once "lib/com_func2.php";

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

$sel_size = array(0=>"中(250mm)",
				  1=>"小(180mm)",
				  2=>"大(330mm)",
				  3=>"特大(380mm)",
				  4=>"千代田専用",
				  5=>"防湿庫/乾燥庫");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_regist4"]);
	unset($_SESSION["rack_sel"]);
}

// セッション破棄
unset($_SESSION["mode_regist4"]);
unset($_SESSION["rack_sel"]);


switch($mode) {
case "input":

	// DB接続
	$mdb2 = db_connect();

	// 日東S品
	if ($rack_sel==0) {

		// 登録画面よりデータ取得
		$rack_old   = mb_convert_kana(Trim($_POST['rack_old']), "KVa");
		$product    = mb_convert_kana(Trim($_POST['product']), "KVa");
		$solder     = $_POST['solder']["solder"];
		$p_new      = mb_convert_kana(Trim($_POST['p_new']), "KVa");
		$p_maker    = mb_convert_kana(Trim($_POST['p_maker']), "KVa");
		$p_sub      = mb_convert_kana(Trim($_POST['p_sub']), "KVa");
		$p_nec      = mb_convert_kana(Trim($_POST['p_nec']), "KVa");
		$r_size     = $_POST['r_size']["r_size"];
		$exp_item   = $_POST['exp_item']["exp_item"];
		$rack_no    = mb_convert_kana(Trim($_POST['rack_no']), "KVa");
		$add_date_y = $_POST['add_date']["Y"];
		$add_date_m = $_POST['add_date']["m"];
		$add_date_d = $_POST['add_date']["d"];
		$add_date   = $_POST['add_date'];
		$rem_part   = mb_convert_kana(Trim($_POST['rem_part']), "KVa");
		$rem_rack   = mb_convert_kana(Trim($_POST['rem_rack']), "KVa");

		$date_len = strlen($add_date_m);
		if ($date_len==1) {
			$add_date_m = "0" . $add_date_m;
		}

		$date_len = strlen($add_date_d);
		if ($date_len==1) {
			$add_date_d = "0" . $add_date_d;
		}

		//
		if ($product!=NULL) {

			// 登録済み確認
			$sql_select = "SELECT * FROM part_smt";
			$sql_where  = " WHERE solder='$solder' AND (product='$product' OR p_new='$product' OR p_maker='$product' OR p_sub='$product' OR p_nec='$product')";
			$sql_order  = " ORDER BY smt_id";
			$search_sql = $sql_select . $sql_where . $sql_order;

			$res_row = $mdb2->queryRow($search_sql);
			if (PEAR::isError($res_row)) {
			} else {
				$smt_id = $res_row[0];
			}
			unset($res_row);

			if ($smt_id!=NULL) {

				// 重複登録 -> 前の部品情報をアップデート

				// 更新日を備考に追加
				$rem_part_set = $add_date_y . "-" . $add_date_m . "-" .$add_date_d . " 更新 " . $rem_part;

				// はんだ種別により新棚番の修正
				switch ($solder) {
				case 0:
					if ($rack_no=="BSXXXXXR" or $rack_no=="BSXXXXXN") {
						$rack_no="BSXXXXXX";
					}
					break;
				case 1:
					if ($rack_no=="BSXXXXXR" or $rack_no=="BSXXXXXX") {
						$rack_no="BSXXXXXN";
					}
					break;
				case 2:
					if ($rack_no=="BSXXXXXN" or $rack_no=="BSXXXXXX") {
						$rack_no="BSXXXXXR";
					}
					break;
				}

				// 更新前データの取得(現在は使用して無い 将来使う？)
				$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
				if (PEAR::isError($res_row)) {
				} else {
					$part_chk = $res_row;
				}

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// [part_smt]更新
				if ($rack_old!='採番中') {
					$res = $mdb2->exec("UPDATE part_smt SET rack_old='$rack_old' WHERE smt_id='$smt_id'");
				}

//				$res = $mdb2->exec("UPDATE part_smt SET product='$product' WHERE smt_id='$smt_id'");
				$res = $mdb2->exec("UPDATE part_smt SET solder='$solder' WHERE smt_id='$smt_id'");

				if ($p_new!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET p_new='$p_new' WHERE smt_id='$smt_id'");
				}

				if ($p_maker!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET p_maker='$p_maker' WHERE smt_id='$smt_id'");
				}

				if ($p_sub!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET p_sub='$p_sub' WHERE smt_id='$smt_id'");
				}

				if ($p_nec!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET p_nec='$p_nec' WHERE smt_id='$smt_id'");
				}

				$res = $mdb2->exec("UPDATE part_smt SET r_size='$r_size' WHERE smt_id='$smt_id'");
				$res = $mdb2->exec("UPDATE part_smt SET exp_item='$exp_item' WHERE smt_id='$smt_id'");

				if ($rack_no!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET rack_no='$rack_no' WHERE smt_id='$smt_id'");
				}

				if ($rem_part!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET rem_part='$rem_part_set' WHERE smt_id='$smt_id'");
				}

				if ($rem_rack!=NULL) {
					$res = $mdb2->exec("UPDATE part_smt SET rem_rack='$rem_rack' WHERE smt_id='$smt_id'");
				}

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

				unset($res_row);
				unset($part_chk);

				// 登録内容 取得
				// 2009/08/06 修正
//				$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product'");
				$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
				if (PEAR::isError($res_row)) {
				} else {
//					$smt_id = $res_row[0];
				}

			} else {

				// 新規登録

				// 追加日を備考に追加
				$rem_part_set = $add_date_y . "-" . $add_date_m . "-" .$add_date_d . " 追加 " . $rem_part;

				// はんだ種別により新棚番の修正
				switch ($solder) {
				case 0:
					if ($rack_no=="BSXXXXXR" or $rack_no=="BSXXXXXN") {
						$rack_no="BSXXXXXX";
					}
					break;
				case 1:
					if ($rack_no=="BSXXXXXR" or $rack_no=="BSXXXXXX") {
						$rack_no="BSXXXXXN";
					}
					break;
				case 2:
					if ($rack_no=="BSXXXXXN" or $rack_no=="BSXXXXXX") {
						$rack_no="BESXXXXXR";
					}
					break;
				}

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// [part_smt]登録
				$res = $mdb2->exec("INSERT INTO part_smt(rack_no, product, solder, p_new, p_maker, p_sub, p_nec, r_size, exp_item, rack_old, rem_part) VALUES(
										".$mdb2->quote($rack_no, 'Text').",
										".$mdb2->quote($product, 'Text').",
										".$mdb2->quote($solder, 'Text').",
										".$mdb2->quote($p_new, 'Text').",
										".$mdb2->quote($p_maker, 'Text').",
										".$mdb2->quote($p_sub, 'Text').",
										".$mdb2->quote($p_nec, 'Text').",
										".$mdb2->quote($r_size, 'Text').",
										".$mdb2->quote($exp_item, 'Text').",
										".$mdb2->quote($rack_old, 'Text').",
										".$mdb2->quote($rem_part_set, 'Text').")");

				// 登録内容 取得
				$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE product='$product' AND solder='$solder'");
				if (PEAR::isError($res_row)) {
				} else {
					$smt_id = $res_row[0];
				}

				// 2008/03/22 [fs_stock]登録 データは仮登録とする
				// 2009/02/12 [fs_stock]廃止 [stock_smt]追加
				// 2009/10/09 [unit_cost]追加
				if ($smt_id!=NULL) {
					$res = $mdb2->query("INSERT INTO stock_smt(smt_id, unit_cost, qty_stock, up_date, rem_stock) VALUES(
									".$mdb2->quote($smt_id, 'Integer').",
									".$mdb2->quote(0.00, 'Decimal').",
									".$mdb2->quote(0, 'Integer').",
									".$mdb2->quote(date('Y-m-d'), 'Date').",
									".$mdb2->quote('新規仮登録', 'Text').")");
				}

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			}

		}

		// 品名IDによる検索
		$form = new HTML_QuickForm("regist", "POST");
		$form -> addElement("header", "title", "SMT新規部品 登録(日東S品):");

		$form -> addElement("text", "rack_no", "棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "product", "品名:", array('size'=>30, 'maxlength'=>50));

		$solder_set[] = $form -> createElement("radio", "solder", NULL, "不明", 0);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "共晶", 1);
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "RoHS", 2);
		$form -> addGroup($solder_set, "solder", "はんだ:");

		$form -> addElement("text", "p_new", "新品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_maker", "メーカー品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_sub", "サブ品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "p_nec", "NEC品名:", array('size'=>30, 'maxlength'=>50));

		$size_set[] = $form -> createElement("radio", "r_size", NULL, "小(180mm)", 1);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "中(250mm)", 0);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "大(330mm)", 2);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "特大(380mm)", 3);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "千代田専用", 4);
		$size_set[] = $form -> createElement("radio", "r_size", NULL, "防湿庫/乾燥庫", 5);
		$form -> addGroup($size_set, "r_size", "リールサイズ:");

		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "通常", 0);
		$exp_set[] = $form -> createElement("radio", "exp_item", NULL, "高額品", 1);
		$form -> addGroup($exp_set, "exp_item", "高額品:");

		$form -> addElement("text", "rack_old", "旧棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("date", "add_date", "追加日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

		$form -> addElement("text", "rem_part", "備考:", array('size'=>40, 'maxlength'=>100));
		$form -> addElement("text", "rem_rack", "棚番備考:", array('size'=>40, 'maxlength'=>100));
		$form -> addElement("submit", "send", "データベース登録");

		// 入力チェック
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> addRule("rack_old", "旧棚番を入力して下さい", "required", "", "client");
		$form -> addRule("product", "品名を入力して下さい", "required", "", "client");
		$form -> addRule("rack_no", "棚番を入力してください", "required", "", "client");

		// 基礎データの設定
		$form -> setDefaults(array("rack_old" =>"採番中",
									"product" =>"",
									"solder"  =>array("solder"=>2),
									"p_new"   =>"",
									"p_maker" =>"",
									"p_sub"   =>"",
									"p_nec"   =>"",
									"r_size"  =>array("r_size"=>1),
									"exp_item"=>array("exp_item"=>0),
									"rack_no" =>"BSXXXXXR",
									"add_date"=>array("Y"=>date("Y"), "m"=>date("m"), "d"=>date("d")),
									"rem_part"=>"",
									"rem_rack"=>""));

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
			$form->display();
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
			$form->display();
		}

		// 登録情報 確認表示
		print('<hr style="width: 100%; height: 2px;">');

		// タイトル
		print('<table border="1" align=center>');
		print('<caption>');
		//print('<br>');
		print('<div align="center"><font size="4" color="#0066cc"><b>');
		print('SMT部品(日東S品) 登録情報');
		print('</b></font></div>');
		print('</caption>');

		// 項目名
		print('<tr bgcolor="#cccccc">');
		print('<th>ID</th>');
		print('<th>棚番</th>');
		print('<th>品名</th>');
		print('<th>はんだ</th>');
		print('<th>新品名</th>');
		print('<th>メーカー品名</th>');
		print('<th>サブ品名</th>');
		print('<th>NEC品名</th>');
		print('<th>リール</th>');
		print('<th>高額品</th>');
		print('<th>旧棚番</th>');
		print('<th>備考</th>');
		print('<th>棚番備考</th>');
		print('</tr>');

		if ($res_row!=NULL ) {
			print('<tr>');

			print('<td>');
			print($res_row[0]);
			print('</td>');

			print('<td>');
			print($res_row[1]);
			print('</td>');

			print('<td>');
			print($res_row[2]);
			print('</td>');

			print('<td>');
			print(solder_name($res_row[3]));
			print('</td>');

			print('<td>');
			if ($res_row[4]==NULL) {
				print('<br>');
			} else {
				print($res_row[4]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[5]==NULL) {
				print('<br>');
			} else {
				print($res_row[5]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[6]==NULL) {
				print('<br>');
			} else {
				print($res_row[6]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[7]==NULL) {
				print('<br>');
			} else {
				print($res_row[7]);
			}
			print('</td>');

			print('<td>');
			print(r_size_name($part_data[$i][8]));
			print('</td>');

			print('<td>');
			switch ($res_row[9]) {
			case 0:
				print('<br>');
				break;
			case 1:
				print('高額');
				break;
			}
			print('</td>');

			print('<td>');
			print($res_row[10]);
			print('</td>');

			print('<td>');
			if ($res_row[11]==NULL) {
				print('<br>');
			} else {
				print($res_row[11]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[12]==NULL) {
				print('<br>');
			} else {
				print($res_row[12]);
			}
			print('</td>');

			print('</tr>');
		}
		print('</table>');

		// 登録結果 破棄
		unset($res_row);


	} elseif ($rack_sel==1) {

		// アドバンス専用品
		// 登録画面よりデータ取得
		$rack_no     = mb_convert_kana(Trim($_POST['rack_no']), "KVa");

		$part_no_1   = mb_convert_kana(Trim($_POST['part_no'][0]), "KVa");
		$part_no_2   = mb_convert_kana(Trim($_POST['part_no'][1]), "KVa");
		$part_no_3   = mb_convert_kana(Trim($_POST['part_no'][2]), "KVa");
		$part_no     = $part_no_1 . " " . $part_no_2 . " " . $part_no_3;

		$part_1      = mb_convert_kana(Trim($_POST['part_1']), "KVa");
		$part_2      = mb_convert_kana(Trim($_POST['part_2']), "KVa");
		$part_3      = mb_convert_kana(Trim($_POST['part_3']), "KVa");
		$part_4      = mb_convert_kana(Trim($_POST['part_4']), "KVa");
		$part_5      = mb_convert_kana(Trim($_POST['part_5']), "KVa");
		$add_date_y  = $_POST['add_date']["Y"];
		$add_date_m  = $_POST['add_date']["m"];
		$add_date_d  = $_POST['add_date']["d"];
		$add_date    = $_POST['add_date'];
		$rem_advance = mb_convert_kana(Trim($_POST['rem_advance']), "KVa");

		$date_len = strlen($add_date_m);
		if ($date_len==1) {
			$add_date_m = "0" . $add_date_m;
		}

		$date_len = strlen($add_date_d);
		if ($date_len==1) {
			$add_date_d = "0" . $add_date_d;
		}

		// 品名1入力確認
		if ($part_1!=NULL) {

			// 登録済み確認
			$sql_select = "SELECT * FROM part_advance";
			$sql_where[0] = " WHERE part_1='$part_1'";

			// 仮に新規品登録は「品名1」のみを検索対象とする
//			if ($part_2!='') {
//				$sql_where[2] = " OR part_2='$part_2'";
//			} else {
//				$sql_where[2] = "";
//			}
//			if ($part_3!='') {
//				$sql_where[3] = " OR part_3='$part_3'";
//			} else {
//				$sql_where[3] = "";
//			}
//			if ($part_4!='') {
//				$sql_where[4] = " OR part_4='$part_4'";
//			} else {
//				$sql_where[4] = "";
//			}
//			if ($part_5!='') {
//				$sql_where[5] = " OR part_5='$part_5'";
//			} else {
//				$sql_where[5] = "";
//			}
//			$sql_where[0] = $sql_where[1] . $sql_where[2] . $sql_where[3] . $sql_where[4]. $sql_where[5];
			$sql_order = " ORDER BY advance_id";

			$search_sql = $sql_select . $sql_where[0] . $sql_order;

			$res_row = $mdb2->queryRow($search_sql);
			if (PEAR::isError($res_row)) {
			} else {
				$advance_id = $res_row[0];
			}
			unset($res_row);


			if ($advance_id!=NULL) {

				// 重複登録 -> 前の部品情報をアップデート

				// 更新日を備考に追加
				$rem_advance_set = $add_date_y . "-" . $add_date_m . "-" .$add_date_d . " 更新 " . $rem_advance;

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// [part_advance]更新
				$res = $mdb2->exec("UPDATE part_advance SET rack_no='$rack_no' WHERE advance_id='$advance_id'");
				$res = $mdb2->exec("UPDATE part_advance SET part_no='$part_no' WHERE advance_id='$advance_id'");
				$res = $mdb2->exec("UPDATE part_advance SET part_1='$part_1' WHERE advance_id='$advance_id'");

				if ($part_2!=NULL) {
					$res = $mdb2->exec("UPDATE part_advance SET part_2='$part_2' WHERE advance_id='$advance_id'");
				}

				if ($part_3!=NULL) {
					$res = $mdb2->exec("UPDATE part_advance SET part_3='$part_3' WHERE advance_id='$advance_id'");
				}

				if ($part_4!=NULL) {
					$res = $mdb2->exec("UPDATE part_advance SET part_4='$part_4' WHERE advance_id='$advance_id'");
				}

				if ($part_5!=NULL) {
					$res = $mdb2->exec("UPDATE part_advance SET part_5='$part_5' WHERE advance_id='$advance_id'");
				}

				$res = $mdb2->exec("UPDATE part_advance SET rem_advance='$rem_advance_set' WHERE advance_id='$advance_id'");

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

				// 登録内容 取得
				$res_row = $mdb2->queryRow("SELECT * FROM part_advance WHERE advance_id='$advance_id'");
				if (PEAR::isError($res_row)) {
				} else {
					$advance_id = $res_row[0];
				}

			} else {

				// 新規登録

				// 追加日を備考に追加
				$rem_advance_set = $add_date_y . "-" . $add_date_m . "-" .$add_date_d . " 追加 " . $rem_advance;

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// [part_advance]登録
				$res = $mdb2->exec("INSERT INTO part_advance(rack_no, part_no, part_1, part_2, part_3, part_4, part_5, rem_advance) VALUES(
										".$mdb2->quote($rack_no, 'Text').",
										".$mdb2->quote($part_no, 'Text').",
										".$mdb2->quote($part_1, 'Text').",
										".$mdb2->quote($part_2, 'Text').",
										".$mdb2->quote($part_3, 'Text').",
										".$mdb2->quote($part_4, 'Text').",
										".$mdb2->quote($part_5, 'Text').",
										".$mdb2->quote($rem_advance_set, 'Text').")");

				// 登録内容 取得
				$res_row = $mdb2->queryRow("SELECT * FROM part_advance WHERE part_1='$part_1'");
				if (PEAR::isError($res_row)) {
				} else {
					$advance_id = $res_row[0];
				}

//				if ($smt_id!=NULL) {
//					$res = $mdb2->query("INSERT INTO stock_smt(smt_id, qty, up_date, rem_stock) VALUES(
//									".$mdb2->quote($smt_id, 'Integer').",
//									".$mdb2->quote(0, 'Integer').",
//									".$mdb2->quote(date('Y-m-d'), 'Date').",
//									".$mdb2->quote('新規仮登録', 'Text').")");
//				}

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			}

		}

		// 品名IDによる検索
		$form = new HTML_QuickForm("regist", "POST");
		$form -> addElement("header", "title", "SMT新規部品 登録(アドバンス):");

		$form -> addElement("text", "rack_no", "現棚番:", array('size'=>15, 'maxlength'=>20));

		$no_1 =& HTML_QuickForm::createElement('text', '', null, array('size'=>3, 'maxlength'=>3));
		$no_2 =& HTML_QuickForm::createElement('text', '', null, array('size'=>4, 'maxlength'=>4));
		$no_3 =& HTML_QuickForm::createElement('text', '', null, array('size'=>3, 'maxlength'=>3));
		$form -> addGroup(array($no_1, $no_2, $no_3), 'part_no', '部品番号:', '');

		$form -> addElement("text", "part_1", "品名1:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_2", "品名2:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_3", "品名3:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_4", "品名4:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("text", "part_5", "品名5:", array('size'=>30, 'maxlength'=>50));

		$form -> addElement("date", "add_date", "追加日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

		$form -> addElement("text", "rem_advance", "備考:", array('size'=>40, 'maxlength'=>100));
		$form -> addElement("submit", "send", "データベース登録");

		// 入力チェック
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> addRule("rack_no", "棚番を入力して下さい", "required", "", "client");
		$form -> addRule("part_no", "部品番号を入力して下さい", "required", "", "client");
//		$form -> addRule("part_no", "部品番号を入力して下さい", "numeric", "", "client");
		$form -> addRule("part_1", "品名を入力して下さい", "required", "", "client");

		// 基礎データの設定
		$form -> setDefaults(array("rack_no"    =>"棚ナシ",
									"part_no[0]"=>"xxx",
									"part_no[1]"=>"xxxx",
									"part_no[2]"=>"xxx",
									"part_1"    =>"",
									"part_2"    =>"",
									"part_3"    =>"",
									"part_4"    =>"",
									"part_5"    =>"",
									"add_date"=>array("Y"=>date("Y"), "m"=>date("m"), "d"=>date("d")),
									"rem_advance"=>""));

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
			$form->display();
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
			$form->display();
		}

		// 登録情報 確認表示
		print('<hr style="width: 100%; height: 2px;">');

		// タイトル
		print('<table border="1" align=center>');
		print('<caption>');
		//print('<br>');
		print('<div align="center"><font size="4" color="#0066cc"><b>');
		print('SMT部品(アドバンス) 登録情報');
		print('</b></font></div>');
		print('</caption>');

		// 項目名
		print('<tr bgcolor="#cccccc">');
		print('<th>ID</th>');
		print('<th>棚番</th>');
		print('<th>部品番号</th>');
		print('<th>品名1</th>');
		print('<th>品名2</th>');
		print('<th>品名3</th>');
		print('<th>品名4</th>');
		print('<th>品名5</th>');
		print('<th>備考</th>');
		print('</tr>');

		if ($res_row!=NULL ) {
			print('<tr>');

			print('<td>');
			print($res_row[0]);
			print('</td>');

			print('<td>');
			print($res_row[1]);
			print('</td>');

			print('<td>');
			print($res_row[2]);
			print('</td>');

			print('<td>');
			print($res_row[3]);
			print('</td>');

			print('<td>');
			if ($res_row[4]==NULL) {
				print('<br>');
			} else {
				print($res_row[4]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[5]==NULL) {
				print('<br>');
			} else {
				print($res_row[5]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[6]==NULL) {
				print('<br>');
			} else {
				print($res_row[6]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[7]==NULL) {
				print('<br>');
			} else {
				print($res_row[7]);
			}
			print('</td>');

			print('<td>');
			if ($res_row[8]==NULL) {
				print('<br>');
			} else {
				print($res_row[8]);
			}
			print('</td>');

			print('</tr>');
		}
		print('</table>');

		// 登録結果 破棄
		unset($res_row);

	}

	// DB切断
	db_disconnect($mdb2);

	$_SESSION["mode_regist4"] = 'input';
	$_SESSION["rack_sel"]     = $rack_sel;

	break;


case "edit":

	break;

}


?>
</body>
</html>
