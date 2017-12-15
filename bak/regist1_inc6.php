<?php
//-------------------------------------------------------------------
// regist1_inc6.php
// ストック部品在庫データ登録
//
// 2008/03/21
// 2008/03/24 ディレクトリ修正
//
//-------------------------------------------------------------------

$csv_file = "../data/stock/qty/" . $txt_file;

$fp = fopen($csv_file, "r");

// FS部品 在庫管理[fs_stock]へ格納
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除
	for ($i=0; $i<=9; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$smt_id    = (int)$data[0];
//	$rack_old  = $data[1];
//	$product   = $data[2];
//	$solder    = solder_no($data[3]);
//	$p_maker   = $data[4];
//	$p_sub     = $data[5];
	$stk_reel  = (int)$data[6];
	$stk_qty   = (int)$data[7];
	$up_date   = $data[8];
	$rem_stock = $data[9];

	$res = $mdb2->queryRow("SELECT * FROM fs_stock WHERE smt_id='$smt_id'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]==NULL) {

		// 未登録 -> 追加登録
		$res = $mdb2->query("INSERT INTO fs_stock(smt_id, stk_reel, stk_qty, up_date, rem_stock) VALUES(
						".$mdb2->quote($smt_id, 'Integer').",
						".$mdb2->quote($stk_reel, 'Integer').",
						".$mdb2->quote($stk_qty, 'Integer').",
						".$mdb2->quote($up_date, 'Date').",
						".$mdb2->quote($rem_stock, 'Text').")");

	} else {

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// 登録済み -> データ更新
		$res = $mdb2->exec("UPDATE fs_stock SET stk_reel='$stk_reel' WHERE smt_id='$smt_id'");
		$res = $mdb2->exec("UPDATE fs_stock SET stk_qty='$stk_qty' WHERE smt_id='$smt_id'");
		$res = $mdb2->exec("UPDATE fs_stock SET up_date='$up_date' WHERE smt_id='$smt_id'");
		$res = $mdb2->exec("UPDATE fs_stock SET rem_stock='$rem_stock' WHERE smt_id='$smt_id'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

	}

}

// CSVファイルを閉じる
fclose($fp);

// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('ストック部品 在庫数データ入力一覧 作成日：');
print(date("Y-m-d H:i"));
print('<br>');
print('</b></font></div>');

print('<table border="1" align=center>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>在庫ID</th>');
print('<th>部品ID</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>リール数</th>');
print('<th>在庫数</th>');
print('<th>更新日</th>');
print('<th>備考</th>');

print('</tr>');

// [smt_id] 検索
// 2008/03/31 100行までの表示に制限(表示は参考程度なので)
$res_query = $mdb2->query("SELECT * FROM fs_stock ORDER BY stock_id LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);
$bgcolor = '#f8f8ff';
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id = $row['smt_id'];

	$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
	if (PEAR::isError($part_row)) {
	} else {
		$product = $part_row[2];
		$solder  = $part_row[3];
	}

	// 列ごとに色を変える
	if ($bgcolor=='#dcdcdc') {
		$bgcolor = '#f8f8ff';
	} elseif ($bgcolor=='#f8f8ff') {
		$bgcolor = '#dcdcdc';
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

	print($color_set);
	print($row['stock_id']);
	print('</td>');

	print($color_set);
	print($row['smt_id']);
	print('</td>');

	print($color_set);
	print($product);
	print('</td>');

	print($color_set);
	print(solder_name($solder));
	print('</td>');

	print($color_set);
	if ($row['stk_reel']!=NULL) {
		print($row['stk_reel']);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($row['stk_qty']!=NULL) {
		print($row['stk_qty']);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	print($row['up_date']);
	print('</td>');

	print($color_set);
	if ($row['rem_stock']!=NULL) {
		print($row['rem_stock']);
	} else {
		print('<br>');
	}
	print('</td>');

	print('</tr>');

}

print('</table></td>');

// 変数破棄
//unset($reel_no);
//unset($set_cnt);

?>
