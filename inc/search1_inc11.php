<?php
//-------------------------------------------------------------------
// search1_inc11.php
// 棚番 → セットアップシート 検索
// $search_no = 11
//
// 2009/12/22
// 2009/12/24
//
// 2011/06/14
//
// 2015/10/30 新棚番対応
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('[ 棚番 ] => ');
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

	//$res_query = $mdb2->query("SELECT * FROM part_smt WHERE rack_no like '$search_para' OR rack_old like '$search_para' ORDER BY smt_id");

/*
print('<pre>');
var_dump($parameter);
var_dump($search_para);
print('</pre>');
 */
	$set_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id IN(SELECT st_id FROM station_data WHERE unit_id='$unit_id') AND rack_data='$parameter'");
	//$set_row = $mdb2->queryRow("SELECT * FROM set_data WHERE st_id IN(SELECT st_id FROM station_data WHERE unit_id='$unit_id') AND rack_data LIKE '$search_para'");
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
