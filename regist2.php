<?php
// セッション開始
session_start();
if ($_GET['mode_regist2']=='input') {

	$mode = $_GET["mode_regist2"];

} elseif ($_SESSION["mode_regist2"]!=NULL) {

	$mode = $_SESSION["mode_regist2"];

} else {

	$mode = $_GET["mode_regist2"];

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

 * 使用完リール 登録
  使い切ったリールの管理番号を登録する事で簡易的な在庫管理を行う

  2007/10/12

  2007/10/16
  入力チェックを入れてみたがイマイチ 一旦コメントアウト

  2007/11/06
  使用完日を任意の日付に設定できるように修正(デフォルトは当日)

  2008/01/11
  メニューから開いた時に管理番号の入力ボックスにfocusが設定できていなかった
  colorful.jsをコメントアウト Firefoxは有効でもfocusの設定が有効だがIEはダメ

  2008/03/24
  細かい修正

  2010/07/27
  session_start()を先頭に移動

  2013-05-22
  リール管理番号を2013-04と2013-05は間違って10桁で採番してしまったので入力文字数
  を10文字へ変更

  2013-06以降は従来の9桁で採番するが暫くはこの10文字が入力可能とする

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
	unset($_SESSION["mode_regist2"]);
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
unset($_SESSION["mode_regist2"]);

switch($mode) {
case "input":

	print('<br>');

	// リール管理番号入力へフォーカス設定
	print('<body onLoad="document.input.check_no.focus()">');

	// 検索条件の取得(半角カナ->全角カナ 全角英数->半角英数変換)
	if ($_POST['check_no']!=NULL) {
		$check_no = mb_convert_kana(Trim($_POST['check_no']), "KVa");
//		$_SESSION["check_no"] = $check_no;
	} else {
//		$check_no = $_SESSION['check_no'];
		$check_no = NULL;
	}

	// 登録日設定
	$yyyy = $_POST['set_date']["Y"];
	$mm = $_POST['set_date']["m"];
	$dd = $_POST['set_date']["d"];
	if ($yyyy!=NULL) {
		$set_date = date('Y-m-d', mktime (0, 0, 0, $mm, $dd, $yyyy));
	}

	$_SESSION["set_y"] = $yyyy;
	$_SESSION["set_m"] = $mm;
	$_SESSION["set_d"] = $dd;

	$form = new HTML_QuickForm("input", "POST");
	$form -> addElement("header", "title", "使用完リール 登録 :");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	//$form -> addElement("text", "check_no", "リール管理番号:", array('size'=>30, 'maxlength'=>9));
	$form -> addElement("text", "check_no", "リール管理番号:", array('size'=>30, 'maxlength'=>10));
	$form -> addElement("static", "br", "");
	$form -> addElement("date", "set_date", "使用完日:",
						array("language"=>"ja","minYear"=>2000,"maxYear"=>2030,"format"=>"Ymd"));
	$form -> addElement("static", "br", "");
	$form -> addElement("submit", "send", "登録！");
//	$form -> applyFilter('check_no', 'constForm');

	// 日付初期設定
	if ($_SESSION["set_y"]==NULL) {
		$yyyy = date("Y");
		$mm   = date("m");
		$dd   = date("d");
	} else {
		$yyyy = $_SESSION["set_y"];
		$mm   = $_SESSION["set_m"];
		$dd   = $_SESSION["set_d"];
	}

	// 初期値設定
	$form -> setDefaults(array("check_no"=>$check_no,
							   "set_date"=>array("Y"=>$yyyy, "m"=>$mm, "d"=>$dd)));

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

	print('<hr style="width: 100%; height: 2px;">');

	// DB接続
	$mdb2 = db_connect();

	// リール管理番号、日付 取得
//	$end_date = $yyyy . '-' . $mm . '-' . $dd;
	$end_date = date('Y-m-d', mktime(0, 0, 0, $mm, $dd, $yyyy));

	$res = $mdb2->exec("UPDATE reel_no SET end_date='$end_date' WHERE check_no='$check_no'");

	print('<table border="1" align=center>');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('使用完リール 登録 作成日：');
	print(date("Y-m-d H:i"));
	print('<br>');
	print('</b></font></div>');
	print('</caption>');

	// 項目名
	print('<tr bgcolor="#cccccc">');
	print('<th>納入日</th>');
	print('<th>管理番号</th>');
	print('<th>連</th>');
	print('<th>総</th>');
	print('<th>はんだ</th>');
	print('<th>品名</th>');
	print('<th>メーカー品名</th>');
	print('<th>メーカー</th>');
	print('<th>数量</th>');
	print('<th>ロット</th>');
	print('<th>使用期限</th>');
	print('<th>使用完日</th>');
	print('<th>備考</th>');

	$res = $mdb2->queryRow("SELECT * FROM reel_no WHERE check_no='$check_no'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]!=NULL) {

		print('<tr>');

		print('<td>');
		print($res[1]);
		print('</td>');

		print('<td>');
		print($res[2]);
		print('</td>');

		print('<td>');
		print($res[3]);
		print('</td>');

		print('<td>');
		print($res[4]);
		print('</td>');

		print('<td>');
		print(solder_name($res[5]));
		print('</td>');

		print('<td>');
		if ($res[6]!=NULL) {
			print($res[6]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[7]!=NULL) {
			print($res[7]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[8]!=NULL) {
			print($res[8]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[9]!=NULL) {
			print($res[9]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[10]!=NULL) {
			print($res[10]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[11]!=NULL) {
			print($res[11]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[12]!=NULL) {
			print($res[12]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('<td>');
		if ($res[13]!=NULL) {
			print($res[13]);
		} else {
			print('<br>');
		}
		print('</td>');

		print('</tr>');

	}

	print('</table></td>');
	print('</form>');

	// DB切断
	db_disconnect($mdb2);

//	$_SESSION["mode_regist2"] = 'regist';
	$_SESSION["mode_regist2"] = 'input';

	break;


case "regist":

	break;

}

?>
</body>
</html>
