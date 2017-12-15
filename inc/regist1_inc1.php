<?php
//-------------------------------------------------------------------
// regist1_inc1.php
// ユニットデータ(TXT)からデータ抽出 -> テーブルへ格納
//
// 2007-04-13
// 2007-06-07 PEAR::DB -> PEAR::MDB2 など
// 2007-07-10 登録処理修正(基板名が同じでファイル名違い(GRP違い)の場合あり)
// 2007-07-12 荷姿を英字から全角カナへ変換して登録
// 2007-08-20 1ステーションの部品登録数の最大値は40という事だったが実際には
//            70近い物もある 最大値を100とする
//
// 2008-10-08 データフォーマット変更 対応
// 2009-02-10 本格的に運用を開始してみたら結構データ更新がある為、登録済み
//            の場合は[unit_id]は保持して[station_data],[set_data],[qty_data]
//            データは一旦削除後、再登録で対応
//            [unit_data]の基板IDやプログラム名が更新された場合は別ユニットが
//            追加されたという事で単純に[unit_data]からの追加処理
//
// 2009-02-13 [unit_data] 更新日の更新 追加
// 2009-03-17 [unit_data] 確認状況のクリア追加
// 2009-03-19 $set_indexがNULLになる場合があり修正
// 2009-08-19 [unit_data] 基板IDの更新 追加
//
// 2013-02-12 HLC VerUpによるデータフォーマット変更に対応(プログラム名などの位置が変更)
//
//-------------------------------------------------------------------

// 初期化
$index[0] = 1;
$index[1] = 0;
unset($tmp_item);
unset($id);
unset($program_chk);
unset($chk_index);
unset($refix_date);
unset($st);
unset($stp);
unset($chk_station);

// マシンデータ(TXT) -> 配列
for ($cnt=0; $cnt<=$row_cnt; $cnt++) {

	$tmp_item = trim(substr($row[$cnt], 8, 12));

	if ($tmp_item=="基板ＩＤ") {
		//$id = trim(substr($row[$cnt], 23, 40));
		$id = trim(substr($row[$cnt], 23, 50));
	}

	if ($tmp_item=="プログラム名") {

		// プログラム名取得
		//$program_chk = trim(substr($row[$cnt], 23, 27));
		$program_chk = trim(substr($row[$cnt], 23, 50));

		// ステーション名の重複確認
		$chk_index = $index[0] - 1;

		if ($st[$chk_index][0]!=$program_chk) {
			$st[$index[0]][0] = $program_chk;
			$index[0]++;
		}

		// 更新日を取得して[/]->[-]へ変換(1回だけ取得)
		if ($refix_date==NULL) {
			//$refix_date = trim(substr($row[$cnt], 50, 16));
			$refix_date = trim(substr($row[$cnt], 73, 16));
			$refix_date = str_replace("/", "-", $refix_date);
		}

	}

	if ($tmp_item=="ステーション") {

		// プログラム名取得で$index[0]が最後にインクリメントされた状態で終わるので補正
		//$chk_station[0] = trim(substr($row[$cnt], 23, 27));
		$chk_station[0] = trim(substr($row[$cnt], 23, 50));
		$st[$index[0]-1][1] = $chk_station[0];

		// 読込み行設定(ステーション名から3行目で読込み開始)
		$row_set = $cnt + 3;

		// 部品インデックスの調整(同じステーションの場合は連番)
		// 2009/03/19 $set_indexがNULLになる場合があり修正
		if ($chk_station[0]==$chk_station[1]) {
			if ($index[2]!=NULL) {
				$set_index = $index[2];
			} else {
				$set_index = 0;
			}
		} else {
			$set_index = 0;
		}

		// 読込み(1ステーションに最大40種類のはずだが余裕を見て100までループ)
		for ($index[1]=$set_index; $index[1]<=100; $index[1]++) {

			// 最初の穴番号の文字位置で有効行の確認
			$chk = trim(substr($row[$row_set], 7, 6));

			if ($chk!=NULL) {

				// 穴番号 - 部品名 - 荷姿
				$stp[$index[0]-1][$index[1]][0] = trim(substr($row[$row_set], 7, 6));
				$stp[$index[0]-1][$index[1]][1] = trim(substr($row[$row_set], 14, 21));
				$stp[$index[0]-1][$index[1]][2] = pack_name(trim(substr($row[$row_set], 66, 6)));

				// 最終index+1で保持
				$index[2] = $index[1] + 1;

				// 行送り
				$row_set++;

			}

			$chk_station[1] = $chk_station[0];

		}

	}

}

// 作業用一時テーブルクリア
$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

// 作業用一時テーブルへ格納
$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4, data5) VALUES(
					".$mdb2->quote('0', 'Text').",
					".$mdb2->quote('ID', 'Text').",
					".$mdb2->quote($unit_file[$cnt_loop], 'Text').",
					".$mdb2->quote($id, 'Text').",
					".$mdb2->quote($unit_index[$cnt_loop], 'Text').",
					".$mdb2->quote($refix_date, 'Text').")");

for ($cnt=1; $cnt<=5; $cnt++) {
	if ($st[$cnt][0]!=NULL) {
		$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3) VALUES(
							".$mdb2->quote($cnt, 'Text').",
							".$mdb2->quote('ST', 'Text').",
							".$mdb2->quote($st[$cnt][0], 'Text').",
							".$mdb2->quote($st[$cnt][1], 'Text').")");

		for ($i=0; $i<=100; $i++) {
			if ($stp[$cnt][$i][0]!=NULL) {
				$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4) VALUES(
									".$mdb2->quote($cnt, 'Text').",
									".$mdb2->quote('STP', 'Text').",
									".$mdb2->quote($stp[$cnt][$i][0], 'Text').",
									".$mdb2->quote($stp[$cnt][$i][1], 'Text').",
									".$mdb2->quote($stp[$cnt][$i][2], 'Text').")");
			}
		}

	}

}

// 配列開放
unset($index);
unset($row_set);
unset($id);
unset($st);
unset($stp);
unset($chk_index);
unset($chk_station);

// [unit_data][station_data][set_data] へ格納

// [unit_data] 基板名登録
$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='0' ORDER BY tmp_id");
$res_query = err_check($res_query);

while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$file_name  = $row_tmp['data2'];
	$board      = $row_tmp['data3'];
	$u_index    = $row_tmp['data4'];
	$refix_date = $row_tmp['data5'];

	// [unit_id] 検索
	// 2007/07/10 基板名が同じでファイル名違い(GRP違い)の場合あり
//	$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' OR board='$board' AND u_index=$u_index");
	$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' AND u_index='$u_index'");
	if (PEAR::isError($res)) {
	} else {
		$unit_id = $res[0];
	}

	if ($unit_id==NULL) {

		// 新規登録
		$res = $mdb2->exec("INSERT INTO unit_data(file_name, board, u_index, refix_date) VALUES(
							".$mdb2->quote($file_name, 'Text').",
							".$mdb2->quote($board, 'Text').",
							".$mdb2->quote($u_index, 'Integer').",
							".$mdb2->quote($refix_date, 'Timestamp').")");

		// [unit_id] 再検索
		// 2007/07/10 基板名が同じでファイル名違い(GRP違い)の場合あり
//		$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' OR board='$board' AND u_index=$u_index");
		$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' AND u_index='$u_index'");
		if (PEAR::isError($res)) {
		} else {
			$unit_id = $res[0];
		}

	} else {

		// 2009/02/10 登録済みの場合、一旦[unit_data]以外を削除してから再登録
		// 2009/02/13 [unit_data] 更新日の更新 追加
		// 2009/03/17 [unit_data] 確認状況のクリア追加
		// 2009/08/19 [unit_data] 基板IDの更新 追加
		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// [unit_data] 基板IDの更新
		$res = $mdb2->exec("UPDATE unit_data SET board='$board' WHERE unit_id='$unit_id'");

		// [unit_data] 更新日の更新
		$res = $mdb2->exec("UPDATE unit_data SET refix_date='$refix_date' WHERE unit_id='$unit_id'");

		// [unit_data] 確認状況のクリア(確認日[chk_date]はdate型なのでそのまま)
		$res = $mdb2->exec("UPDATE unit_data SET chk_status='' WHERE unit_id='$unit_id'");

		// 該当するステーションデータと数量データの削除
		// (セットデータはステーションデータの削除で一緒に削除される)
		$res = $mdb2->exec("DELETE FROM station_data WHERE unit_id='$unit_id'");
		$res = $mdb2->exec("DELETE FROM qty_data WHERE unit_id='$unit_id'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

	}

}

// 2009/02/10 以降、登録済みの場合は無くなるが更新処理もそのまま一応残しておく

// [st_id] 検索(未登録/登録済みの検出と登録ステーション数のカウント)
$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id'");
$res_query = err_check($res_query);

unset($st_id);
$i = 0;
while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	if ($row_tmp['st_id']!=NULL) {
		$st_id[$i] = $row_tmp['st_id'];
		$i++;
	} else {
		break;
	}

}

// 未登録   -> [station_data] [set_data]登録追加
// 登録済み -> [station_data]保持 [set_data]更新
if ($st_id==NULL) {

	// [station_data] プログラム名、ステーション名 取得
	$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0>='1' AND data0<='5' AND data1='ST' ORDER BY tmp_id");
	$res_query = err_check($res_query);

	$i = 0;
	while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$str1[$i] = $row_tmp['data2'];
		$str2[$i] = $row_tmp['data3'];
		$i++;
	}

	// ステーション数 取得
	$cnt_st = count($str1) - 1;

	// [station_data]登録処理内で[set_data]登録を行う
	for ($set=0; $set<=$cnt_st; $set++) {

		$res = $mdb2->exec("INSERT INTO station_data(unit_id, st_index, p_name, s_name) VALUES(
							".$mdb2->quote($unit_id, 'Integer').",
							".$mdb2->quote($set, 'Integer').",
							".$mdb2->quote($str1[$set], 'Text').",
							".$mdb2->quote($str2[$set], 'Text').")");

		// [set_data] 登録
		$set_tmp = (string)($set + 1);
		$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='$set_tmp' and data1='STP' ORDER BY tmp_id");
		$res_query = err_check($res_query);

		$set1 = 0;
		while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$str3[$set1] = $row_tmp['data2'];
			$str4[$set1] = $row_tmp['data3'];
			$str5[$set1] = $row_tmp['data4'];
			$set1++;
		}

		// 部品データ数取得
		$cnt_set = count($str3) - 1;

		// [st_id] 検索
		$res = $mdb2->queryRow("SELECT * FROM station_data WHERE st_index='$set' AND p_name='$str1[$set]'");
		if (PEAR::isError($res)) {
		} elseif ($res[0]!=NULL) {
			$st_id = $res[0];
		}

		for ($set2=0; $set2<=$cnt_set; $set2++) {
			$res = $mdb2->exec("INSERT INTO set_data(st_id, set_index, hole_no, part, packing) VALUES(
								".$mdb2->quote($st_id, 'Integer').",
								".$mdb2->quote($set2, 'Integer').",
								".$mdb2->quote($str3[$set2], 'Text').",
								".$mdb2->quote($str4[$set2], 'Text').",
								".$mdb2->quote($str5[$set2], 'Text').")");
		}

		// [set_data]変数初期化
		unset($str3);
		unset($str4);
		unset($str5);

	}

} else {

	// ステーション数 取得
	$cnt_st = count($st_id) - 1;

	// [station_data]登録処理内で[set_data]登録を行う
	for ($set=0; $set<=$cnt_st; $set++) {

		// [set_data] 登録
		$set_tmp = (string)($set + 1);
		$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='$set_tmp' and data1='STP' ORDER BY tmp_id");
		$res_query = err_check($res_query);

		$set1 = 0;
		while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$str3[$set1] = $row_tmp['data2'];
			$str4[$set1] = $row_tmp['data3'];
			$str5[$set1] = $row_tmp['data4'];
			$set1++;
		}

		// 部品データ数取得
		$cnt_set = count($str3) - 1;

		for ($set2=0; $set2<=$cnt_set; $set2++) {

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			$res = $mdb2->exec("UPDATE set_data SET hole_no='$str3[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");
			$res = $mdb2->exec("UPDATE set_data SET part='$str4[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");
			$res = $mdb2->exec("UPDATE set_data SET packing='$str5[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		}

		// [set_data]変数初期化
		unset($str3);
		unset($str4);
		unset($str5);
	}

}


// [set_data]変数開放
unset($str1);
unset($str2);
unset($str3);
unset($str4);
unset($str5);


?>
