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

 *一時処理用 いろいろ

  2015/09/10
  ローカル開発環境でExcel出力NGなので動作確認用に試行錯誤

  2015/09/14
  やっとExcel出力NGの原因判明 PHP5.4から関数への参照渡しが廃止されたのが原因

  Spreadsheet_Excel_Writer の Worksheet.php
  OLE の Root.php

  を少し修正してとりあえずエラーは出なくなった

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

// PEARライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';
require_once 'Spreadsheet/Excel/Writer.php';

//---------------------------------------------------------------


//print('<pre>');
//Var_Dump("OK");
//print('</pre>');


sampleCsv();


function sampleCsv() {

	try {

		//CSV形式で情報をファイルに出力のための準備
		$csvFileName = '/tmp/' . time() . rand() . '.csv';
		$res = fopen($csvFileName, 'w');
		if ($res === FALSE) {
			throw new Exception('ファイルの書き込みに失敗しました。');
		}

		// データ一覧。この部分を引数とか動的に渡すようにしましょう
		$dataList = array(
				array('hogehoge','mogemoge','mokomoko','aaa'),
				array('ddd','sss','eeeeee','ffff'),
		);

		// ループしながら出力
		foreach($dataList as $dataInfo) {

			// 文字コード変換。エクセルで開けるようにする
			mb_convert_variables('SJIS', 'UTF-8', $dataInfo);

			// ファイルに書き出しをする
			fputcsv($res, $dataInfo);
		}

		// ハンドル閉じる
		fclose($res);

		// ダウンロード開始
		header('Content-Type: application/octet-stream');

		// ここで渡されるファイルがダウンロード時のファイル名になる
		header('Content-Disposition: attachment; filename=sampaleCsv.csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($csvFileName));
		readfile($csvFileName);

	} catch(Exception $e) {

		// 例外処理をここに書きます
		echo $e->getMessage();

	}
}


/*
$wBook = new Spreadsheet_Excel_Writer('test-hoge.xls');
$wSheet =& $workbook->addWorksheet('test contents');
if (PEAR::isError($wSheet)) {
	die($wSheet->getMessage());
}
$wBook->close();
*/

/*
// ファイルへのパスをここで指定します
$workbook = new Spreadsheet_Excel_Writer('test.xls');

$worksheet =& $workbook->addWorksheet('My first worksheet');

$worksheet->write(0, 0, 'Name');
$worksheet->write(0, 1, 'Age');
$worksheet->write(1, 0, 'John Smith');
$worksheet->write(1, 1, 30);
$worksheet->write(2, 0, 'Johann Schmidt');
$worksheet->write(2, 1, 31);
$worksheet->write(3, 0, 'Juan Herrera');
$worksheet->write(3, 1, 32);

// この場合でも、ワークブックを明示的に閉じる必要があります
$workbook->send('test.xls');
$workbook->close();
*/

/*
// Excelブック生成
$book_name = "Excel_Outout_Test_20150910";
$book_name = "$book_name".".xls";
$workbook = new Spreadsheet_Excel_Writer();

// Excelブック出力
$workbook->send("$book_name");
$workbook->close();
*/


/*
// 2007/11/14 生産計画[planning]の生産数量｢0｣対応
// これまでのデータを一括修正する為に使用(1回のみ)
// 実績数[qty30]が入っていれば実績数で生産数を更新
// 実績数が入っていない場合はオーダー情報[order_info]の数量で生産数を更新
//
// 原因は元からある問題でASSが終わり残数が[0]となった状態で大日程編集で編集を
// 行うと残数が[0]で登録＝生産数量も[0]となってしまう
// 残数が[0]だった場合は、生産計画の生産数は保持して残数のみ[0]で登録する
// ように修正

// DB接続
$mdb2 = db_connect();

for ($plan_id=1; $plan_id<=30000; $plan_id++) {

	$res = $mdb2->queryRow("SELECT * FROM planning WHERE plan_id='$plan_id'");
	if (PEAR::isError($res)) {
	} else {

		$order_id = $res[1];
		$qty30 = $res[13];

		if ($qty30!=NULL and $qty30!=0) {

			$res = $mdb2->exec("UPDATE planning SET qty20='$qty30' WHERE plan_id='$plan_id'");

		} else {

			$res_tmp = $mdb2->queryRow("SELECT * FROM order_info WHERE order_id='$order_id'");
			if (PEAR::isError($res_tmp)) {
			} else {


				$qty10 = $res[4];
				$res = $mdb2->exec("UPDATE planning SET qty20='$qty10' WHERE plan_id='$plan_id'");

			}
		}

	}

}

// DB切断
db_disconnect($mdb2);
//-------------------------------------------------------------------------
*/

// 2007/12/10 [part_smt]テーブルのリールサイズ、高額品を一括整形
// これまで元データの文字列をそのまま登録していたが数字で管理した方がミスが出にくい
// と思うので一括修正
// リールサイズ 通常 -> 0 現在はNULL
//              小   -> 1 現在は"小"
//              大   -> 2 現在は1
//
// 高額品 通常品 -> 0 現在はNULL
//        高額品 -> 1 現在は"高額"

/*
// DB接続
$mdb2 = db_connect();

//$mdb2 -> query("UPDATE part_smt SET r_size=0 WHERE r_size=NULL");
//$mdb2 -> query("UPDATE part_smt SET r_size=1 WHERE r_size='大'");
//
//$mdb2 -> query("UPDATE part_smt SET exp_item=0 WHERE exp_item=NULL");
//$mdb2 -> query("UPDATE part_smt SET exp_item=1 WHERE exp_item='高額'");

$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY smt_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id = $row['smt_id'];

	$r_size = $row['r_size'];
	if ($r_size==1) {
		$r_size = 2;
	} elseif ($r_size=='小') {
		$r_size = 1;
	}

	$exp_item = $row['exp_item'];
	if ($exp_item==NULL) {
		$exp_item = 0;
	}

	$mdb2 -> query("UPDATE part_smt SET r_size='$r_size' WHERE smt_id='$smt_id'");
	$mdb2 -> query("UPDATE part_smt SET exp_item='$exp_item' WHERE smt_id='$smt_id'");

}

// DB切断
db_disconnect($mdb2);
*/

//-------------------------------------------------------------------------
// 2008/03/22 [part_smt]と[fs_stock]の整合
//
/*
// DB接続
$mdb2 = db_connect();

$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY smt_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id = $row['smt_id'];

	$res_row = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id'");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]==NULL) {

print('<pre>');
Var_Dump($smt_id);
print('</pre>');

		$res = $mdb2->query("INSERT INTO fs_stock(smt_id, stk_reel, stk_qty, up_date, rem_stock) VALUES(
							".$mdb2->quote($smt_id, 'Integer').",
							".$mdb2->quote(0, 'Integer').",
							".$mdb2->quote(0, 'Integer').",
							".$mdb2->quote(date('Y-m-d'), 'Date').",
							".$mdb2->quote('新規仮登録', 'Text').")");

	}

}

// DB切断
db_disconnect($mdb2);
*/

//-------------------------------------------------------------------------
// 2009/02/12 [part_smt]と[stock_smt]の整合
//
/*
// DB接続
$mdb2 = db_connect();

$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY smt_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id = $row['smt_id'];

	$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id'");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]==NULL) {

print('<pre>');
Var_Dump($smt_id);
print('</pre>');

		$res = $mdb2->query("INSERT INTO stock_smt(smt_id, qty, up_date, rem_stock) VALUES(
							".$mdb2->quote($smt_id, 'Integer').",
							".$mdb2->quote(0, 'Integer').",
							".$mdb2->quote('2009-02-03', 'Date').",
							".$mdb2->quote('ダミー登録', 'Text').")");

	}

}

// DB切断
db_disconnect($mdb2);
*/

?>

</body>
</html>
