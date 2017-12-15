<?php
// セッション開始
session_start();
if ($_GET['mode_edit1']=='search') {

	unset($_SESSION["eid"]);
	unset($_SESSION["sel_search"]);
	unset($_SESSION["parameter"]);

	$mode = $_GET["mode_edit1"];
	$permission = $_GET["permission"];

} elseif ($_SESSION["mode_edit1"]!=NULL) {

	$mode_tmp = $_GET["mode_edit1"];
	if ($mode_tmp!=NULL) {
		$mode = $mode_tmp;
	} else {
		$mode = $_SESSION["mode_edit1"];
	}

	if ($_GET['permission']!=NULL) {
		$permission = $_GET["permission"];
	} else {
		$permission = $_SESSION["permission"];
	}

	if ($_SESSION["eid"]==NULL or $_GET["eid"]!=NULL) {
		$eid = $_GET["eid"];
		$_SESSION["eid"] = $eid;
	}

} else {

	$mode = $_GET["mode_edit1"];
	$permission = $_GET["permission"];

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

 * 部品在庫数編集

  2008/03/22

  2008/07/03
  はんだ種別を更新可能とする(現場要望)

  2008/07/17
  部品名と棚番を選択して検索、編集可能とする(安斎H要望)
  ストック部品 → 補充部品へ表示変更(棚外管理の部品)

  2010/07/27
  session_start()を先頭に移動

  2017/07/13
  文字コードをUTF-8へ

-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// 共有関数
include_once 'inc/parameter_inc.php';
include_once "lib/com_func1.php";

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

// 配列設定
$sel_solder = array(0=>"不明",
					1=>"共晶",
					2=>"RoHS",
					3=>"混在");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_edit1"]);
	unset($_SESSION["permission"]);
}


// セッション破棄
unset($_SESSION["mode_edit1"]);

switch($mode) {
case "search":

	print('<br>');

	// DB接続
	$mdb2 = db_connect();

	// 編集データ取得
	if ($_POST['smt_id']!=NULL) {

		$smt_id    = (int)$_POST['smt_id'];
		$rack_old  = mb_convert_kana(Trim($_POST['rack_old']), "KVa");
		$product   = mb_convert_kana(Trim($_POST['product']), "KVa");
		$solder    = $_POST['solder']["solder"];
		$p_maker   = mb_convert_kana(Trim($_POST['p_maker']), "KVa");

		$stock_id  = (int)$_POST['stock_id'];
		$stk_reel  = (int)mb_convert_kana(Trim($_POST['stk_reel']), "KVa");
		$stk_qty   = (int)mb_convert_kana(Trim($_POST['stk_qty']), "KVa");
		$up_y      = $_POST['up_date']["Y"];
		$up_m      = $_POST['up_date']["m"];
		$up_d      = $_POST['up_date']["d"];
		$rem_stock = mb_convert_kana(Trim($_POST['rem_stock']), "KVa");

		// 日付変換(YYYY-MM-DD)
		$up_date = date('Y-m-d', mktime(0, 0, 0, $up_m, $up_d, $up_y));

		if ($stock_id!=NULL) {

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			// SMT部品データ[part_smt] 更新
			$res = $mdb2->exec("UPDATE part_smt SET solder='$solder' WHERE smt_id='$smt_id'");

			// FS部品 在庫管理[fs_stock] 更新
			$res = $mdb2->exec("UPDATE fs_stock SET stk_reel='$stk_reel' WHERE stock_id='$stock_id'");
			$res = $mdb2->exec("UPDATE fs_stock SET stk_qty='$stk_qty' WHERE stock_id='$stock_id'");
			$res = $mdb2->exec("UPDATE fs_stock SET up_date='$up_date' WHERE stock_id='$stock_id'");
			$res = $mdb2->exec("UPDATE fs_stock SET rem_stock='$rem_stock' WHERE stock_id='$stock_id'");

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		} else {

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			// FS部品 在庫管理[fs_stock]へ追加
			$res = $mdb2->query("INSERT INTO fs_stok(smt_id, stk_reel, stk_qty, up_date, rem_stock) VALUES(
							".$mdb2->quote($smt_id, 'Integer').",
							".$mdb2->quote($stk_reel, 'Integer').",
							".$mdb2->quote($stk_qty, 'Integer').",
							".$mdb2->quote($up_date, 'Date').",
							".$mdb2->quote($rem_stock, 'Text').")");

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		}

		// 更新後、編集ID破棄
		unset($_SESSION["eid"]);

	}

	// 検索条件(種別)の取得
	if ($_POST['sel_search']["sel_search"]!=NULL) {
		$sel_search = $_POST['sel_search']["sel_search"];
		$_SESSION["sel_search"] = $sel_search;
	} else {
		$sel_search = $_SESSION["sel_search"];
	}

	// 検索条件(パラメータ)の取得
	if ($_POST['parameter']!=NULL) {
		$parameter = mb_convert_kana(Trim($_POST['parameter']), "KVa");
		$_SESSION["parameter"] = $parameter;
	} else {
		$parameter = $_SESSION["parameter"];
	}
	$search_para = $parameter . '%';	// [%]を付けてlike検索


	// 品名IDによる検索
	$form = new HTML_QuickForm("edit", "POST");
	$form -> addElement("header", "title", "補充部品 在庫数 編集:");

	$search_sel[] = $form -> createElement("radio", "sel_search", NULL, "棚番", 0);
	$search_sel[] = $form -> createElement("radio", "sel_search", NULL, "部品名", 1);
	$form -> addGroup($search_sel, "sel_search", "検索種別:");
	$form -> addElement("static", "br", "");

	$form -> addElement("text", "parameter", "検索条件:", array('size'=>30, 'maxlength'=>50));
	$form -> addElement("static", "br", "");
	$form -> addElement("submit", "send", "データベース検索");

	// 初期設定
	if ($sel_search!=NULL) {
		$form -> setDefaults(array("sel_search"=>array("sel_search"=>$sel_search)));
	} else {
		$form -> setDefaults(array("sel_search"=>array("sel_search"=>1)));
	}

	if ($parameter!=NULL) {
		$form -> setDefaults(array("parameter"=>$parameter));
	} else {
		$form -> setDefaults(array("parameter"=>""));
	}

	// 入力チェック
//	$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//	$form -> addRule("product", "部品名を入力して下さい", "required", "", "client");

	//
	if ($form -> validate()) {
		$form -> process("showForm",FALSE);
		$form -> display();
	} else {
		$form -> setJsWarnings("以下の項目でエラーが発生しました",
				"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
		$form -> display();
	}

	// 登録情報 確認表示
	print('<hr style="width: 100%; height: 2px;">');

	// タイトル
	print('<table border="1" align=center>');
	print('<caption>');
	//print('<br>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('補充部品 在庫数 情報');
	print('</b></font></div>');
	print('</caption>');

	// 項目名
	print('<tr bgcolor="#cccccc">');
	print('<th>部品ID</th>');
	print('<th>現棚番</th>');
	print('<th>品名</th>');
	print('<th>はんだ</th>');
	print('<th>メーカー品名</th>');
	print('<th>リール数</th>');
	print('<th>在庫数</th>');
	print('<th>更新日</th>');
	print('<th>備考</th>');
	print('</tr>');

	if ($parameter!=NULL) {

		// [smt_id] 検索
		if ($sel_search==0) {

			$sql[0] = "SELECT * FROM part_smt WHERE rack_old='$parameter' ORDER BY smt_id";

		} elseif ($sel_search==1) {

			$sql[1] = "SELECT * FROM part_smt";
			$sql[2] = " WHERE product LIKE '$search_para'";
			$sql[3] = " OR p_new LIKE '$search_para'";
			$sql[4] = " OR p_maker LIKE '$search_para'";
			$sql[5] = " OR p_sub LIKE '$search_para'";
			$sql[6] = " OR p_nec LIKE '$search_para'";
			$sql[8] = " ORDER BY smt_id";
			$sql[0] = $sql[1] . $sql[2] . $sql[3] . $sql[4] . $sql[5] . $sql[6] . $sql[7];

		}
		$res_query = $mdb2->query($sql[0]);
		$res_query = err_check($res_query);

		unset($sql);
		unset($smt_id);

		$i = 0;
		while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$smt_id[$i] = $row['smt_id'];
			$i++;
		}
	}

	if ($smt_id!=NULL) {

		$cnt_smt = count($smt_id) - 1;
		$bgcolor = '#dcdcdc';

		for ($i=0; $i<=$cnt_smt; $i++) {

			$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id[$i]'");
			if (PEAR::isError($part_row)) {
			} else {
				$part_data[$i][0] = $part_row[0];	// SMT部品ID
				$part_data[$i][1] = $part_row[10];	// 現棚番
				$part_data[$i][2] = $part_row[2];	// 品名
				$part_data[$i][3] = $part_row[3];	// はんだ
				$part_data[$i][4] = $part_row[5];	// メーカー品名
			}

			$stock_row = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id[$i]'");
			if (PEAR::isError($stock_row)) {
			} else {
				$part_data[$i][5] = $stock_row[2];	// リール数
				$part_data[$i][6] = $stock_row[3];	// 在庫数
				$part_data[$i][7] = $stock_row[4];	// 更新日
				$part_data[$i][8] = $stock_row[5];	// 備考
			}

			// 列ごとに色を変える
			if ($bgcolor == '#dcdcdc') {
				$bgcolor = '#f8f8ff';
			} elseif ($bgcolor == '#f8f8ff') {
				$bgcolor = '#dcdcdc';
			}

			$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

			print('<tr>');

			print($color_set);
			print($part_data[$i][0]);
			print('</td>');

			print($color_set);
			print($part_data[$i][1]);
			print('</td>');

			print($color_set);
			print($part_data[$i][2]);
			print('</td>');

			print($color_set);
			print(solder_name($part_data[$i][3]));
			print('</td>');

			print($color_set);
			if ($part_data[$i][4]!=NULL) {
				print($part_data[$i][4]);
			} else {
				print('<br>');
			}
			print('</td>');

			print($color_set);
			if ($part_data[$i][5]!=NULL) {
				print($part_data[$i][5]);
			} else {
				print('<br>');
			}
			print('</td>');

			print($color_set);
			if ($part_data[$i][6]!=NULL) {
				print($part_data[$i][6]);
			} else {
				print('<br>');
			}
			print('</td>');

			print($color_set);
			if ($part_data[$i][7]!=NULL) {
				print($part_data[$i][7]);
			} else {
				print('<br>');
			}
			print('</td>');

			print($color_set);
			if ($part_data[$i][8]!=NULL) {
				print($part_data[$i][8]);
			} else {
				print('<br>');
			}
			print('</td>');

			if ($permission=='admin') {

				print($color_set);
				$btn1 = "<input type='button' name='edit' onClick=\"location.href='edit1.php?mode_edit1=edit&eid=";
				$btn1 = $btn1 . $part_data[$i][0] . "'\" value='編集'></td>";
				print($btn1);

//				print($color_set);
//				$btn2 = "<input type='button' name='edit' onClick=\"location.href='edit1.php?mode_edit1=del&did=";
//				$btn2 = $btn2 . $part_data[$i][0] . "'\" value='削除'></td>";
//				print($btn2);

			}

			print('</tr>');
		}

		print('</table>');

	}

	// 登録結果 破棄
	unset($part_data);

	// セッション情報 保存
	$_SESSION["mode_edit1"] = 'search';
	$_SESSION["permission"] = $permission;

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit":

	print('<br>');

	$smt_id     = $_SESSION["eid"];

	// DB接続
	$mdb2 = db_connect();

	if ($smt_id!=NULL) {

		// SMT部品データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$rack_no  = $res_row[1];
			$product  = $res_row[2];
			$solder   = $res_row[3];
			$p_new    = $res_row[4];
			$p_maker  = $res_row[5];
			$p_sub    = $res_row[6];
			$p_nec    = $res_row[7];
			$r_size   = $res_row[8];
			$exp_item = $res_row[9];
			$rack_old = $res_row[10];
			$rem_part = $res_row[11];
		}

		// SMT部品 在庫管理データ取得
		$res_row = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} else {
			$stock_id  = $res_row[0];
			$stk_reel  = $res_row[2];
			$stk_qty   = $res_row[3];
			$up_date   = $res_row[4];
			$rem_stock = $res_row[5];
		}

		// 更新日 設定(使わない？)
		if ($up_date!=NULL) {
			list($up_y, $up_m, $up_d) = split('[/.-]', $up_date);
		} else {
			$up_y = date("Y");
			$up_m = date("m");
			$up_d = date("d");
		}

		//
		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("header", "title", "補充部品 在庫数編集:");

		$form -> addElement("text", "smt_id", "部品ID:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "rack_old", "現棚番:", array('size'=>15, 'maxlength'=>20));
		$form -> addElement("text", "product", "品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("select", "solder", "はんだ:", $sel_solder);
		$form -> addElement("text", "p_maker", "メーカー品名:", array('size'=>30, 'maxlength'=>50));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		$form -> addElement("header", "title", "");
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		$form -> addElement("hidden", "stock_id");
		$form -> addElement("text", "stk_reel", "リール数:", array('size'=>10, 'maxlength'=>10));
		$form -> addElement("text", "stk_qty", "在庫数:", array('size'=>10, 'maxlength'=>10));

		$form -> addElement("date", "up_date", "更新日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

		$form -> addElement("text", "rem_stock", "備考:", array('size'=>40, 'maxlength'=>100));
		$form -> addElement("static", "br", "");
		$form -> addElement("static", "br", "");

		// 部品データの設定(日付は当日とする)
		$form -> setDefaults(array("smt_id"    =>$smt_id,
									"rack_old" =>$rack_old,
									"product"  =>$product,
									"solder"   =>array("solder"=>$solder),
									"p_maker"  =>$p_maker,
									"stock_id" =>$stock_id,
									"stk_reel" =>$stk_reel,
									"stk_qty"  =>$stk_qty,
									"up_date"  =>array("Y"=>date("Y"), "m"=>date("m"), "d"=>date("d")),
									"rem_stock"=>$rem_stock));

		$form -> addElement("submit", "send", "データベース更新");

		// 入力確認
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> addRule("stk_reel", "リール数を入力して下さい", "required", "", "client");
		$form -> addRule("stk_qty", "在庫数を入力して下さい", "required", "", "client");
		$form -> addRule("stk_reel", "数字ではありません", "numeric", "", "client");
		$form -> addRule("stk_qty", "数字ではありません", "numeric", "", "client");

//		HTML_QuickForm::registerRule("set_no", "regex", "/[a-zA-Z0-9]{9}/");
//		$form -> addRule("rack_no", '棚番(新)の書式が違います', "set_no", "", "client");

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
					"エラー項目を修正して、再度［データベース更新］ボタンをクリックしてください");
			$form->display();
		}

		// セッション情報 保存
		$_SESSION["mode_edit1"] = 'search';
		$_SESSION["permission"] = $permission;

	}

	// DB切断
	db_disconnect($mdb2);

	break;

}


?>

</body>
</html>
