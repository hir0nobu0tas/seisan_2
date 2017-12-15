<?php
// セッション開始
session_start();
if ($_GET['mode_upload1']=='smt' or $_GET['mode_disp2']=='mw500') {

	$mode = $_GET["mode_upload1"];

} elseif ($_SESSION["mode_upload1"]!=NULL) {

	$mode = $_SESSION["mode_upload1"];

} else {

	$mode = $_GET["mode_upload1"];

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

 *使用部品集計データ登録
  部品名に対応した棚番を付加して出力 部品ストックの在庫確認に使用

  2007/11/09
  2007/12/18 データディレクトリ修正
  2008/01/07 QuickForm.php を require_once ではファイルのアップロードが
             正常に出来ない？
  2008/03/13 MW500出荷履歴管理は別プロジェクトへ分けたのでデータディレクトリ
             を修正

  2008/03/24 ディレクトリ修正

  2010/07/27 session_start()を先頭に移動

  2017/07/13 文字コードをUTF-8へ

-->

<?php
//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------

// PEARライブラリ
// 2008/01/07 require_onceではファイルのアップロードがうまく出来ない？
//require_once 'HTML/QuickForm.php';
include_once 'HTML/QuickForm.php';

//---------------------------------------------------------------

function upload_process($values) {
	global $file;
	if ($file -> isUploadedFile()) {
    	$file -> moveUploadedFile($values['path']);
//	if ($file -> is_Uploaded_File()) {
//    	$file -> move_Uploaded_File($values['path']);
    	print "ファイルのアップロードが完了しました！";
	} else {
		print "ファイルをアップロード出来ませんでした！";
	}
}


print('<br>');


switch($mode) {
case "smt":

	$form = new HTML_QuickForm('upload_form', 'POST');
	$form -> addElement("header", NULL, "使用部品(SMT)集計データ CSVファイル アップロード");
	$path = "../data/stock/use";
	$form -> addElement('hidden', 'path', $path);
	$file =& $form->addElement('file', 'filename', 'ファイル名：', array('size'=>50));
	$form -> addRule('filename', 'ファイルを選択してください', 'uploadedfile');
	$form -> addRule("name","ファイルサイズは100KBまでです。","maxfilesize",102400);
	$form -> addElement('submit', 'btnUpload', 'CSVファイル アップロード');

	if ($form -> validate()) {
		$form -> process('upload_process', true);
	} else {
		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
		$form -> display();
	}

	$_SESSION["mode_upload1"] = 'smt';

	break;

//case "mw500":
//
//	$form = new HTML_QuickForm('upload_form', 'POST');
//	$form -> addElement("header",NULL,"MW500 出荷履歴データ CSVファイル アップロード");
//	$path = "../data/kensa/mw500";
//	$form -> addElement('hidden', 'path', $path);
//	$file =& $form->addElement('file', 'filename', 'ファイル名：', array('size'=>50));
//	$form -> addRule('filename', 'ファイルを選択してください', 'uploadedfile');
//	$form -> addRule("name","ファイルサイズは500KBまでです。","maxfilesize",5120000);
//	$form -> addElement('submit', 'btnUpload', 'CSVファイル アップロード');
//
//	if ($form -> validate()) {
//	    $form -> process('upload_process', true);
//	} else {
//		$form -> setRequiredNote("<font color='Red'>*</font> 必須項目です");
//	    $form -> display();
//	}
//
//	break;

}

?>

</body>
</html>
