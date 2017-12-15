<?php
//セッション開始
session_start();
if ($_GET['mode_setup1']=='search') {

	unset($_SESSION["mfd"]);

	$mode = $_GET["mode_setup1"];
	$permission = $_GET["permission"];

} elseif ($_SESSION["mode_setup1"]!=NULL) {

	// editモード時には編集又は削除を$_GETで再選択
	$mode = $_SESSION["mode_setup1"];
	if ($mode=="edit") {

		// SelectBox編集の場合は$_GETはNULL -> edit_2モードへ
		if ($_GET["mode_setup1"]!=NULL) {
			$mode = $_GET["mode_setup1"];
		} else {
			$mode = edit_2;
		}

	}

	// 編集するIDと削除するIDを別管理
	if ($_SESSION["eid"]==NULL or $_GET["eid"]!=NULL) {
		$eid = $_GET["eid"];
		$_SESSION["eid"] = $eid;
	}

	if ($_SESSION["did"]==NULL or $_GET["did"]!=NULL) {
		$did = $_GET["did"];
		$_SESSION["did"] = $did;
	}

} else {

	$mode = $_GET["mode_setup1"];

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

 *実装リール登録
  準備作業時にセットする部品リールの管理番号(バーコード)をスキャンして登録する
  事により使用部品のロット管理を行なう

  2008/09/

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
include 'inc/parameter_inc.php';
include 'lib/com_func1.php';

// PEARライブラリ
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';

// 配列設定
$sel_mc1 = array(0=>"指定無し", 1=>"JUKI[1号機]", 2=>"JUKI[2号機}",
	   			3=>"九松[3号機]", 4=>"手付け", 9=>"外注");

$sel_mc2 = array(0=>"未定", 1=>"JUKI[1号機]", 2=>"JUKI[1/2号機]",
   				3=>"九松[3号機]", 4=>"JUKI[1号機]/九松[3号機]",
   				5=>"JUKI[1/2号機]/九松[3号機]", 6=>"手付け",
   				9=>"外注");

//$sel_mfd = array(0=>"当日のみ", 1=>"1週間", 2=>"2週間");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_setup1"]);
	unset($_SESSION["eid"]);
	unset($_SESSION["did"]);
	unset($_SESSION["mfd"]);
}


switch($mode) {
case "search":

	print('<br>');

	// セッション破棄
	unset_session();

	$form = new HTML_QuickForm("search", "POST");
	$form -> addElement("header", "title", "生産計画 検索:");
	$form -> addElement("date", "mfd", "生産予定日:",
						array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

	// 今日の取得
	$yyyy = date("Y");
	$mm = date("m");
	$dd = date("d");

	// 日付範囲は当日をデフォルトとする
	$form -> setDefaults(array("mfd"=>array("Y"=>$yyyy, "m"=>$mm, "d"=>$dd)));

	$form -> addElement("submit", "send", "検索");
	$form->display();

//	if ($form -> validate()) {
//		$form -> process("showForm",FALSE);
//	} else {
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//		$form -> setJsWarnings("以下の項目でエラーが発生しました",
//			"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
//		$form->display();
//	}

	// コメント
//	print('<div align="left"><font size="3" color="#0066cc"><b>');
//	print('＊作業時間は「当日のみ」、号機指定有りでリスト表示させた時に再計算されます');
//	print('</b></div>');

	$_SESSION["mode_setup1"]='disp';

	break;


case "disp":

	// DB接続
	$mdb2_smt = db_connect_smt();

	// 検索日付 取得
	$yyyy = (int)$_POST['mfd']["Y"];
	$mm   = (int)$_POST['mfd']["m"];
	$dd   = (int)$_POST['mfd']["d"];
	if ($yyyy!=0) {
		$mfd = date('Y-m-d', mktime (0, 0, 0, $mm, $dd, $yyyy));
	}

	// $_SESSION["eid']にデータ有り -> 2回目の表示(データの更新も行う)
	if ($_SESSION["eid"]!=NULL) {

		$plan_id    = (int)Trim($_SESSION["eid"]);
		$order_id   = (int)Trim($_POST['order_id']);
		$operate_no = mb_convert_kana(Trim($_POST['operate_no']["0"]), "KVa");
		$dash_no    = mb_convert_kana(Trim($_POST['operate_no']["1"]), "KVa");
		$eqp        = mb_convert_kana(Trim($_POST['eqp']), "KVa");
		$product    = mb_convert_kana(Trim($_POST['product']), "KVa");
		$solder     = (int)$_POST['solder']["solder"];
		$machine    = (int)$_POST['machine']["machine"];
		$qty10      = (int)Trim($_POST['qty10']);
		$qty20      = (int)Trim($_POST['qty20']);
		$qty21      = (int)Trim($_POST['qty21']);
		$qty30      = (int)Trim($_POST['qty30']);
		$ass_set    = (int)$_POST['ass_set'];

		$yyyy       = (int)$_POST['mfd']["Y"];
		$mm         = (int)$_POST['mfd']["m"];
		$dd         = (int)$_POST['mfd']["d"];
		if ($yyyy!=0) {
			$mfd = date('Y-m-d', mktime (0, 0, 0, $mm, $dd, $yyyy));
		}

		$mf_order   = (int)$_POST['mf_order'];
//		$priority   = (int)$_POST['priority'];

		// [order_info]から行取得
		$order_row = row_order($mdb2, $order_id);
		$product_id = $order_row[1];

		// トランザクションブロック開始
   		$res = $mdb2->beginTransaction();

		// [planning] 更新
		$res = $mdb2->exec("UPDATE planning SET qty20='$qty20' WHERE plan_id='$plan_id'");
		$res = $mdb2->exec("UPDATE planning SET qty21='$qty21' WHERE plan_id='$plan_id'");
		$res = $mdb2->exec("UPDATE planning SET mfd='$mfd' WHERE plan_id='$plan_id'");
		$res = $mdb2->exec("UPDATE planning SET mf_order='$mf_order' WHERE plan_id='$plan_id'");
//		$res = $mdb2->exec("UPDATE planning SET priority='$priority' WHERE plan_id='$plan_id'");
		$res = $mdb2->exec("UPDATE planning SET machine='$machine' WHERE order_id='$order_id'");
		$res = $mdb2->exec("UPDATE planning SET qty30='$qty30' WHERE plan_id='$plan_id'");

		// [order_info] 更新
		$res = $mdb2->exec("UPDATE order_info SET ass_set='$ass_set' WHERE order_id='$order_id'");
		$res = $mdb2->exec("UPDATE order_info SET plan='$mfd' WHERE order_id='$order_id'");

		// [product]更新
		$res = $mdb2->exec("UPDATE product_smt SET solder='$solder' WHERE product_id='$product_id'");
		// 2007/07/11 更新日追加
		$update_item = date('Y-m-d');
		$res = $mdb2->exec("UPDATE product_smt SET update_item='$update_item' WHERE product_id='$product_id'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

	}

	// 検索する条件の取得
//	if ($_SESSION["mfd"]!=NULL) {
//		$mfd = $_SESSION["mfd"];
//	}

	// 検索条件によるSQL文作成
	$sql_select = "SELECT * FROM planning T3 JOIN order_info T2 ON(T3.order_id=T2.order_id) JOIN product_smt T1 ON(T2.product_id=T1.product_id)";
	$sql_where  = " WHERE mfd='$mfd'";
	$sql_order  = " ORDER BY machine, plan_id";
	$search_sql = $sql_select . $sql_where . $sql_order;

print('<pre>');
var_dump($search_sql);
print('</pre>');

	$res_query = $mdb2_smt->query($search_sql);
	$res_query = err_check($res_query);

	// タイトル
	print('<br>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('生産計画(詳細) [');
	print($mfd);
	print(' ] 作成日： ');
	print(date("Y-m-d H:i:s"));
	print('</b></font></div>');

	// method="post"でcheckboxの状態が送られる </form>は一覧の最後で設定
//	print('<form method="post">');

	// 項目名
	print('<td><table border="1" align=center>');
	print('<tr bgcolor="#cccccc">');
	print('<th>工番</th>');
	print('<th>品名</th>');
	print('<th>基板名</th>');
	print('<th>はんだ</th>');
	print('<th>全数</th>');
	print('<th>実装面</th>');
	print('<th>生産日</th>');
	print('<th>号機</th>');
	print('<th>備考</th>');
	print('<th>リール登録</th>');
	print('</tr>');

	// DB接続
//	$mdb2 = db_connect();

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		// [planning]
		$plan_id    = $row['plan_id'];		//
		$ass_side   = $row['ass_side'];		// 実装面
		$mfd        = $row['mfd'];			// 生産日
		$machine    = $row['machine'];		// 号機
		$priority   = $row['priority'];		// 優先順位
		$chk[0]     = $mfd;
		$chk[1]     = $machine;

		// [ordert_info]
		$operate_no = $row['operate_no'];	// 工番
		$dash_no    = $row['dash_no'];		// ダッシュ
		$qty10      = $row['qty10'];		// 数量
		$rem_ass    = $row['rem_ass'];		// 備考

		// [product_smt]
		$product    = $row['product'];		// 品名コード
		$board      = $row['board'];		// 基板コード
		$solder     = $row['solder'];		// はんだ

		// 部品リールの登録状況確認
//		$set_row = $mdb2->queryRow("SELECT * FROM set_reel WHERE plan_id='$plan_id'");
//		if (PEAR::isError($set_row)) {
//		} else {
//			$set_r_id = $set_row[0];
//		}
//		unset($set_row);

		// 号機毎にセパレータを入れる
		if ($chk[3]!=NULL and $chk[1]!=$chk[3]) {
			print('<tr>');
			for ($i=1; $i<=11; $i++) {
				print('<td bgcolor="#b0c4de"><br></td>');
			}
			print('</tr>');
		}

		// 列ごとに色を変える(表面と裏面で色固定)
		if ($priority==1) {
			$bgcolor = '#ffd700';
		} else {
			if ($row['solder']==2) {
				if ($row['ass_side']==1) {
					$bgcolor = '#ffe4e1';
				} elseif ($row['ass_side']==2) {
					$bgcolor = '#f5deb3';
				}
			} else {
				if ($row['ass_side']==1) {
					$bgcolor = '#dcdcdc';
				} elseif ($row['ass_side']==2) {
					$bgcolor = '#f8f8ff';
				}
			}
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		print($color_set);
		print($operate_no);
		print('</td>');

		print($color_set);
		print($product);
		print('</td>');

		print($color_set);
		if ($board!=NULL) {
			print($board);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		print(solder_name($solder));
		print('</td>');

		print($color_set);
		print($qty10);
		print('</td>');

		print($color_set);
		print(ass_side_name($ass_side));
		print('</td>');

		print($color_set);
		print($row['mfd']);
		print('</td>');

		print($color_set);
		if ($machine!=NULL) {
			print(mc_no($machine));
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
		if ($rem_ass!=NULL) {
			print($rem_ass);
		} else {
			print('<br>');
		}
		print('</td>');

		print($color_set);
//		if ($set_r_id!=NULL) {
//			print('登録済み');
//		} else {
			print('<br>');
//		}
		print('</td>');

		print($color_set);
		$btn1 = "<input type='button' name='regist' onClick=\"location.href='setup1.php?mode_setup1=regist&eid=";
		$btn1 = $btn1 . $plan_id . "'\" value='登録'></td>";
		print($btn1);

		print($color_set);
		$btn2 = "<input type='button' name='conf' onClick=\"location.href='setup1.php?mode_setup1=conf&eid=";
		$btn2 = $btn2 . $plan_id . "'\" value='確認'></td>";
		print($btn2);

		$chk[2] = $mfd;
		$chk[3] = $machine;


	}

	// 確認用変数破棄
	unset($chk);

	// ResultSet開放
	$res_query->free();

	// DB切断
	db_disconnect($mdb2_smt);

	break;


case "regist":

	//$plan_id = $eid;
	$plan_id = $_SESSION["eid"];

	// DB接続
	$mdb2 = db_connect();

	if ($plan_id!=NULL) {

		// [planning]から行取得
		$plan_row = row_plan($mdb2, $plan_id);
			$order_id = $plan_row[1];
			$ass_side = $plan_row[2];
			$qty20 = $plan_row[3];
			$qty21 = $plan_row[4];
			$mfd = $plan_row[5];
			$machine = $plan_row[6];
			$mf_order = $plan_row[7];
//			$priority = $plan_row[8];
			$qty30 = $plan_row[13];

			// 年月日の分解
			list($yyyy1, $mm1, $dd1) = split('[/.-]', $mfd);

		// [order_info]から行取得
		// 2006/09/28 修正
		$order_row = row_order($mdb2, $order_id);
			$product_id = $order_row[1];
			$operate_no = $order_row[2];
			$dash_no = $order_row[3];
			$qty10 = $order_row[4];
			$qty11 = $order_row[5];
			$qty12 = $order_row[6];
			$ass_set = $order_row[10];

		// [product_smt]から行取得
		$product_row = row_product_smt($mdb2, $product_id);
			$eqp = $product_row['2'];
			$product = $product_row['3'];
			$mc = $product_row[4];
			$solder = $product_row[6];
			$board = $product_row[14];

		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("header", "title", "生産計画修正:");
		$form -> addElement("text", "order_id", "オーダーID:");

		$ope =& HTML_QuickForm::createElement('text', '', null, array('size' => 10, 'maxlength' => 10));
		$dash =& HTML_QuickForm::createElement('text', '', null, array('size' => 10, 'maxlength' => 10));
		$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');

		$form -> addElement("text", "eqp", "装置名:", array('size' => 40, 'maxlength' => 100));
		$form -> addElement("text", "product", "品名:", array('size' => 40, 'maxlength' => 100));
		$form -> addElement("text", "board", "基板名:", array('size'=>40, 'maxlength'=>100));

		$form -> addElement("text", "qty10", "数量:");
		$form -> addElement("select", "ass_side", "面:", $sel_side);

		$solder_set[] = $form -> createElement("radio", "solder", NULL, "不明", '0');
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "共晶", '1');
		$solder_set[] = $form -> createElement("radio", "solder", NULL, "鉛フリー", '2');
		$form -> addGroup($solder_set, "solder", "はんだ種類:");

		$form -> addElement("select", "machine", "号機:", $sel_mc1);

		$form -> addElement("text", "qty20", "生産数:");
		$form -> addElement("text", "qty21", "残数:");
		$form -> addElement("text", "qty30", "実績数:");
		$form -> addElement("advcheckbox", "ass_set", "ASS方法:", "集合ASS", NULL, array('0', '1'));
		$form -> addElement("date", "mfd", "生産日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
		$form -> addElement("select", "mf_order", "生産順番:", $sel_order);
// 		$form -> addElement("select", "priority", "優先順位:", $sel_priority);

		// 基礎データの設定
		// 2006/09/28 修正
		$form -> setDefaults(array("order_id"      =>$order_id,
									"operate_no[0]"=>$operate_no,
									"operate_no[1]"=>$dash_no,
									"eqp"          =>$eqp,
									"product"      =>$product,
									"board"        =>$board,
									"solder"       =>array("solder"=>$solder),
									"machine"      =>array("machine"=>$machine),
									"qty10"        =>$qty10,
									"ass_side"     =>array("ass_side"=>$ass_side),
									//"qty20"      =>$qty20,
									//"qty21"      =>$qty21,
									"qty30"        =>$qty30,
									"ass_set"      =>$ass_set,
									"mfd"          =>array("Y"=>$yyyy1, "m"=>$mm1, "d"=>$dd1),
									"mf_order"     =>array("mf_order"=>$mf_order)));
//									"priority"     =>array("priority"=>$priority)));

		if ($ass_side==2) {
			$form -> setDefaults(array("qty20"=>$qty11));
			$form -> setDefaults(array("qty21"=>$qty11));
		} elseif ($ass_side==1) {
			$form -> setDefaults(array("qty20"=>$qty12));
			$form -> setDefaults(array("qty21"=>$qty12));
		}

		$form -> addElement("submit", "send", "データベース登録");

		// 数量の入力確認
		$form -> addRule("qty20", '数量を入力して下さい。', 'required', '', 'client');

		if ($form -> validate()) {
			$form -> process("showForm",FALSE);
		} else {
			$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
			$form -> setJsWarnings("以下の項目でエラーが発生しました",
				"エラー項目を修正して、再度［データベース登録］ボタンをクリックしてください");
			$form->display();
		}

		//$_SESSION["eid"] = $plan_id;
		$_SESSION["mode_setup1"] = 'disp';

	} else {

		// セッション破棄
		unset_session();

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "del":

	$plan_id = $_SESSION["did"];

	// DB接続
	$mdb2 = db_connect();

	if ($plan_id!=NULL) {

		// [planning]から行取得
		$plan_row = row_plan($mdb2, $plan_id);
			$order_id = $plan_row[1];
			$ass_side = $plan_row[2];
			$qty20 = $plan_row[3];
			$qty21 = $plan_row[4];
			$mfd = $plan_row[5];
			$machine = $plan_row[6];
			$mf_order = $plan_row[7];
			$priority = $plan_row[8];

			// 年月日の分解
			list($yyyy1, $mm1, $dd1) = split('[/.-]', $mfd);

		// [order_info]から行取得
		// 2006/09/28 修正
		$order_row = row_order($mdb2, $order_id);
			$product_id = $order_row[1];
			$operate_no = $order_row[2];
			$dash_no = $order_row[3];
			$qty10 = $order_row[4];
			$qty11 = $order_row[5];
			$qty12 = $order_row[6];
			$ass_set = $order_row[10];

		// [product_smt]から行取得
		$product_row = row_product_smt($mdb2, $product_id);
			$eqp = $product_row[2];
			$product = $product_row[3];
			$mc = $product_row[4];
			$solder = $product_row[6];

		$form = new HTML_QuickForm("edit", "POST");
		$form -> addElement("header", "title", "生産計画削除:");
		$form -> addElement("text", "plan_id", "計画ID:");

		$ope =& HTML_QuickForm::createElement('text', '', null, array('size' => 10, 'maxlength' => 10));
		$dash =& HTML_QuickForm::createElement('text', '', null, array('size' => 10, 'maxlength' => 10));
		$form -> addGroup(array($ope, $dash), 'operate_no', '工番:', ' ダッシュ:');

		$form -> addElement("text", "eqp", "装置名:", array('size' => 40, 'maxlength' => 100));
		$form -> addElement("text", "product", "品名:", array('size' => 40, 'maxlength' => 100));

		$form -> addElement("text", "qty10", "数量:");
		$form -> addElement("select", "ass_side", "面:", $sel_side);

		$form -> addElement("text", "qty20", "生産数:");
		$form -> addElement("text", "qty21", "残数:");
		$form -> addElement("date", "mfd", "生産日:",
					array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));

		$plan_del[] = $form -> createElement("radio", "plan_del", NULL, "生産計画削除", '1');
		$plan_del[] = $form -> createElement("radio", "plan_del", NULL, "削除キャンセル", '0');
		$form -> addGroup($plan_del, "plan_del", "データ削除？:");

		// 基礎データの設定
		// 2006/09/28 修正
		$form -> setDefaults(array("plan_id"       =>$plan_id,
									"operate_no[0]"=>$operate_no,
									"operate_no[1]"=>$dash_no,
									"eqp"          =>$eqp,
									"product"      =>$product,
									"qty10"        =>$qty10,
									"ass_side"     =>array("ass_side"=>$ass_side),
									//"qty20"      =>$qty21,
									//"qty21"      =>$qty21,
									"mfd"          =>array("Y"=>$yyyy1, "m"=>$mm1, "d"=>$dd1),
									"plan_del"     =>array("plan_del"=>'1')));

		if ($ass_side==2) {
			$form -> setDefaults(array("qty20"=>$qty11));
			$form -> setDefaults(array("qty21"=>$qty11));
		} elseif ($ass_side==1) {
			$form -> setDefaults(array("qty20"=>$qty12));
			$form -> setDefaults(array("qty21"=>$qty12));
		}

		$form -> addElement("submit", "send", "一覧へ戻る");

		$form->display();

		$_SESSION["did"] = $plan_id;
		$_SESSION["mode_setup1"] = 'disp';

	} else {

		// セッション破棄
		unset_session();

	}

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit_2":

	$plan3 = $_SESSION['plan1'];
	$mf_order = $_POST['mf_order'];
	$mf_set = $_POST['mf_set'];
	$id_data = $_POST['id_data'];

	// 年月日 分解
	list($date[0], $date[1], $date[2]) = split('[/.-]', $plan3);

	if ($mf_order!=NULL) {

		// method="post"でボタン動作有効
		print('<form method="post">');
		print('<br><br>');

		// SheckBox編集ボタン
		$btn4 = "<th colspan=10><input type='submit' value='セレクトボックス編集をデータベースに反映'></th>";
		print($btn4);
		print('</form>');

		$_SESSION["id_data"] = $id_data;
		$_SESSION["mf_order"] = $mf_order;
		$_SESSION["mf_set"] = $mf_set;
		$_SESSION["mode_setup1"] = 'disp';

	} else {

		// セッション破棄
		unset_session();

	}

	break;

}

?>

</body>
</html>
