<?php
//-------------------------------------------------------------------
// search1_inc7.php
// FS品 在庫数一覧 (Excel出力)
// $search_no = 7
//
// 2007/09/
// 2008/03/28 リール数の集計処理テスト
// 2008/07/04
// 2009/02/12 [fs_stock]廃止 [stock_smt]対応 棚外品の管理を止めシンプルに在庫数で管理
// 2009/02/14 一覧フォーマットを変更(表示項目追加)
// 2009/08/24 在庫備考 削除
// 2009/10/15 在庫数標示 修正
//
//-------------------------------------------------------------------

// ベンチマーク用
//$oTimer =& new Benchmark_Timer();
//$oTimer->start();

print('<table border="1" align=center width="95%">');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('SMT(福島ストック品) 在庫数一覧 作成日： ');
print(date("Y-m-d H:i"));
print('<br>');
print('＊2009-02-14 出力件数を100件に制限(Excel出力は全件出力します)');
print('<br>');
print('<a href="excel/excel_out4.php" target="result">');
print('<img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');

print('<tr bgcolor="#cccccc">');
//print('<th>ID</th>');
print('<th>現棚番</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>メーカー品名</th>');
print('<th>リール</th>');
print('<th>高額品</th>');
print('<th>部品備考</th>');
print('<th>棚番備考</th>');
print('<th>在庫数</th>');
print('<th>更新日</th>');
//print('<th>在庫備考</th>');
print('</tr>');

// DB接続
$mdb2 = db_connect();

$bgcolor = '#dcdcdc';

//$oTimer->setMarker('1');

// 2008/04/02 確認用なので出力件数を100件までに制限
//$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY rack_old LIMIT 100 OFFSET 1");
$res_query = $mdb2->query("SELECT * FROM part_smt ORDER BY rack_old LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);
$bgcolor = '#f8f8ff';

//$oTimer->setMarker('2');

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

//$oTimer->setMarker('3');

	$smt_id   = $row['smt_id'];
	$rack_old = $row['rack_old'];
	$product  = $row['product'];
	$solder   = $row['solder'];
	$p_maker  = $row['p_maker'];
	$r_size   = $row['r_size'];
	$exp_item = $row['exp_item'];
	$rem_part = $row['rem_part'];
	$rem_rack = $row['rem_rack'];

	// 在庫数 検索
	if ($smt_id!=NULL) {
		$res_row = $mdb2->queryRow("SELECT * FROM stock_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} elseif ($res_row[0]!=NULL) {
			$qty_stock = $res_row[3];
			$up_date   = $res_row[4];
			$rem_stock = $res_row[5];
		} else {
			unset($qty_stock);
			unset($up_date);
			unset($rem_stock);
		}
		unset($res_row);
	} else {
		unset($qty_stock);
		unset($up_date);
		unset($rem_stock);
	}

//$oTimer->setMarker('4');

/*
	// 登録リールの総数と使用数のカウント
	$product_tmp = $product . '%';
//	$reel_cnt = $mdb2->queryOne("SELECT COUNT(*) FROM reel_no WHERE product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp'");
//	$reel_cnt = err_check($reel_cnt);
//	$reel_cmp = $mdb2->queryOne("SELECT COUNT(*) FROM reel_no WHERE (product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp') AND end_date IS NOT NULL");
//	$reel_cmp = err_check($reel_cmp);

	$res_tmp = $mdb2->query("SELECT reel_id, end_date FROM reel_no WHERE product LIKE '$product_tmp' OR p_maker LIKE '$product_tmp' ORDER BY reel_id");
	$res_tmp = err_check($res_tmp);

	$cnt = 0;
	$cmp = 0;
	while($row_tmp = $res_tmp->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		// 登録全数
		$r_cnt[$cnt] = $row_tmp['reel_id'];
		$cnt++;

		// 使用完数
		if ($row_tmp['end_date']!=NULL) {
			$r_cmp[$cmp] = $row_tmp['reel_id'];
			$cmp++;
		}

	}

	$reel_cnt = count($r_cnt);
	$reel_cmp = count($r_cmp);
	unset($r_cnt);
	unset($r_cmp);
*/

//$oTimer->setMarker('5');

	// 列ごとに色を変える
	if ($bgcolor=='#f8f8ff') {
		$bgcolor = '#dcdcdc';
	} elseif ($bgcolor=='#dcdcdc') {
		$bgcolor = '#f8f8ff';
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

//	print($color_set);
//	print($smt_id);
//	print('</td>');

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
	if ($p_maker!=NULL) {
		print($p_maker);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($r_size!=NULL) {
		print(r_size_name($r_size));
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if (exp_item_name($exp_item)!=NULL) {
		print(exp_item_name($exp_item));
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($rem_part!=NULL) {
		print($rem_part);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($rem_rack!=NULL) {
		print($rem_rack);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($qty_stock!=NULL) {
		print($qty_stock);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($up_date!=NULL) {
		print($up_date);
	} else {
		print('<br>');
	}
	print('</td>');

//	print($color_set);
//	if ($rem_stock!=NULL) {
//		print($rem_stock);
//	} else {
//		print('<br>');
//	}
//	print('</td>');

	print('</tr>');

//$oTimer->setMarker('6');

}

// DB切断
db_disconnect($mdb2);

//$oTimer->stop();
//$oTimer->display();


?>
