<?php
//-------------------------------------------------------------------
// regist1_inc8.php
// 生産時の注意事項データ登録
//
// 2009/08/26
//
//
//-------------------------------------------------------------------

$csv_file = "../data/notes/" . $txt_file;

// 更新後の確認用
$up_cnt = 0;

$fp = fopen($csv_file, "r");

// 注意事項データ[notes_data]へ格納
while (($data=fgetcsv($fp, 10000, ","))!==FALSE) {

	// Trimによる前後スペース削除
	for ($i=0; $i<=3; $i++) {
		$data[$i] = Trim($data[$i]);
	}

	$unit_id     = $data[0];
	$pdf_name    = $data[1];
	$pdf_comment = $data[2];
	$pdf_issue   = $data[3];

	// [unit_id]で検索
	$res = $mdb2->queryRow("SELECT * FROM notes_data WHERE unit_id='$unit_id'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]==NULL) {

		// 未登録 -> 追加登録
		if ($pdf_name!=NULL) {
			$res = $mdb2->query("INSERT INTO notes_data(unit_id, pdf_name, pdf_comment, pdf_issue) VALUES(
							".$mdb2->quote($unit_id, 'Text').",
							".$mdb2->quote($pdf_name, 'Text').",
							".$mdb2->quote($pdf_comment, 'Text').",
							".$mdb2->quote($pdf_issue, 'Text').")");

			// 登録した[notes_id]の取得
			$res = $mdb2->queryRow("SELECT * FROM notes_data WHERE unit_id='$unit_id'");
			if (PEAR::isError($res)) {
			} elseif ($res[0]!=NULL) {
				$notes_id = $res[0];
			}

		}

	} else {

		$notes_id = $res[0];

		// トランザクションブロック開始
		$res = $mdb2->beginTransaction();

		// 登録済み -> データ更新
		$res = $mdb2->exec("UPDATE notes_data SET pdf_name='$pdf_name' WHERE notes_id='$notes_id'");
		$res = $mdb2->exec("UPDATE notes_data SET pdf_comment='$pdf_comment' WHERE notes_id='$notes_id'");
		$res = $mdb2->exec("UPDATE notes_data SET pdf_issue='$pdf_issue' WHERE notes_id='$notes_id'");

		// トランザクションブロック終了
		$res = transaction_end($mdb2, $res);

	}

}

// CSVファイルを閉じる
fclose($fp);


// 登録データ確認一覧表示
print('<br>');
print('<table border="1" align=center width="95%">');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('生産時の注意事項 一覧 作成日：');
print(date("Y-m-d H:i:s"));
print('<br>');
print('</b></font></div>');

// 項目名
print('<tr bgcolor="#cccccc">');
print('<th>Unit</th>');
print('<th>ファイル名</th>');
print('<th>コメント</th>');
print('<th>登録日</th>');
print('</tr>');


// ブラウザへの一覧表示は100件に制限
$res_query = $mdb2->query("SELECT * FROM notes_data ORDER BY notes_id LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);
$bgcolor = '#dcdcdc';

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$unit_id     = $row['unit_id'];
	$pdf_name    = $row['pdf_name'];
	$pdf_commnet = $row['pdf_comment'];
	$pdf_issue   = $row['pdf_issue'];

	// 列ごとに色を変える
	if ($bgcolor=='#f8f8ff') {
		$bgcolor = '#dcdcdc';
	} elseif ($bgcolor=='#dcdcdc') {
		$bgcolor = '#f8f8ff';
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

	print($color_set);
	print($unit_id);
	print('</td>');

	print($color_set);
	print('<a href="../data/notes/pdf/');
	print($pdf_name);
	print('" title="クリックで表示します">');
	print($pdf_name);
	print('</a></td>');

	print($color_set);
	print($pdf_commnet);
	print('</td>');

	print($color_set);
	print($pdf_issue);
	print('</td>');

	print('</tr>');

}

?>
