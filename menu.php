<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="content-style-type" content="text/css">
	<title>生産管理システム</title>
	<link rel="stylesheet" type="text/css" href="menu.css">
</head>

<body>

<!--
[生産管理システム]

 *メニュー表示
  各権限により表示メニューを切替える

  2007/03/

  2007/10/02 スタイルシートで背景変更

  2007/12/21 [mw500] 追加

  2008/01/17 準備作業用シートのテスト

  2008/03/13 MW500出荷履歴管理は別プロジェクトへ分けたのでメニューを整理

  2008/03/22 「ストック品在庫数編集」追加

  2008/07/02 「払い出しリール管理」追加

  2008/07/17 「ストック品在庫数編集」→「補充部品 在庫数編集」

  2009/08/03 新規部品登録を「日東S品」と「アドバンス」に分る

  2009/08/07 SMT準備[arrange]追加 アドバンス棚 新規品登録、部品検索からの修正を可とする

  2009/08/27 生産計画分の使用部品集計 雛形作成開始

  2015/09/10 ローカル開発環境でExcel出力NGなので動作確認用に試行錯誤

  2015/09/14 やっとExcel出力がOKになったのでテストメニューはコメントアウト

  2015/12/11 アドバンスの部品管理はもう行っていないので削除

  2017/07/13 文字コードをUTF-8へ

  2017/07/28 日付処理を Warning が出ないように修正

-->

<?php
	include 'lib/calendar.php';

	// calendarクラス初期化
	$O_calendar1 = new calendar(0, 0);
	$O_calendar2 = new calendar(0, 0);

	// 当日を取得
    // 2017/07/28 修正 今は timezone を設定しないとダメらしい
	// $day = getdate();
    date_default_timezone_set('Asia/Tokyo');

	$mode = $_GET["mode"];
?>

<?php
if ($mode=="developer") {
?>
	<!--
	<div align="center"><img src="../graphics/smt_menu.png"></div>
	<br>
	<div align="center" id="menu">
	-->
	<div align="center" id="menu">
	<h1>Menu</h1>
	<ul>
	<li><a href="regist1.php?mode_regist1=select" target="result">データ登録</a></li>
	<li><a href="upload1.php?mode_upload1=smt" target="result">集計データアップロード</a></li>
	<li><a href="regist3.php?mode_regist3=select" target="result">集計データ 棚番検索</a></li>
	<li><a href="regist2.php?mode_regist2=input" target="result">使用完リール登録</a></li>
	<li><a href="regist4.php?mode_regist4=input&rack_sel=0" target="result">新規部品登録(日東S品)</a></li>
	<!--
	<li><a href="regist4.php?mode_regist4=input&rack_sel=1" target="result">新規部品登録(アドバンス)</a></li>
	 -->
	<li><a href="regist5.php?mode_regist5=input" target="result">払い出しリール管理</a></li>
	<li><a href="search1.php?mode_search1=search&permission=admin" target="result">データ検索</a></li>

	<li><a href="disp1.php?mode_disp1=search&permission=admin" target="result">生産計画 使用部品集計</a></li>

	<!--
	<li><a href="test_script1.php" target="result">テストスクリプト</a></li>
	<li><a href="phpinfo.php" target="result">PHP Info</a></li>
	 -->

	<!--
	<li><a href="search1.php?mode_search1=search_2&permission=admin" target="result">準備作業用帳票</a></li>
	<li><a href="setup1.php?mode_setup1=search&permission=admin" target="result">実装リール登録</a></li>
	<li><a href="edit1.php?mode_edit1=search&permission=admin" target="result">補充部品 在庫数編集</a></li>
	<li><a href="test_script1.php" target="result">テストスクリプト</a></li>
	<li><a href="./pdf/pdf_out1.php?id=11789" target="result">PDFテスト</a></li>
	<li><a href="upload1.php?mode_upload1=mw500" target="result">[MW500] データアップロード</a></li>
	<li><a href="regist5.php?mode_regist5=select" target="result">[MW500] 出荷データ登録</a></li>
	<li><a href="search2.php?mode_search2=search&permission=admin" target="result">[MW500] 出荷履歴検索</a></li>
	 -->
	<li><a href="login.php?mode=logout" target="result">ログアウト</a></li>
	</ul>
	</div>

<?php
} elseif ($mode=="admin") {
?>
	<div align="center" id="menu">
	<h1>Menu（管理者用）</h1>
	<ul>
	<!--
	<li><a href="upload1.php?mode_upload1=smt" target="result">データアップロード</a></li>
	<li><a href="regist3.php?mode_regist3=select" target="result">集計データ 棚番検索</a></li>
	-->
	<li><a href="regist4.php?mode_regist4=input&rack_sel=0" target="result">新規部品登録(日東S品)</a></li>
	<!--
	<li><a href="regist4.php?mode_regist4=input&rack_sel=1" target="result">新規部品登録(アドバンス)</a></li>
	 -->
	<li><a href="edit1.php?mode_edit1=search&permission=admin" target="result">補充部品 在庫数編集</a></li>
	<li><a href="regist2.php?mode_regist2=input" target="result">使用完リール登録</a></li>
	<li><a href="regist5.php?mode_regist5=input" target="result">払い出しリール管理</a></li>
	<li><a href="search1.php?mode_search1=search&permission=admin" target="result">データ検索</a></li>
	<li><a href="login.php?mode=logout" target="result">ログアウト</a></li>
	</ul>
	</div>

<?php
} elseif ($mode=="user") {
?>
	<div align="center" id="menu">
	<h1>Menu（作業者用）</h1>
	<ul>
	<li><a href="regist2.php?mode_regist2=input" target="result">使用完リール登録</a></li>
	<li><a href="regist5.php?mode_regist5=input" target="result">払い出しリール管理</a></li>
	<li><a href="search1.php?mode_search1=search&permission=guest" target="result">データ検索</a></li>
	<li><a href="login.php?mode=logout" target="result">ログアウト</a></li>
	</ul>
	</div>

<?php
} elseif ($mode=="guest") {
?>
	<div align="center" id="menu">
	<h1>Menu（ゲスト用）</h1>
	<ul>
	<li><a href="search1.php?mode_search1=search&permission=guest" target="result">データ検索</a></li>
	<li><a href="login.php?mode=logout" target="result">ログアウト</a></li>
	</ul>
	</div>

<?php
} elseif ($mode=="setup") {
?>
	<div align="center" id="menu">
	<h1>Menu（準備用）</h1>
	<ul>
	<li><a href="regist4.php?mode_regist4=input&rack_sel=0" target="result">新規部品登録(日東S品)</a></li>
	<!--
	<li><a href="regist4.php?mode_regist4=input&rack_sel=1" target="result">新規部品登録(アドバンス)</a></li>
	 -->
	<li><a href="search1.php?mode_search1=search&permission=setup" target="result">データ検索</a></li>
	<li><a href="login.php?mode=logout" target="result">ログアウト</a></li>
	</ul>
	</div>

<?php
}
?>

<br>
<hr style="width: 100%; height: 2px;">

<?php
print('<div align="center">');

// 2017/07/28 修正
// $O_calendar1->show_calendar($day["year"], $day["mon"], $day["mday"]);
$O_calendar1->show_calendar(date('Y'), date('m'), date('d'));

// 擬似改行
//print('<td><img src="../graphics/bar_dummy.jpg" width="1" height="5"></td>');
print('<br>');

// 来月表示
// 2017/07/28 修正
// if ($day["mon"] == '12') {
// 	$O_calendar2->show_calendar($day["year"]+1, '1');
// } else {
// 	$O_calendar2->show_calendar($day["year"], ($day["mon"]+1));
// }
if (date('m') == '12') {
	$O_calendar2->show_calendar(date('Y', strtotime('+1 year')), date('m', strtotime('+1 month')));
} else {
	$O_calendar2->show_calendar(date('Y'), date('m', strtotime('+1 month')));
}

// 時計表示
include("lib/watch2.php");

print('</div>');

?>

</body>
</html>
