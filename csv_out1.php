<?php
/*
[生産管理システム]

 *生産実績出力(CSV出力)
  SMT修正完のオーダー情報を年月指定でCSV出力

  2006/10/06 ローカルではOKだが、サーバではゲストは書込み権限を制限しているのでダメ
             違う方法を検討

 */

//---------------------------------------------------------------
// 初期設定
//---------------------------------------------------------------
// 共有関数
include_once 'lib/com_func1.php';

// ライブラリ
include_once 'HTML/QuickForm.php';
include_once 'MDB2.php';

//---------------------------------------------------------------

// 表示年月の取得
$out_date = $_GET["result"];
$search_para = $out_date . '%';

// ファイルオープン(追記型："a")
$csv_name = "SMT_" . "RESULT_" . $out_date . ".csv";
@$file = fopen($csv_name, "a") or die("ファイル　オープン　エラー");

// ファイルロック(排他的ロック：LOCK_EX)
flock($file, LOCK_EX);

// DB接続
$db = db_connect();

$sql_select = "SELECT * FROM planning T3";
$sql_join1 = " JOIN order_info T2 ON(T3.order_id=T2.order_id)";
$sql_join2 = " JOIN product T1 ON(T2.product_id=T1.product_id)";
$sql_where = " WHERE smt_fix like '$search_para'";
$sql_order = " ORDER BY plan, operate_no, dash_no, eqp, product, mfd, ass_side";
$search_sql = $sql_select . $sql_join1 . $sql_join2 . $sql_where . $sql_order;

if ($search_sql != NULL) {
	$rs = $db->query($search_sql);

	// エラー処理
	$db_err = db_err_chk($db, $rs);
}

while($row=$rs->fetchRow(DB_FETCHMODE_ASSOC)) {


	// [YYYY-MM-DD HH:MM:SS] -> [YYMMDD]
	$yy = substr($row['smt_fix'], 2, 2);
	$mm = substr($row['smt_fix'], 5, 2);
	$dd = substr($row['smt_fix'], 8, 2);
	$yymmdd = $yy . $mm . $dd;

	// 工番＋ロット -> 工番
	$ope_no = substr($row['operate_no'], 0, 4);

	// 工番＋ロット -> ロット
	$lot_no = substr($row['operate_no'], 5, 2);

	// はんだ(PBF) -> N / はんだ(共晶) -> null
	switch ($row['solder']) {
	case '0':
		$sol_pbf = '';
		break;
	case '1':
		$sol_pbf = '';
		break;
	case '2':
		$sol_pbf = 'N';
		break;
	}

	$t5[0] = $ope_no;
	$t5[1] = $lot_no;
	$t5[2] = $row['eqp'];
	$t5[3] = $row['product'];

	// 実装面単位で検索されるのでオーダー単位へ
	if ($t5 != $t6) {

		// データ書き出し
		$data[0] = "\"" . $row['eqp'] . "\",\"" . $row['product'] . "\",\"" .  $row['client_id'];
		$data[1] = "\",\"" . $sol_pbf . "\",\"" . $ope_no . "\",\"" . $lot_no;
	   	$data[2] = "\",\"" . $row['dash_no'] . "\",\"" . $row['qty30'] . "\",\"" . $yymmdd . "\"";
		$w_data = $data[0] . $data[1] . $data[2];
		fputs($file, $w_data."\n");

	}

	$t6[0] = $ope_no;
	$t6[1] = $lot_no;
	$t6[2] = $row['eqp'];
	$t6[3] = $row['product'];

}

// ファイルロック解除(ロック解除：LOCK_UN)
flock($file, LOCK_UN);

// ファイルクローズ
fclose($file);

// HTTPヘッダー出力
$len = filesize($csv_name);
header("Content-Disposition: inline ; filename=$csv_name");
header("Content-type: text/octet-stream" );
header("Content-Length: ". $len );

// ファイル出力(文字コード変換含む)
$kanji_code = mb_internal_encoding();	// 現在の内部文字コードをキープ
mb_http_output("SJIS");					// HTTP文字コードをSJISに明示的に設定
readfile($csv_name);					// ファイルを読み込んでHTTP出力
mb_internal_encoding($kanji_code);		// 内部文字コードを元に戻す

// テンポラリファイルを削除
unlink ($csv_name);


// 配列開放
unset($t5);
unset($t6);
unset($data);

// DB切断
db_disconnect($db);

?>
