<?php
//-------------------------------------------------------------------
// regist1_inc6.php
// ストック部品在庫データ(現場版)で更新
//
// 2008/04/03
// 2008/07/07
//
//-------------------------------------------------------------------

$csv_file = "../data/stock/update/" . $txt_file;

$fp = fopen($csv_file, "r");

// 安斎H管理の在庫データ(現場データ)を在庫管理[fs_stock]へ取込み
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除と桁数確認
	for ($i=0; $i<=5; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$rack_old = $data[0];

	// 棚番の桁数確認(3桁の場合は頭に"0"追加)
	$data_len = strlen($rack_old);
	if ($data_len==3) {
		$rack_old = '0' . $rack_old;
	}

	$product  = $data[1];
	$solder   = solder_no($data[2]);
	$stk_reel = (int)$data[3];
	$stk_qty  = (int)$data[4];
	$up_date  = $data[5];

	// 棚番データではんだが「混在」の場合、現場データのはんだ種別で更新
	// この時に更新したはんだ種別を備考に追加
	$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old' AND solder='$solder'");
//	$res_row = $mdb2->queryRow("SELECT * FROM part_smt WHERE rack_old='$rack_old' ORDER BY smt_id");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]!=NULL) {

		$smt_id = $res_row[0];

		if ($solder==$res_row[3]) {

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			// データ更新
			$res = $mdb2->exec("UPDATE fs_stock SET stk_reel='$stk_reel' WHERE smt_id='$smt_id'");
			$res = $mdb2->exec("UPDATE fs_stock SET stk_qty='$stk_qty' WHERE smt_id='$smt_id'");

			if ($up_date!=NULL) {
				$res = $mdb2->exec("UPDATE fs_stock SET up_date='$up_date' WHERE smt_id='$smt_id'");
			}

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		} elseif ($res_row[3]==3) {

			if ($solder==1 or $solder==2) {

				if ($sold==1) {
					$rem_stock = '共晶';
				} elseif ($solder==2) {
					$rem_stock = 'RoHS';
				}

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// データ更新
				$res = $mdb2->exec("UPDATE fs_stock SET stk_reel='$stk_reel' WHERE smt_id='$smt_id'");
				$res = $mdb2->exec("UPDATE fs_stock SET stk_qty='$stk_qty' WHERE smt_id='$smt_id'");

				if ($up_date!=NULL) {
					$res = $mdb2->exec("UPDATE fs_stock SET up_date='$up_date' WHERE smt_id='$smt_id'");
				}

				$res = $mdb2->exec("UPDATE fs_stock SET rem_stock='$rem_stock' WHERE smt_id='$smt_id'");

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			}

		}

	}

}

// CSVファイルを閉じる
fclose($fp);

// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('FS部品 在庫数データ(現場版) 更新一覧 作成日：');
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
	if ($bgcolor == '#dcdcdc') {
		$bgcolor = '#f8f8ff';
	} elseif ($bgcolor == '#f8f8ff') {
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


?>
