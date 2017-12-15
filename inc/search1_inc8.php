<?php
//-------------------------------------------------------------------
// search1_inc8.php
// アドバンス棚 登録一覧 (Excel出力)
// $search_no = 8
//
// 2009/08/24
// 2010/02/16 項目がずれていたので修正
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('アドバンス棚 登録品一覧 作成日： ');
print(date("Y-m-d H:i"));
print('<br>');
print('＊2009-08-21 出力件数を100件に制限(Excel出力は全件出力します)');
print('<br>');
print('<a href="excel/excel_out9.php" target="result">');
print('<img src="../graphics/seisan_excel_out.png" border="0"></a>');
print('</b></font></div>');

print('<tr bgcolor="#cccccc">');
print('<th>ID</th>');
print('<th>棚番</th>');
print('<th>部品番号</th>');
print('<th>品名1</th>');
print('<th>品名2</th>');
print('<th>品名3</th>');
print('<th>品名4</th>');
print('<th>品名5</th>');
print('<th>備考</th>');
print('</tr>');

// DB接続
$mdb2 = db_connect();

$bgcolor = '#dcdcdc';

// 2008/08/21 確認用なので出力件数を100件までに制限
$res_query = $mdb2->query("SELECT * FROM part_advance ORDER BY rack_no LIMIT 100 OFFSET 1");
$res_query = err_check($res_query);
$bgcolor = '#f8f8ff';

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$advance_id  = $row['advance_id'];
	$rack_no     = $row['rack_no'];
	$part_no     = $row['part_no'];
	$part_1      = $row['part_1'];
	$part_2      = $row['part_2'];
	$part_3      = $row['part_3'];
	$part_4      = $row['part_4'];
	$part_5      = $row['part_5'];
	$rem_advance = $row['rem_advance'];

	// 列ごとに色を変える
	if ($bgcolor=='#f8f8ff') {
		$bgcolor = '#dcdcdc';
	} elseif ($bgcolor=='#dcdcdc') {
		$bgcolor = '#f8f8ff';
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

	print($color_set);
	print($advance_id);
	print('</td>');

	print($color_set);
	print($rack_no);
	print('</td>');

	print($color_set);
	print($part_no);
	print('</td>');

	print($color_set);
	if ($part_1!=NULL) {
		print($part_1);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($part_2!=NULL) {
		print($part_2);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($part_3!=NULL) {
		print($part_3);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($part_4!=NULL) {
		print($part_4);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($part_5!=NULL) {
		print($part_5);
	} else {
		print('<br>');
	}
	print('</td>');

	print($color_set);
	if ($rem_advance!=NULL) {
		print($rem_advance);
	} else {
		print('<br>');
	}
	print('</td>');

	print('</tr>');

}

// DB切断
db_disconnect($mdb2);

?>
