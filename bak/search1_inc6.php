<?php
//-------------------------------------------------------------------
// search1_inc6.php
// 補充品 在庫数一覧 (Excel出力)
// $search_no = 6
//
// 2008/07/17
//
//-------------------------------------------------------------------

print('<table border="1" align=center>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('補充品 在庫数一覧 作成日： ');
print(date("Y-m-d H:i"));
print('<br>');
print('<a href="excel/excel_out7.php" target="result">');
print('<img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');

print('<tr bgcolor="#cccccc">');
print('<th>現棚番</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>メーカー品名</th>');
print('<th>リール数</th>');
print('<th>在庫数</th>');
print('<th>更新日</th>');
print('<th>備考</th>');
print('</tr>');

// DB接続
$mdb2 = db_connect();

$bgcolor = '#dcdcdc';

// 2008/07/17 確認用なので出力件数を100件までに制限
$res_query = $mdb2->query("SELECT * FROM fs_stock ORDER BY smt_id LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);

$bgcolor = '#f8f8ff';

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id    = $row['smt_id'];
	$stk_reel  = $row['stk_reel'];
	$stk_qty   = $row['stk_qty'];
	$up_date   = $row['up_date'];
	$rem_stock = $row['rem_stock'];

	$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]!=NULL) {

		$rack_old = $res_row[10];
		$product  = $res_row[2];
		$solder   = $res_row[3];
		$p_maker  = $res_row[5];

	}

	// 列ごとに色を変える
	if ($bgcolor=='#f8f8ff') {
		$bgcolor = '#dcdcdc';
	} elseif ($bgcolor=='#dcdcdc') {
		$bgcolor = '#f8f8ff';
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

	print($color_set);
	print($rack_old);
	print('</td>');

	print($color_set);
	print($product);
	print('</td>');

	print($color_set);
	print(solder_name($solder));
	print('</td>');

	print($color_set);
	print($p_maker);
	print('</td>');

	print($color_set);
	if ($stk_reel!=NULL) {
		print($stk_reel);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($stk_qty!=NULL) {
		print($stk_qty);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	print($up_date);
	print('</td>');

	print($color_set);
	if ($rem_stock!=NULL) {
		print($rem_stock);
	} else {
		print('<br>');
	}
	print('</td>');

	print('</tr>');

}

// DB切断
db_disconnect($mdb2);


?>
