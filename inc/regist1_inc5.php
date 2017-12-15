<?php
//-------------------------------------------------------------------
// regist1_inc5.php
// SMT部品在庫データ登録
//
// 2008-10-27
// 2009-02-
// 2009-09-08
// 2009-10-09 単価[unit_cost]を追加して登録処理を見直し * まだ未完成 *
// 2009-10-14 大体OK？
// 2010-07-01 実数確認実施で残数以外の棚番データ等も更新されたので対応 → 「はんだ」の更新
// 2011-01-07 CSV読み込みの配列数修正(5→6)
// 2015-11-05 新棚番対応
//
//-------------------------------------------------------------------

$csv_file = "../data/stock/qty/" . $txt_file;

$fp = fopen($csv_file, "r");

// [part_smt],[stock_smt]更新
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除
	for ($i=0; $i<=8; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$rack_no   = $data[0];
	$rack_old  = $data[1];
	$product   = $data[2];
	$p_nec     = $data[3];
	$solder    = $data[4];
	$unit_cost = $data[5];
	$qty_stock = $data[6];
	$up_date   = $data[7];
	$rem_stock = $data[8];

	if ($solder=='不明') {
		$solder_type = '0';
	} elseif ($solder=='共晶') {
		$solder_type = '1';
	} elseif ($solder=='RoHS' or $solder=='ROHS' ) {
		$solder_type = '2';
	} elseif ($solder=='混在') {
		$solder_type = '3';
	}


	// [smt_id] 検索
////$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_no' AND (product='$product1' OR product='$product2')");
//	$part_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_no'");
//	if (PEAR::isError($part_row)) {
//	} else {
//		$smt_id  = $part_row[0];
//		$rack_no = $part_row[10];
//	}

	// [smt_id] 検索(棚番が複数登録されている場合がある)
	if ($rack_no!=NULL) {
		$res_query = $mdb2->query("SELECT * FROM part_smt WHERE rack_no='$rack_no'");
		$res_query = err_check($res_query);
	} else {
		$res_query = $mdb2->query("SELECT * FROM part_smt WHERE rack_old='$rack_old'");
		$res_query = err_check($res_query);
	}

	// 配列として宣言後、添字の指定を省略すると自動的に要素が追加される(今更のTipsだが)
	$smt_id  = array();
	$rack_no = array();
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$smt_id[] = $row['smt_id'];
	}

	$id_cnt = count($smt_id);
	if ($id_cnt>0) {

		for ($set_cnt=1; $set_cnt<=$id_cnt; $set_cnt++) {

			// 在庫データ登録(更新)
			$id_tmp = $smt_id[$set_cnt - 1];

			// 2010-07-01 はんだの更新がある品種のみデータ更新
			// 0=不明, 1=共晶, 2=RoHS, 3=混在
			if ($solder!=NULL) {
				$res = $mdb2->exec("UPDATE part_smt SET solder='$solder_type' WHERE smt_id='$id_tmp'");
			}

			$res = $mdb2->exec("UPDATE stock_smt SET unit_cost='$unit_cost' WHERE smt_id='$id_tmp'");
			$res = $mdb2->exec("UPDATE stock_smt SET qty_stock='$qty_stock' WHERE smt_id='$id_tmp'");
			$res = $mdb2->exec("UPDATE stock_smt SET up_date='$up_date' WHERE smt_id='$id_tmp'");
			$res = $mdb2->exec("UPDATE stock_smt SET rem_stock='$rem_stock' WHERE smt_id='$id_tmp'");

			unset($id_tmp);

		}

	}

	unset($smt_id);
	unset($solder);
	unset($solder_type);
	unset($unit_cost);
	unset($qty_stock);
	unset($up_date);
	unset($rem_stock);

}

// CSVファイルを閉じる
fclose($fp);


// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('SMTストック部品 在庫一覧 作成日：');
print(date("Y-m-d H:i"));
print('<br>');
print('</b></font></div>');

print('<table border="1" align=center width="95%">');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>棚番</th>');
print('<th>品名</th>');
print('<th>はんだ</th>');
print('<th>単価</th>');
print('<th>在庫数</th>');
print('<th>更新日</th>');
print('<th>備考</th>');

print('</tr>');

// 2009/10/14 確認用なので出力件数を100件までに制限
$res_query = $mdb2->query("SELECT * FROM stock_smt ORDER BY stock_id LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$smt_id    = $row['smt_id'];
	$unit_cost = $row['unit_cost'];
	$qty_stock = $row['qty_stock'];
	$up_date   = $row['up_date'];
	$rem_stock = $row['rem_stock'];

	// 部品情報 検索
	if ($smt_id!=NULL) {
		$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE smt_id='$smt_id'");
		if (PEAR::isError($res_row)) {
		} elseif ($res_row[0]!=NULL) {
			$rack_old = $res_row[10];
			$product  = $res_row[2];
			$solder   = $res_row[3];
		} else {
			unset($rack_old);
			unset($product);
			unset($solder);
		}
		unset($res_row);
	} else {
		unset($rack_old);
		unset($product);
		unset($solder);
	}

	$bgcolor = '#f8f8ff';
	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

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
	if ($unit_cost==NULL) {
		print('<br>');
	} else {
		print($unit_cost);
	}
	print('</td>');

	print($color_set);
	if ($qty_stock==NULL) {
		print('<br>');
	} else {
		print($qty_stock);
	}
	print('</td>');

	print($color_set);
	if ($up_date==NULL) {
		print('<br>');
	} else {
		print($up_date);
	}
	print('</td>');

	print($color_set);
	if ($rem_stock==NULL) {
		print('<br>');
	} else {
		print($rem_stock);
	}
	print('</td>');

	print('</tr>');

}


?>
