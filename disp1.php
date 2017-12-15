<?php
// セッション開始
session_start();
if ($_GET['mode_disp1']=='search') {

	$mode = $_GET["mode_disp1"];

} elseif ($_SESSION["mode_disp1"]!=NULL) {

	$mode = $_SESSION["mode_disp1"];

} else {

	$mode = $_GET["mode_disp1"];

}
?>

<!doctype html public "-//w3c//dtd html 4.01 Transitional//en">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>生産管理システム</title>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<!--
[生産管理システム]

 *生産計画の使用部品集計(試作)
  生産計画に対応するプログラム名をcsvで読み込んで使用部品の集計を行う

  2009/09/
  2010/07/27 session_start()を先頭に移動

  2017/07/13 文字コードをUTF-8へ

-->

<?php

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------
// 共有関数
include_once 'inc/parameter_inc.php';
include_once 'lib/com_func1.php';

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

//---------------------------------------------------------------

unset($_SESSION);


switch($mode) {
case "search":

	// DB接続
	$mdb2 = db_connect();

	// 作業用一時テーブル作成
	$res = $mdb2->exec("CREATE TEMP TABLE data_tmp (
						tmp_id SERIAL,
						data0 TEXT,
						data1 TEXT,
						data2 TEXT,
						data3 TEXT,
						data4 TEXT,
						CONSTRAINT data_tmp_pkey PRIMARY KEY (tmp_id))");

	// 作業用一時テーブルクリア
	//$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

	// ファイルパスの取得
	$data_dir = "../data/total/";
	$data_file = getdirtree($data_dir);

	// ファイル一覧の分解(ファイル名のみとフルパス名)
	$data_cnt = count($data_file);

	if ($data_cnt!=NULL) {
		for ($k=0; $k<$data_cnt; $k++) {
			list($csv[$k], $csv_full[$k]) = each($data_file);
		}
	}

	$form = new HTML_QuickForm("select", "POST");
	$form -> addElement("static", "br", "");
	$form -> addElement("header", "title", "生産計画 使用部品集計:");
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("select", "data_csv", "使用ファイル リスト:", $csv);
	$form -> addElement("static", "br", "");
	$form -> addElement("static", "br", "");
	$form -> addElement("submit", "send", "集計開始");
	$form -> display();

	$_SESSION["mode_disp1"] = 'search';
	$_SESSION["csv"]        = $csv;
	$_SESSION["csv_full"]   = $csv_full;

//	$csv_full = $_SESSION['csv_full'];
//	$cnt_csv  = count($csv_full) - 1;
//	$csv      = $_SESSION['csv'];
	$csv_file = $csv[$_POST['data_csv']];


	if ($csv_file!=NULL) {

		$data_csv = "../data/total/" . $csv_file;

		$fp = fopen($data_csv, "r");

		// 作業用一時テーブルへ格納
		while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

			// Trimによる前後スペース削除
			for ($i=0; $i<=3; $i++) {
				$data[$i] = Trim($data[$i]);
			}

			$mf_date = $data[0];
			$mf_no   = $data[1];
			$mf_file = $data[2];
			$mf_qty  = $data[3];

			$mdb2->query("INSERT INTO data_tmp(data0, data1, data2, data3) VALUES(
						".$mdb2->quote($mf_date, 'Text').",
						".$mdb2->quote($mf_no, 'Text').",
						".$mdb2->quote($mf_file, 'Text').",
						".$mdb2->quote($mf_qty, 'Text').")");

		}

	}

	print('<hr style="width: 100%; height: 2px;">');

	print('<table border="1" align=center width="95%">');
	print('<caption>');
	print('<div align="center"><font size="4" color="#0066cc"><b>');
	print('使用ファイル リスト [ ');
	print($csv_file);
	print(' ]');
	print('<br>');
	print('<a href="excel/excel_out11.php?csv_file=');
	print($csv_file);
	print('">');
	print('<img src="../graphics/seisan_excel_out.png" border="0"></a>');
	print('</b></font></div>');
	print('</caption>');

	print('<tr bgcolor="#cccccc">');
	print('<th>生産日</th>');
	print('<th>工番</th>');
	print('<th>Unit ID</th>');
	print('<th>プログラム名</th>');
	print('<th>品名</th>');
	print('<th>数量</th>');
	print('</tr>');


	$res_query = $mdb2->query("SELECT * FROM data_tmp ORDER BY tmp_id");
	$res_query = err_check($res_query);

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		$mf_date = $row['data0'];
		$mf_no   = $row['data1'];
		$mf_file = $row['data2'];
		$mf_qty  = $row['data3'];

		$res_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$mf_file'");
		if (PEAR::isError($res_row)) {
		} elseif ($res_row[0]!=NULL) {
			$unit_id = $res_row[0];
			$board   = $res_row[3];
		}

		if ($mf_file!=NULL ) {

			$td_set = "<td bgcolor=#f8f8ff>";

			print('<tr>');

			print($td_set);
			print($mf_date);
			print('</td>');

			print($td_set);
			print($mf_no);
			print('</td>');

			print($td_set);
			print($unit_id);
			print('</td>');

			print($td_set);
			print($mf_file);
			print('</td>');

			print($td_set);
			if ($board!=NULL) {
				print($board);
			} else {
				print('<br>');
			}
			print('</td>');

			print($td_set);
			print($mf_qty);
			print('</td>');

			print('</tr>');

		}

	}

	// セッション保存
	$_SESSION["mode_disp1"] = 'search';

	// DB切断
	db_disconnect($mdb2);

	break;


case "edit":

	break;

}

?>
</body>
</html>
