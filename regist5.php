<?php
// セッション開始
session_start();
if ($_GET['mode_regist5']=='input') {

	$mode = $_GET["mode_regist5"];
	unset($_SESSION["set_y"]);
	unset($_SESSION["set_m"]);
	unset($_SESSION["set_d"]);

} elseif ($_SESSION["mode_regist5"]!=NULL) {

	$mode = $_SESSION["mode_regist5"];

} else {

	$mode = $_GET["mode_regist5"];

}
?>

<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>生産管理システム</title>
	<!--
	<script type="text/javascript" src="lib/colorful.js"></script>
	  -->
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<!--
[生産管理システム]

 * 部品リール払い出し 登録
  外注へ払い出すリールの管理

  2008/06/26
  2008/07/02 テスト版

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
include_once 'lib/com_func1.php';
include_once 'lib/com_func2.php';
include_once 'lib/regist5_func.php';

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

$process_list = array(0=>"-Select-",
					  1=>"払い出しリール登録",
					  2=>"返却リール登録",
					  3=>"リール登録情報削除",
					  4=>"払い出しリール一覧",
					  5=>"未返却リール一覧");

//---------------------------------------------------------------

// セッション破棄
function unset_session() {
	unset($_SESSION["mode_regist5"]);
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


// セッション破棄
//$res = unset_session();
//unset($_SESSION["mode_regist5"]);

switch($mode) {
case "input":

	print('<br>');

	// リール管理番号入力へフォーカス設定
	print('<body onLoad="document.input.check_no.focus()">');

	// 検索条件の取得(半角カナ->全角カナ 全角英数->半角英数変換)
	if ($_POST['check_no']!=NULL) {
		$check_no = mb_convert_kana(Trim($_POST['check_no']), "KVa");
	} else {
		$check_no = NULL;
	}

	// 処理選択
	$sel_process = $_POST['sel_process']["sel_process"];
	$process_disp = process_name($sel_process);

	// 払い出し先選択
	$sel_mc = $_POST['sel_mc']["sel_mc"];
	$mc_disp = mc_name($sel_mc);

	// 登録日設定
	if ($_POST['date_process']["Y"]!=NULL) {
		$set_y = $_POST['date_process']["Y"];
		$set_m = $_POST['date_process']["m"];
		$set_d = $_POST['date_process']["d"];

		// 登録日
		$set_date = date('Y-m-d', mktime (0, 0, 0, $set_m, $set_d, $set_y));

		$_SESSION["set_y"] = $set_y;
		$_SESSION["set_m"] = $set_m;
		$_SESSION["set_d"] = $set_d;
	}

	// 備考(半角カナ->全角カナ 全角英数->半角英数変換)
	if ($_POST['rem_manage']!=NULL) {
		$rem_manage = mb_convert_kana(Trim($_POST['rem_manage']), "KVa");
	} else {
		$rem_manage = NULL;
	}

	// DB接続
	$mdb2 = db_connect();

	if ($sel_process==1 or $sel_process==2 or $sel_process==3) {

		// リール登録処理
		$res = reel_reg($mdb2, $sel_process, $check_no, $sel_mc, $set_date, $rem_manage);

	}

	// 条件入力画面
	$form = new HTML_QuickForm("input", "POST");
	$form -> addElement("header", "title", "払い出しリール 管理 :");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");

	$form -> addElement("select", "sel_process", "処理選択:", $process_list);
	$form -> addElement("static", "br", "");

	$form -> addElement("text", "check_no", "リール管理番号:", array('size'=>30, 'maxlength'=>9));
	$form -> addElement("static", "br", "");

	$mc_sel[] = $form -> createElement("radio", "sel_mc", NULL, "社内", 0);
	$mc_sel[] = $form -> createElement("radio", "sel_mc", NULL, "外注", 1);
	$form -> addGroup($mc_sel, "sel_mc", "払い出し先:");
	$form -> addElement("static", "br", "");

	$form -> addElement("date", "date_process", "登録日:",
						array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
	$form -> addElement("static", "br", "");

	$form -> addElement("text", "rem_manage", "備考:", array('size'=>50, 'maxlength'=>100));
	$form -> addElement("static", "br", "");

	$form -> addElement("submit", "send", "登録！");
//	$form -> applyFilter('check_no', 'constForm');

	// 日付初期設定
	if ($_SESSION["set_y"]!=NULL) {
		$set_y = $_SESSION["set_y"];
		$set_m = $_SESSION["set_m"];
		$set_d = $_SESSION["set_d"];
	} else {
		$set_y = date("Y");
		$set_m = date("m");
		$set_d = date("d");
	}

	// 初期値設定
	$form -> setDefaults(array("check_no"     =>$check_no,
								"sel_process" =>array("sel_process"=>'1'),
								"sel_mc"      =>array("sel_mc"=>'1'),
								"date_process"=>array("Y"=>$set_y, "m"=>$set_m, "d"=>$set_d)));

	// 入力チェック(一旦コメントアウト)
//	$form -> addRule("check_no", '管理番号は英数半角9文字(N********)です', "regex", '/^[N]{1}[0-9]{8}/', "client");
//	if ($form -> validate()) {
//		$form -> process("showForm", FALSE);
//	} else {
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//		$form -> setJsWarnings("以下の項目でエラーが発生しました", "");
//		$form -> display();
//	}
	$form -> display();

	// 一覧表示
	include 'inc/regist5_inc1.php';

	// DB切断
	db_disconnect($mdb2);

	$_SESSION["mode_regist5"] = 'input';

	break;


case "regist":

	break;

}

?>
</body>
</html>
