<?php
//-------------------------------------------------------------------
// regist1_inc2.php
// 数量データ(TXT)からデータ抽出 -> テーブル格納
//
// 2007-04-
// 2007-06-07 PEAR::DB -> PEAR::MDB2 など
// 2007-07-12 荷姿を英字から全角カナへ変換して登録
// 2007-07-13 複数ページの読込み不具合修正
//            ファイル数が多いと途中で止まる現象(リソース不足？)の
//            暫定処置としてファイル数を100で区切って登録する事
// 2007-07-31 基板ID 入力無し対応
// 2007-08-20 部品登録で1ステーションに最大100と再設定したので対応
// 2008-12-09 数量データの初期化漏れで裏面の数量データのインポートNG
//            → 初期化を追加
// 2009-04-09 プログラム名取得の文字位置修正
//            (表、裏で最大のプログラム名の時正常に登録できなかった)
//
// 2010-09-15 プログラム名から[unit_id]の検索処理の修正
//            T7059A(LV58SER01)-A1 と T7059A(LV58SER01)試作-A1 をプログラム名をLIKE検索していた為、
//            別ファイルとして処理出来ていなかった
//            暫定処理でデータ更新実施 明日、処理を再検討
//
// 2010-09-16 昨日の暫定処理を見直し修正
//
// 2010-10-14 基板IDの取得文字数を修正
//            ユニットデータ登録処理は40文字としていたがこちらは修正していなかった
//            unit_id を取得出来ない場合は登録しないように修正
//
// 2013-02-12 HLC VerUpによるデータフォーマット変更に対応(プログラム名などの位置が変更)
//
//-------------------------------------------------------------------

// 2008/12/09 初期化追加
unset($id);
unset($part);

// 数量データ(TXT) -> 配列
for ($cnt=0; $cnt<=$row_cnt; $cnt++) {

	$tmp_item = trim(substr($row[$cnt], 8, 12));

	if ($tmp_item=="プログラム名" and $id[1]==NULL) {

		//$id[1] = trim(substr($row[$cnt], 23, 27));
		$id[1] = trim(substr($row[$cnt], 23, 50));

		// 更新日を取得して[/]->[-]へ変換(1回だけ取得)
		if ($id[2]==NULL) {
			//$id[2] = trim(substr($row[$cnt], 50, 16));
			$id[2] = trim(substr($row[$cnt], 73, 16));
			$id[2] = str_replace("/", "-", $id[2]);
		}

	}

	if ($tmp_item=="基板ＩＤ" and $id[0]==NULL) {

		// 基板ID確認
		// 2007/07/31 基板IDの入力が無い場合は仮に'dummy_name'とする
		// 2010/10/14 取得文字数を40文字に修正
		//$id[0] = trim(substr($row[$cnt], 23, 40));
		$id[0] = trim(substr($row[$cnt], 23, 50));
		if ($id[0]==NULL) {
			$id[0] = 'dummy_name';
		}

		// 読込み行設定(プログラム名から4行目で読込み開始)
		$row_set = $cnt + 4;

		// 部品情報読込み(1ステーションに最大40種×5ステーション = 200+)
//		for ($index=1; $index<=200; $index++) {
		// 2007/07/31 行数カウント値でループ処理 forループからwhileループへ
		$index = 1;
		while ($index<=($row_cnt - 9)) {

			// 最初の項目数の文字位置で有効行の確認
			$chk = trim(substr($row[$row_set], 6, 3));

			if ($chk!=NULL) {

				// No - 部品名 - 数量 - リール数 - 荷姿
				$part[$index][0] = trim(substr($row[$row_set],  6,  3));
				$part[$index][1] = trim(substr($row[$row_set], 10, 20));
				$part[$index][2] = trim(substr($row[$row_set], 50,  4));
				$part[$index][3] = trim(substr($row[$row_set], 60,  4));
				$part[$index][4] = pack_name(trim(substr($row[$row_set], 31, 5)));
				$row_set++;

			} else {

				// ページ間ヘッダスキップ
				$row_set = $row_set + 10;

				// 読込み行数 調整
				$row_cnt = $row_cnt - 9;

			}

			$index++;

		}

	}

}

// 作業用一時テーブルクリア
$res = $mdb2->exec("TRUNCATE TABLE data_tmp");

// 作業用テーブルへの登録
// 2007/07/31 基板ID無し対応
if ($id[0]=='dummy_name') {
	$id[0] = NULL;
}
$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2) VALUES(
					".$mdb2->quote('0', 'Text').",
					".$mdb2->quote($id[0], 'Text').",
					".$mdb2->quote($id[1], 'Text').")");

// 2007/08/20 最大100×5ステーション = 500 へ修正
for ($i=0; $i<=500; $i++) {
	if ($part[$i][0]!=NULL) {
		$res = $mdb2->exec("INSERT INTO data_tmp(data0, data1, data2, data3, data4) VALUES(
							".$mdb2->quote($part[$i][0], 'Text').",
							".$mdb2->quote($part[$i][1], 'Text').",
							".$mdb2->quote($part[$i][2], 'Text').",
							".$mdb2->quote($part[$i][3], 'Text').",
							".$mdb2->quote($part[$i][4], 'Text').")");
	}
}

// 配列へ格納
//$data[0] = array(0=>'0', 1=>$id[0], 2=>$id[1], 3=>$id[2]);
//
//for ($i=0; $i<=200; $i++) {
//
//	if ($part[$i][0]!=NULL) {
//		$data[] = array(0=>$part[$i][0], 1=>$part[$i][1], 2=>$part[$i][2], 3=>$part[$i][3], 4=>$part[$i][4]);
//	}
//}

// 基板ID、プログラム名 取得
$tmp_row = $mdb2->queryRow("SELECT * FROM data_tmp WHERE data0='0'");
if (PEAR::isError($tmp_row)) {
} else {
	$q_board     = $tmp_row[2];
	$q_prog      = $tmp_row[3];
	$tmp_len     = strlen($q_prog);
	$tmp_prog[0] = substr($q_prog, 0, $tmp_len-4);
	//$tmp_prog[1] = $tmp_prog[0] . ".H41";
	//$tmp_prog[2] = $tmp_prog[0] . ".H42";
	//$tmp_prog[3] = $tmp_prog[0] . ".H43";
	$tmp_prog[1] = $tmp_prog[0] . ".H51";
	$tmp_prog[2] = $tmp_prog[0] . ".H52";
	$tmp_prog[3] = $tmp_prog[0] . ".H53";
}

// [unit_id] 検索
// 2010-09-16 LIKE検索では期待した[unit_id]を取得出来ない場合があったので修正
//$res = $mdb2->queryRow("SELECT * FROM station_data WHERE p_name LIKE '$tmp_prog'");
$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE p_name IN('$tmp_prog[1]', '$tmp_prog[2]', '$tmp_prog[3]')");
if (PEAR::isError($st_row)) {
} else {
	$unit_id = $st_row[1];
}

// 2010-10-14 unit_idを取得出来ない場合は登録しない
if ($unit_id!=NULL) {

	// [qty_data] ユニット登録検索
//	$rs_qty = $mdb2->query("SELECT * FROM qty_data WHERE q_board='$q_board' AND q_prog='$q_prog' ORDER BY qty_id");
	$res_query = $mdb2->query("SELECT * FROM qty_data WHERE unit_id='$unit_id' ORDER BY qty_id");
	$res_query = err_check($res_query);

	$i = 0;
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$prog[$i] = $row['q_prog'];
		$i++;
	}

	if ($prog==NULL) {
		// ユニット登録数
		$cnt_qty = 0;
	} else {
		// ユニット登録数 取得
		$cnt_qty = count($prog) - 1;
	}

	// [qty_data] 登録データ取得
	$res_query = $mdb2->query("SELECT * FROM data_tmp WHERE data0!='0' ORDER BY tmp_id");
	$res_query = err_check($res_query);

	$i = 0;
	while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$str0[$i] = $row['data0'];	// index
		$str1[$i] = $row['data1'];	// 部品名
		$str2[$i] = $row['data2'];	// 数量
		$str3[$i] = $row['data3'];	// リール数
		$str4[$i] = $row['data4'];	// 荷姿
		$i++;
	}

	// データ数 取得
	$cnt_data = count($str0) - 1;

	if ($cnt_qty==0) {

		// 新規登録
		for ($set=0; $set<=$cnt_data; $set++) {

			$res = $mdb2->exec("INSERT INTO qty_data(unit_id, q_board, q_prog, q_index, q_part, qty, reel, q_pack) VALUES(
								".$mdb2->quote($unit_id, 'Integer').",
								".$mdb2->quote($q_board, 'Text').",
								".$mdb2->quote($q_prog, 'Text').",
								".$mdb2->quote($str0[$set], 'Integer').",
								".$mdb2->quote($str1[$set], 'Text').",
								".$mdb2->quote($str2[$set], 'Text').",
								".$mdb2->quote($str3[$set], 'Integer').",
								".$mdb2->quote($str4[$set], 'Text').")");

		}

	} else {

		// データ更新
		for ($set=0; $set<=$cnt_data; $set++) {

			$qty_row = $mdb2->queryRow("SELECT * FROM qty_data WHERE q_prog='$q_prog' AND q_index='$str0[$set]'");
			$qty_id = $qty_row[0];

			// トランザクションブロック開始
			$res = $mdb2->beginTransaction();

			$res = $mdb2->query("UPDATE qty_data SET q_part='$str1[$set]' WHERE qty_id='$qty_id'");
			$res = $mdb2->query("UPDATE qty_data SET qty='$str2[$set]' WHERE qty_id='$qty_id'");
			$res = $mdb2->query("UPDATE qty_data SET reel='$str3[$set]' WHERE qty_id='$qty_id'");
			$res = $mdb2->query("UPDATE qty_data SET q_pack='$str4[$set]' WHERE qty_id='$qty_id'");

			// トランザクションブロック終了
			$res = transaction_end($mdb2, $res);

		}

	}

}

// [set_data]変数初期化
unset($str0);
unset($str1);
unset($str2);
unset($str3);
unset($str4);
unset($cnt_data);

unset($tmp_prog);


/*

// 登録データ確認一覧表示
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('数量データ入力一覧 [');
print($q_board);
print('] 作成日：');
print(date("Y-m-d H:i:s"));
print('<br>');
print('<a href="excel/excel_out1.php?board=');
print($q_board);
print('" target="result"><img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');

print('<table border="1" align=center>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>数量ID</th>');
print('<th>ユニットID</th>');
print('<th>Index</th>');
print('<th>部品名</th>');
print('<th>数量</th>');
print('<th>リール数</th>');
print('<th>荷姿</th>');
print('</tr>');

//
//$sql_select = "SELECT * FROM set_data T3 JOIN station_data T2 ON(T3.st_id=T2.st_id) JOIN unit_data T1 ON(T2.unit_id=T1.unit_id)";
$sql_select = "SELECT * FROM qty_data";
$sql_where = " WHERE unit_id='$unit_id'";
$sql_order = " ORDER BY qty_id";
$search_sql = $sql_select . $sql_where . $sql_order;

$res_query = $mdb2->query($search_sql);
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$qty_id = $row['qty_id'];
	$unit_id = $row['unit_id'];
	$q_index = $row['q_index'];
	$q_part = $row['q_part'];
	$qty = $row['qty'];
	$reel = $row['reel'];
	$q_pack = $row['q_pack'];

	if ($qty_id!=NULL ) {

		print('<tr>');

		print('<td>');
		if ($qty_id==NULL) {
			print('<br>');
		} else {
			print($qty_id);
		}
		print('</td>');

		print('<td>');
		if ($unit_id==NULL) {
			print('<br>');
		} else {
			print($unit_id);
		}
		print('</td>');

		print('<td>');
		if ($q_index==NULL) {
			print('<br>');
		} else {
			print($q_index);
		}
		print('</td>');

		print('<td>');
		if ($q_part==NULL) {
			print('<br>');
		} else {
			print($q_part);
		}
		print('</td>');

		print('<td>');
		if ($qty==NULL) {
			print('<br>');
		} else {
			print($qty);
		}
		print('</td>');

		print('<td>');
		if ($reel==NULL) {
			print('<br>');
		} else {
			print($reel);
		}
		print('</td>');

		print('<td>');
		if ($q_pack==NULL) {
			print('<br>');
		} else {
			print($q_pack);
		}
		print('</td>');

		print('</tr>');

	}

}

print('</table></td>');
 */

?>
