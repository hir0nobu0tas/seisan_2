<?php
//-------------------------------------------------------------------
// search1_inc9.php
// セットアップデータ 登録一覧 (Excel出力)
// $search_no = 9
//
// 2009/08/24
// 2009/08/26 生産時の注意事項 表示追加
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('セットアップデータ 登録一覧 作成日： ');
print(date("Y-m-d H:i"));
print('<br>');
print('＊2009-08-24 表示件数を100件に制限(Excel出力は全件出力します)');
print('<br>');
print('<a href="excel/excel_out10.php" target="result">');
print('<img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');

print('<tr bgcolor="#cccccc">');
print('<th>Unit</th>');
print('<th>ファイル名</th>');
print('<th>基板名</th>');
print('<th>装置名</th>');
print('<th>更新日</th>');
print('<th>確認状況</th>');
print('<th>確認日</th>');
print('<th>注意事項</th>');
print('</tr>');

// DB接続
$mdb2 = db_connect();

$bgcolor = '#dcdcdc';

// 2008/08/21 確認用なので出力件数を100件までに制限
$res_query = $mdb2->query("SELECT * FROM unit_data ORDER BY unit_id LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);
$bgcolor = '#f8f8ff';

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$unit_id    = $row['unit_id'];
	$u_index    = $row['u_index'];
	$file_name  = $row['file_name'];
	$board      = $row['board'];
	$product    = $row['product'];
	$refix_date = $row['refix_date'];
	$chk_status = $row['chk_status'];
	$chk_date   = $row['chk_date'];

	$res = $mdb2->queryRow("SELECT * FROM notes_data WHERE unit_id='$unit_id'");
	if (PEAR::isError($res)) {
	} elseif ($res[0]==NULL) {
		$notes_data = "";
	} else {
		$notes_data = "登録有り";
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
	print($unit_id);
	print('</td>');

	print($color_set);
	print($file_name);
	print('</td>');

	print($color_set);
	if ($board!=NULL) {
		print($board);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($product!=NULL) {
		print($product);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	print($refix_date);
	print('</td>');

	print($color_set);
	if ($chk_status!=NULL) {
		print($chk_status);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($chk_date!=NULL) {
		print($chk_date);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($notes_data!=NULL) {
		print($notes_data);
	} else {
		print('<br>');
	}
	print('</td>');

	print('</tr>');

}

// DB切断
db_disconnect($mdb2);

?>
