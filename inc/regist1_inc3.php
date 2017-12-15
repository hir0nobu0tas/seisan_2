<?php
//-------------------------------------------------------------------
// regist1_inc3.php
// 更新データ(CSV)からデータ抽出 -> [set_data]テーブル更新
//
// 2007/08/23 実装開始
// 2007/11/29 放置していたが実装再開
// 2007/11/30 [set_data]に棚番選択[rack_sel] 追加
// 2009/03/05 放置していたが再開して動作テスト中(ほぼOK？)
// 2009/05/13 更新データフォーマット変更対応
//
// 2015/10/26 新棚番対応
//
//-------------------------------------------------------------------

$csv_count = count($txt_full) - 1;

for ($index=0; $index<=$csv_count; $index++) {

	$csv_file = $txt_full[$index];
	$fp = fopen($csv_file, "r");

	// 作業用一時テーブルクリア
	$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

	// 作業用一時テーブルへ格納
	while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

		// Trimによる前後スペース削除
		for ($i=0; $i<=11; $i++) {
			$data[$i] = Trim($data[$i]);
		}

		// 空白行の削除
		if ($data[0]!=NULL) {

			// 項目IDのみ拾って項目行は削除
			if ($data[0]=='UNIT_ID') {
				$data[12] = 'UNIT_ID';
			} elseif ($data[0]=='ST_ID') {
				$data[12] = 'ST_ID';
			} elseif ($data[0]=='SET_ID') {
				$data[12] = 'SET_ID';
			} else {

				// 項目ID Set
				$data[12] = $id_set;

				$res = $mdb2->query("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5, data6, data7, data8, data9, data10, data11, data12) VALUES(
								".$mdb2->quote($data[12], 'Text').",
								".$mdb2->quote($data[0], 'Text').",
								".$mdb2->quote($data[1], 'Text').",
								".$mdb2->quote($data[2], 'Text').",
								".$mdb2->quote($data[3], 'Text').",
								".$mdb2->quote($data[4], 'Text').",
								".$mdb2->quote($data[5], 'Text').",
								".$mdb2->quote($data[6], 'Text').",
								".$mdb2->quote($data[7], 'Text').",
								".$mdb2->quote($data[8], 'Text').",
								".$mdb2->quote($data[9], 'Text').",
								".$mdb2->quote($data[10], 'Text').",
								".$mdb2->quote($data[11], 'Text').")");

			}

			// 項目ID保持
			$id_set = $data[12];

		}

	}

	// CSVファイルを閉じる
	fclose($fp);

	// [unit_data] 更新
	$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='UNIT_ID' ORDER BY tmp_id");
	$res_query = err_check($res_query);

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		unset($unit_id);
		unset($chk_status);
		unset($chk_date);

		$unit_id       = $row['data1'];
		$chk_status[1] = $row['data6'];
		$chk_date      = $row['data7'];

		$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id'");
		if (PEAR::isError($res)) {
		} else {
			$chk_status[0] = $res[6];
		}

		// 2009/05/15 修正
//		if ($chk_status[0]!=$chk_status[1]) {
		if ($chk_status[1]!=NULL) {

//			// トランザクションブロック開始
//			$res = $mdb2->beginTransaction();

			$res = $mdb2->query("UPDATE unit_data SET chk_status='$chk_status[1]' WHERE unit_id='$unit_id'");
			$res = $mdb2->query("UPDATE unit_data SET chk_date='$chk_date' WHERE unit_id='$unit_id'");

//			// トランザクションブロック終了
//			$res = transaction_end($mdb2, $res);

		}

	}

	// [station_data] 更新
	$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='ST_ID' ORDER BY tmp_id");
	$res_query = err_check($res_query);

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		unset($st_id);
		unset($rem_st1);
		unset($rem_st2);
		unset($rem_st3);

		$st_id   = $row['data1'];
		$rem_st1 = $row['data7'];
		$rem_st2 = $row['data11'];
		$rem_st3 = $row['data12'];

		$res = $mdb2->query("UPDATE station_data SET rem_st1='$rem_st1' WHERE st_id='$st_id'");
		$res = $mdb2->query("UPDATE station_data SET rem_st2='$rem_st2' WHERE st_id='$st_id'");
		$res = $mdb2->query("UPDATE station_data SET rem_st3='$rem_st3' WHERE st_id='$st_id'");

	}

	// [set_data] 更新
	$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='SET_ID' ORDER BY tmp_id");
	$res_query = err_check($res_query);

	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

		unset($set_id);
		unset($rack_data);
		unset($data_sel);
		unset($rem_set1);
		unset($rem_set2);

		$set_id    = $row['data1'];
		$rack_data = $row['data8'];
		$data_sel  = $row['data10'];
		$rem_set1  = $row['data11'];
		$rem_set2  = $row['data12'];

//		// トランザクションブロック開始
//		$res = $mdb2->beginTransaction();

		$res = $mdb2->query("UPDATE set_data SET rack_data='$rack_data' WHERE set_id='$set_id'");
		$res = $mdb2->query("UPDATE set_data SET data_sel='$data_sel' WHERE set_id='$set_id'");
		$res = $mdb2->query("UPDATE set_data SET rem_set1='$rem_set1' WHERE set_id='$set_id'");
		$res = $mdb2->query("UPDATE set_data SET rem_set2='$rem_set2' WHERE set_id='$set_id'");

//		// トランザクションブロック終了
//		$res = transaction_end($mdb2, $res);

	}

}

?>
