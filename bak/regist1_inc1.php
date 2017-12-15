<?php
//-------------------------------------------------------------------
// regist1_inc1.php
// ユニットデータ(TXT)からデータ抽出 -> テーブルへ格納
//
// 2007/04/13
// 2007/06/07 PEAR::DB -> PEAR::MDB2 など
// 2007/07/10 登録処理修正(基板名が同じでファイル名違い(GRP違い)の場合あり)
// 2007/07/12 荷姿を英字から全角カナへ変換して登録
// 2007/08/20 1ステーションの部品登録数の最大値は40という事だったが実際には
//            70近い物もある 最大値を100とする
//
// 2008/10/08 データフォーマット変更 対応
//
//-------------------------------------------------------------------

$index[0] = 1;
$index[1] = 0;
//unset($refix_date);
$refix_date = '';

// マシンデータ(TXT) -> 配列
for ($cnt=0; $cnt<=$row_cnt; $cnt++) {

	$tmp_item = trim(substr($row[$cnt], 8, 12));

	if ($tmp_item=="基板ＩＤ") {
		$id = trim(substr($row[$cnt], 23, 40));
	}

	if ($tmp_item=="プログラム名") {

		// プログラム名取得
		$program_chk = trim(substr($row[$cnt], 23, 26));

		// ステーション名の重複確認
		$chk_index = $index[0] - 1;

		if ($st[$chk_index][0]!=$program_chk) {

			$st[$index[0]][0] = $program_chk;
			$index[0]++;

		}

		// 更新日を取得して[/]->[-]へ変換(1回だけ取得)
		if ($refix_date==NULL) {
			$refix_date = trim(substr($row[$cnt], 50, 16));
			$refix_date = str_replace("/", "-", $refix_date);
		}

	}

	if ($tmp_item=="ステーション") {

		// プログラム名取得で$index[0]が最後にインクリメントされた状態で終わるので補正
		$st[$index[0] - 1][1] = trim(substr($row[$cnt], 23, 26));

		/*
		// F側/R側読込み行設定(ステーション名から4行目で読込み開始)
		$row_set[0] = $cnt + 4;
		$row_set[1] = $cnt + 4;

		// F側読込み(1ステーションに最大40種類なので余裕を見て50までループ)
		// 2007/08/20 70近いファイルもある！ 100までループへ修正
		for ($index[1]=0; $index[1]<=100; $index[1]++) {

			// 最初の穴番号の文字位置で有効行の確認
			$chk = trim(substr($row[$row_set[0]], 7, 6));
			if ($chk!=NULL) {

				// 穴番号 - 部品名 - 荷姿
				$stp[$index[0]][$index[1]][0] = trim(substr($row[$row_set[0]], 7, 6));
				$stp[$index[0]][$index[1]][1] = trim(substr($row[$row_set[0]], 14, 21));
				$stp[$index[0]][$index[1]][2] = pack_name(trim(substr($row[$row_set[0]], 35, 6)));

				// F側の最終index+1で保持
				$index[2] = $index[1] + 1;

				// 行送り
				$row_set[0]++;

			}
		}

		// R側読込み(1ステーションに最大40種類なので余裕を見て50までループ))
		// 2007/08/20 70近いファイルもある！ 100までループへ修正
		// 登録indexはF側最終index保持から
		for ($index[1]=$index[2]; $index[1]<=100; $index[1]++) {

			// 最初の穴番号の文字位置で有効行の確認
			$chk = trim(substr($row[$row_set[1]], 47, 6));
			if ($chk!=NULL) {

				// 穴番号 - 部品名 - 荷姿
				$stp[$index[0]][$index[1]][0] = trim(substr($row[$row_set[1]], 47, 6));
				$stp[$index[0]][$index[1]][1] = trim(substr($row[$row_set[1]], 54, 21));
				$stp[$index[0]][$index[1]][2] = pack_name(trim(substr($row[$row_set[1]], 75, 6)));

				// 行送り
				$row_set[1]++;

			}
		}
		*/

		// 読込み行設定(ステーション名から3行目で読込み開始)
		$row_set = $cnt + 3;

		$set_index = $index[1];

		// 読込み(1ステーションに最大40種類のはずだが余裕を見て100までループ)
		for ($index[1]=0; $index[1]<=100; $index[1]++) {

			// 最初の穴番号の文字位置で有効行の確認
			$chk = trim(substr($row[$row_set], 7, 6));
			if ($chk!=NULL) {

				// 穴番号 - 部品名 - 荷姿
				$stp[$index[0]][$index[1]][0] = trim(substr($row[$row_set], 7, 6));
				$stp[$index[0]][$index[1]][1] = trim(substr($row[$row_set], 14, 21));
				$stp[$index[0]][$index[1]][2] = pack_name(trim(substr($row[$row_set], 66, 6)));

print('<pre>');
var_dump($stp[$index[0]][$index[1]][0]);
var_dump($stp[$index[0]][$index[1]][1]);
var_dump($stp[$index[0]][$index[1]][2]);
print('</pre>');


				// F側の最終index+1で保持
				$index[2] = $index[1] + 1;

				// 行送り
				$row_set++;

			}

			$chk_index = $index[1];

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

// 配列へ格納
//$data[0] = array(0=>'0', 1=>'ID', 2=>$id, 3=>$unit_index[0], 4=>$refix_date);
//
//for ($cnt=1; $cnt<=5; $cnt++) {
//
//	if ($st[$cnt][0]!=NULL) {
//
//		$data[] = array(0=>$cnt, 1=>'ST', 2=>$st[$cnt][0], 3=>$st[$cnt][1]);
//
//		for ($i=0; $i<=50; $i++) {
//
//			if ($stp[$cnt][$i][0]!=NULL) {
//				$data[] = array(0=>$cnt, 1=>'STP', 2=>$stp[$cnt][$i][0], 3=>$stp[$cnt][$i][1], 4=>$stp[$cnt][$i][2]);
//			}
//		}
//	}
//}

// 配列開放
unset($index);
unset($row_set);
unset($id);
unset($st);
unset($stp);

// [unit_data][station_data][set_data] へ格納


/*
// [unit_data] 基板名登録
$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0='0' ORDER BY tmp_id");
$res_query = err_check($res_query);

while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$file_name = $row_tmp['data2'];
	$board = $row_tmp['data3'];
	$u_index = $row_tmp['data4'];
	$refix_date = $row_tmp['data5'];

	// [unit_id] 検索
	// 2007/07/10 基板名が同じでファイル名違い(GRP違い)の場合あり
//	$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' OR board='$board' AND u_index=$u_index");
	$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' AND u_index=$u_index");
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
		$res = $mdb2->queryRow("SELECT * FROM unit_data WHERE file_name='$file_name' AND u_index=$u_index");
		if (PEAR::isError($res)) {
		} else {
			$unit_id = $res[0];
		}

	}

}

// [st_id] 検索(未登録/登録済みの検出と登録ステーション数のカウント)
$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id'");
$res_query = err_check($res_query);

unset($st_id);
$i = 0;
while($row_tmp = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	if ($row['st_id']!=NULL) {
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
		$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0=$set+1 and data1='STP' ORDER BY tmp_id");
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
		$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0=$set+1 and data1='STP' ORDER BY tmp_id");
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
			$res = $mdb2->exec("UPDATE set_data SET hole_no='$str3[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");
			$res = $mdb2->exec("UPDATE set_data SET part='$str4[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");
			$res = $mdb2->exec("UPDATE set_data SET packing='$str5[$set2]' WHERE st_id='$st_id[$set]' AND set_index='$set2'");
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

*/


?>
