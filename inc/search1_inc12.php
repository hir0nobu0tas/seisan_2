<?php
//-------------------------------------------------------------------
// search1_inc12.php
// 部品名 → 使用基板 検索
// $search_no = 12
//
// 2011-06-14
// 2015-10-30 修正
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('[ 部品名 ] => ');
print('[ ');
print($parameter);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>ID</th>');
print('<th>実装面</th>');
print('<th>ファイル名</th>');
print('<th>基板名</th>');
print('<th>登録日</th>');
//print('<th>確認日</th>');
print('<th>セット位置</th>');
print('<th>部品名</th>');
print('</tr>');

$res_query = $mdb2->query($search_sql);
$res_query = err_check($res_query);

$bgcolor = '#dcdcdc';
while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	$unit_id    = $row['unit_id'];
	$unit_index = $row['u_index'];
	$file_name  = $row['file_name'];
	$board      = $row['board'];
	$refix_date = $row['refix_date'];
	$chk_date   = $row['chk_date'];

	$set_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id IN(SELECT st_id FROM station_data WHERE unit_id='$unit_id') AND part='$parameter'");
	if (PEAR::isError($set_row)) {
	} else {
		$hole_no = $set_row[3];
		$part    = $set_row[4];
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
	print($unit_id);
	print('</td>');

	print($color_set);
	switch ($unit_index) {
	case 1:
		print('表1');
		break;
	case 2:
		print('裏1');
		break;
	case 3:
		print('表2');
		break;
	case 4:
		print('裏2');
		break;
	}
	print('</td>');

	print($color_set);
	print($file_name);
	print('</td>');

	print($color_set);
	if ($board==NULL) {
		print('<br>');
	} else {
		print($board);
	}
	print('</td>');

	print($color_set);
	print($refix_date);
	print('</td>');

//	print($color_set);
//	if ($chk_date==NULL) {
//		print('<br>');
//	} else {
//		print($chk_date);
//	}
//	print('</td>');

	print($color_set);
	if ($hole_no==NULL) {
		print('<br>');
	} else {
		print($hole_no);
	}
	print('</td>');

	print($color_set);
	if ($part==NULL) {
		print('<br>');
	} else {
		print($part);
	}
	print('</td>');

	print('</tr>');

}


?>
