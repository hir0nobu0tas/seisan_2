<?php
//-------------------------------------------------------------------
// search1_inc1.php
// データファイル名検索(セットアップ)
// $search_no = 1
//
// 2007/04/11
// 2007/08/24
// 2008/11/14 共用部品の検索 表面1と裏面1の場合のみ対応の暫定版
//
// 2009/05/11 部品一覧ファイル生成(現場要望) 実装開始 → OK
//
// 2009/07/31 同一部品集計が未完成だったので実装中 → OK？
//
// 2009/08/05 生産時の注意事項をPDF化してセットアップシート等と同様に検索(忠護SL要望)
//            一部注意書きは文字情報として[station_data]にインポートしているがPDFと
//            してリンク化 実装開始
//
// 2009/08/08 生産時の注意事項 仮実装(まだ複数のファイルに対応出来ていない)
//            まだ注意事項のファイル(PDF)の登録がほとんど無いので登録を増やしてテスト予定
//
// 2009/10/22 生産時の注意事項が裏面のみの場合に仮対応(見直し必要!)
//
// 2009/10/23 生産時の注意事項ファイルの取得処理を見直し修正
//
// 2009/11/11 表1、裏1、裏2の構成の場合、共用部品検索が出来無い 修正中
//            $cnt_unit = 2 → 3面(表1、裏1、裏2 か 表1、裏1、表2 ？)
//            従来と同様に最初の実装面の部品を基準として他の実装面に共用部品があるか検索
//            すべての実装面の部品で重複(共用)部品の検索は現時点では行なわない
//
// 2009/12/14 データファイル名検索(セットアップ)で検索するセットアップシートを
//            「絞り込み検索出来ないか？」と現場から要望有り
//            -----------------------------------------------------------------
//            今日追加登録した QPC30349C-01 と QPC30349D-01 の裏面データはGrp.Aと共用で
//            QPC30349A,C,D-02 として登録済み 現在の検索処理では Grp.C の表面と裏面のみ
//            を表示(使用部品一覧と共用部品の検索)する事が出来ない
//            1.一旦「QPC30349」で検索(すべてチェックされた状態)
//            2.Grp.Cで使用するデータのみチェックを残し再検索
//            3.Grp.Cだけの使用部品一覧と共用部品の検索となる
//
//            共用部品検索処理の修正もまだなので合わせて修正予定
//
// 2009/12/15 チェックボックスによる絞り込み検索は難しそうなので特殊な構成用の検索テーブル
//            を別途用意する処理を検討中
//            とりあえず放置していた 表1、裏1、裏2構成での共用部品検索処理を先に実装
//            暫定コピペ版なら結構あっさりと実装できたので関数化を検討
//
// 2009/12/16 特殊構成の検索処理は[search1.php]に実装
//
// 2016/11/21 共用部品の検索処理修正中 検索がうまく出来ていない場合がある
//            → ユニットデータをunit_idソートからu_indexソートへ変更で修正
//
// 2017/03/08 M2DC001(3)は01面と04面が組で02面は別のM/Cで生産するので01面と02面の
//            共用部品は表示しないようにして欲しいと現場から要望有り
//            u_indexソートはブラウザのリスト表示は並び変わるが、共用部品はまだ01面と02面
//            で検索されている
//            修正方法検討中
//
//
//-------------------------------------------------------------------

print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
//print('[ ');
//print($search);
//print(' ]');
//print('<br>');
print('セットアップシート：[ ');
print($parameter);
print(' ] 工番：[ ');
print($operate_no);
print(' ] ダッシュ：[ ');
print($dash_no);
print(' ] 数量：[ ');
print($mf_qty);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>Unit</th>');
print('<th>ファイル</th>');
print('<th>基板ID</th>');
print('<th>面</th>');
//print('<th>Index</th>');
print('<th>プログラム名</th>');
print('<th>ステーション名</th>');
print('</tr>');

//
if ($unit_id!=NULL) {

	$cnt_unit = count($unit_id) - 1;
	$bgcolor = '#dcdcdc';

	for ($i=0; $i<=$cnt_unit; $i++) {

		$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id[$i]'");
		if (PEAR::isError($unit_row)) {
		} else {
			$unit_data[$i][0] = $unit_row[0];
			$unit_data[$i][1] = $unit_row[1];
			$unit_data[$i][2] = $unit_row[2];
			$unit_data[$i][3] = $unit_row[3];
			$unit_data[$i][4] = $unit_row[4];
		}

		// [station_data] 取得
		$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id[$i]'");
		$res_query = err_check($res_query);

		$st_cnt = 0;
		while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$st_data[$i][0][$st_cnt] = $row['st_id'];
			$st_data[$i][1][$st_cnt] = $row['st_index'];
			$st_data[$i][2][$st_cnt] = $row['p_name'];
			$st_data[$i][3][$st_cnt] = $row['s_name'];
			$st_cnt++;
		}

		// 列ごとに色を変える(PBFはまた別の色で)
		if ($bgcolor == '#dcdcdc') {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor == '#f8f8ff') {
			$bgcolor = '#dcdcdc';
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		// ユニットID
		print($color_set);
		print($unit_data[$i][0]);
		print('</td>');

		// 基板名
		print($color_set);
		print($unit_data[$i][2]);
		print('</td>');

		// 製品名
		print($color_set);
		if ($unit_data[$i][3]==NULL) {
			print('<br>');
		} else {
			print($unit_data[$i][3]);
		}
		print('</td>');

		// 面
		print($color_set);
		switch ($unit_data[$i][1]) {
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

		// プログラム名
		print($color_set);
		print($st_data[$i][2][0]);
		print('</td>');

		// ステーション名
		print($color_set);
		print($st_data[$i][3][0]);
		print('</td>');


		// 2007/07/18 ブラウザ表示からExcel出力へ変更
		print($color_set);
		$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out1.php?unit_id=";
		$btn1 = $btn1 . $unit_data[$i][0] . "&operate_no=";
		$btn1 = $btn1 . $operate_no . "&dash_no=";
		$btn1 = $btn1 . $dash_no . "&mf_qty=";
		$btn1 = $btn1 . $mf_qty . "&sort_sel=";
		$btn1 = $btn1 . $sort_sel . "'\" value='Excel出力'></td>";
		print($btn1);

		print('</tr>');

	}

}

//-----------------------------------------------------------------------------
// 共用部品検索
// 2008/11/14 表面1と裏面1だけに対応の暫定版？
// 2009/12/15 表1、裏1、裏2に仮対応 最初の実装面を基準として他の面で使用している
//            か検索 すべての実装面の部品で重複(共用)部品があるか？という検索は
//            現時点では実装しない(ほとんど無い構成なので最低限の修正で対応する)
//            11月からずっと放置していたが暫定版(コピペ版)なら結構簡単に実装できた
//            関数化検討
//

// 実装面のカウント
$cnt_unit = count($unit_id) - 1;

for ($unit_tmp=0; $unit_tmp<=$cnt_unit; $unit_tmp++) {

	// 実装面毎の部品リスト取得
	$part_set[$unit_tmp]  = part_list($mdb2, $unit_id[$unit_tmp]);
	$id_list[$unit_tmp]   = $part_set[$unit_tmp][0];
	$part_list[$unit_tmp] = $part_set[$unit_tmp][1];

	// 部品数の取得
	$cnt_part[$unit_tmp] = count($part_list[$unit_tmp]) - 1;

}

// 2面以上の実装面があれば表面の部品リストを基準に裏面で同じ部品が使われているか確認
// $cnt_unit = 1 → 2面(表1、裏1)
// $cnt_unit = 3 → 4面(表1、裏1、表2、裏2)
// 3面は基本的に無いはずなので想定しない
//
// 2009/11/11
// 3面の構成があった! 表1、裏2、裏3の構成で共用品が検索出来ない 共用品検索を見直し
// $cnt_unit = 2 → 3面(表1、裏1、裏2 か 表1、裏1、表2 ？)
// 従来と同様に最初の実装面の部品を基準として他の実装面に共用部品があるか検索
// すべての実装面の部品で重複(共用)部品の検索は現時点では行なわない
//
// 2009/12/15
// 11月から放置していたがやっと表1、裏2、裏3の構成での共用品検索処理を実装
// 今後関数化を検討
//
if ($cnt_unit==1) {

	// 表1と裏1の照合
	// 共用部品 登録初期化(表1、裏1)
	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {
		$set_id = $id_list[0][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[1]; $part_tmp++) {
		$set_id = $id_list[1][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {

		// 共用部品の有り/無し 検索
//		$res_array = array_search($set_part[0][$part_tmp], $set_part[1]);
		$res_array = in_array($part_list[0][$part_tmp], $part_list[1]);

		// 共用部品有りの場合[set_id]と[part]取得
		if ($res_array==true) {

			// 検索元の[set_id]と[part](品名)取得
			$set_id[0] = $id_list[0][$part_tmp];
			$part[0]   = $part_list[0][$part_tmp];

			// foreachを初めて使ってみる
			foreach ($part_list[1] as $key=>$value) {

				if ($part[0]==$value) {
					$set_id = $id_list[0][$part_tmp];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");

					$set_id = $id_list[1][$key];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");
				}

			}

		}

	}

} elseif ($cnt_unit==2) {

	// 2009/12/15 暫定版(関数化？)

	// 表1と裏1の照合
	// 共用部品 登録初期化(表1、裏1)
	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {
		$set_id = $id_list[0][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[1]; $part_tmp++) {
		$set_id = $id_list[1][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {

		// 共用部品の有り/無し 検索
		$res_array = in_array($part_list[0][$part_tmp], $part_list[1]);

		// 共用部品有りの場合[set_id]と[part]取得
		if ($res_array==true) {

			// 検索元の[set_id]と[part](品名)取得
			$set_id[0] = $id_list[0][$part_tmp];
			$part[0]   = $part_list[0][$part_tmp];

			// foreachを初めて使ってみる
			foreach ($part_list[1] as $key=>$value) {

				if ($part[0]==$value) {
					$set_id = $id_list[0][$part_tmp];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");

					$set_id = $id_list[1][$key];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");
				}

			}

		}

	}

	// 表1と裏2の照合
	// 共用部品 登録初期化(裏2のみ)
	for ($part_tmp=0; $part_tmp<=$cnt_part[2]; $part_tmp++) {
		$set_id = $id_list[2][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {

		// 共用部品の有り/無し 検索
		$res_array = in_array($part_list[0][$part_tmp], $part_list[2]);

		// 共用部品有りの場合[set_id]と[part]取得
		if ($res_array==true) {

			// 検索元の[set_id]と[part](品名)取得
			$set_id[0] = $id_list[0][$part_tmp];
			$part[0]   = $part_list[0][$part_tmp];

			// foreachを初めて使ってみる
			foreach ($part_list[2] as $key=>$value) {

				if ($part[0]==$value) {
					$set_id = $id_list[0][$part_tmp];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");

					$set_id = $id_list[2][$key];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");
				}

			}

		}

	}

} elseif ($cnt_unit==3) {

	// 2008/11/24 暫定版(とりあえずのコピペ版 関数化？)

	// 表1と裏1の照合
	// 共用部品 登録初期化(表1、裏1)
	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {
		$set_id = $id_list[0][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[1]; $part_tmp++) {
		$set_id = $id_list[1][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[0]; $part_tmp++) {

		// 共用部品の有り/無し 検索
		$res_array = in_array($part_list[0][$part_tmp], $part_list[1]);

		// 共用部品有りの場合[set_id]と[part]取得
		if ($res_array==true) {

			// 検索元の[set_id]と[part](品名)取得
			$set_id[0] = $id_list[0][$part_tmp];
			$part[0]   = $part_list[0][$part_tmp];

			// foreachを初めて使ってみる
			foreach ($part_list[1] as $key=>$value) {

				if ($part[0]==$value) {
					$set_id = $id_list[0][$part_tmp];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");

					$set_id = $id_list[1][$key];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");
				}

			}

		}

	}

	// 表2と裏2の照合
	// 共用部品 登録初期化(表2。裏2)
	for ($part_tmp=0; $part_tmp<=$cnt_part[2]; $part_tmp++) {
		$set_id = $id_list[2][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[3]; $part_tmp++) {
		$set_id = $id_list[3][$part_tmp];
		$res = $mdb2->exec("UPDATE set_data SET p_share='' WHERE set_id='$set_id'");
	}

	for ($part_tmp=0; $part_tmp<=$cnt_part[2]; $part_tmp++) {

		// 共用部品の有り/無し 検索
		$res_array = in_array($part_list[2][$part_tmp], $part_list[3]);

		// 共用部品有りの場合[set_id]と[part]取得
		if ($res_array==true) {

			// 検索元の[set_id]と[part](品名)取得
			$set_id[2] = $id_list[2][$part_tmp];
			$part[2]   = $part_list[2][$part_tmp];

			// foreachを初めて使ってみる
			foreach ($part_list[3] as $key=>$value) {

				if ($part[2]==$value) {
					$set_id = $id_list[2][$part_tmp];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");

					$set_id = $id_list[3][$key];
					$res = $mdb2->exec("UPDATE set_data SET p_share='共' WHERE set_id='$set_id'");
				}

			}

		}

	}

}

print('</table>');


//-----------------------------------------------------------------------------
// 2009/05/13～ 現場 栄君からの要望(3月から放置していた)
// 2009/06/19 同一部品集計以外は大体要望通り？
// 2009/07/31 同一部品集計 仮対応(もう少し表示を工夫できるか？)
//

print('<br>');
print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('使用部品一覧：[ ');
print($parameter);
print(' ] 工番：[ ');
print($operate_no);
print(' ] ダッシュ：[ ');
print($dash_no);
print(' ] 数量：[ ');
print($mf_qty);
//print(' ] *2009-07-31 同一部品集計を修正中です*');
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>Unit0</th>');
print('<th>Unit1</th>');
print('<th>Unit2</th>');
print('<th>Unit3</th>');
print('<th>製品名</th>');
print('<th>基板ID</th>');
print('<th>プログラム名</th>');
//print('<th>ステーション名</th>');
print('</tr>');

//print('<pre>');
//var_dump($unit_id);
//print('</pre>');

//
if ($unit_id!=NULL) {

	$cnt_unit = count($unit_id) - 1;
	$bgcolor = '#ffffcc';

	// [unit_data] 取得
	$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id[0]'");
	if (PEAR::isError($unit_row)) {
	} else {
		$file_name = $unit_row[2];
		$board     = $unit_row[3];
		$product   = $unit_row[4];
	}

	// [station_data] 取得
	$st_row = $mdb2->queryRow("SELECT * FROM station_data WHERE unit_id='$unit_id[0]' ORDER BY st_id");
	if (PEAR::isError($st_row)) {
	} else {
		$p_name = $st_row[3];
	}

	$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

	print('<tr>');

	// ユニットID
	print($color_set);
	print($unit_id[0]);
	print('</td>');

	print($color_set);
	if ($unit_id[1]==NULL) {
		print('<br>');
	} else {
		print($unit_id[1]);
	}
	print('</td>');

	print($color_set);
	if ($unit_id[2]==NULL) {
		print('<br>');
	} else {
		print($unit_id[2]);
	}
	print('</td>');

	print($color_set);
	if ($unit_id[3]==NULL) {
		print('<br>');
	} else {
		print($unit_id[3]);
	}
	print('</td>');

	// 製品名
	print($color_set);
	if ($product==NULL) {
		print('<br>');
	} else {
		print($product);
	}
	print('</td>');

	// 基板名
	print($color_set);
	if ($board==NULL) {
		print('<br>');
	} else {
		print($board);
	}
	print('</td>');

	// プログラム名
	print($color_set);
	if ($p_name==NULL) {
		print('<br>');
	} else {
		print($p_name);
	}
	print('</td>');

	// 2007/07/18 直接Excel出力へ変更
	// ここからmode_serch1=dispで呼ばなければserch1.phpのdispブロックは何処からも
	// 呼ばれない
	// 2016/03/11 ファイル名用に品名を渡す
	// 2016/04/18 ダッシュ追加
	print($color_set);
	$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out8.php?unit_id0=";
	$btn1 = $btn1 . $unit_id[0] . "&unit_id1=";
	$btn1 = $btn1 . $unit_id[1] . "&unit_id2=";
	$btn1 = $btn1 . $unit_id[2] . "&unit_id3=";
	$btn1 = $btn1 . $unit_id[3] . "&operate_no=";
	$btn1 = $btn1 . $operate_no . "&dash_no=";
	$btn1 = $btn1 . $dash_no . "&product=";
	$btn1 = $btn1 . $parameter . "&mf_qty=";
	$btn1 = $btn1 . $mf_qty . "'\" value='Excel出力'></td>";
	print($btn1);

	print('</tr>');

}

print('</table>');


//-----------------------------------------------------------------------------
// 2009/08/05～ 機種毎の注意事項をPDF化してリンク表示(忠護SL要望) 実装開始
// 2009/08/08 仮実装 現在は注意事項は1つしか表示しない
//

print('<br>');
print('<table border="1" align=center width="95%">');
//print('<table border="1" align=left>');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('生産時の注意事項：[ ');
print($parameter);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
//print('<th>Unit</th>');
//print('<th>セットアップファイル</th>');
print('<th>生産時の注意事項(PDF)</th>');
print('<th>登録日</th>');
print('</tr>');

//
if ($unit_id!=NULL) {

	$cnt_unit = count($unit_id) - 1;
	$bgcolor = '#ffcc99';

	// [notes_data] 取得(基本的に1機種に注意事項は1ファイルにまとめる)
	for ($i=0; $i<=$cnt_unit; $i++) {

		$notes_row = $mdb2->queryRow("SELECT * FROM notes_data WHERE unit_id='$unit_id[$i]'");
		if (PEAR::isError($notes_row)) {
		} else {
			$notes_id    = $notes_row[0];
			$pdf_name    = $notes_row[2];
			$pdf_comment = $notes_row[3];
			$pdf_issue   = $notes_row[4];

			// 注意事項ファイルを取得したらbreakする
			if ($notes_id!=NULL) {
		    	break;
		    }
		}

	}

	if ($pdf_name!=NULL) {

		// 注意事項登録有り
		print('<tr>');

		// 注意事項ファイル(リンク)
		print('<td bgcolor="#ffcc99" width="80%">');
		print('<a href="../data/notes/pdf/');
		print($pdf_name);
		print('" title="クリックで表示します">');
		print($pdf_comment);
		print('</a></td>');

		// 発行日
		print('<td bgcolor="#ffcc99" width="20%">');
		print($pdf_issue);
		print('</a></td>');

		print('</tr>');

	} else {

		// 注意事項登録無し(セル背景色を変えてみた)
		print('<tr>');

		// 注意事項ファイル(リンク)
		print('<td bgcolor="#99ff99" width="80%">');
		print('注意事項は登録されていません');
		print('</a></td>');

		// 発行日
		print('<td bgcolor="#99ff99" width="20%">');
		print('<br>');
		print('</a></td>');

		print('</tr>');

	}


}

print('</table>');


//-----------------------------------------------------------------------------
print('<br>');
print('<br>');
print('<table border="1" align=center width="95%">');
print('<caption>');
print('<div align="center"><font size="4" color="#0066cc"><b>');
print('セットアップデータ 更新用：[ ');
print($parameter);
print(' ]');
print('</b></font></div>');
print('</caption>');

print('<tr bgcolor="#cccccc">');
print('<th>Unit</th>');
print('<th>ファイル</th>');
print('<th>基板ID</th>');
print('<th>面</th>');
//print('<th>Index</th>');
print('<th>プログラム名</th>');
print('<th>ステーション名</th>');
print('</tr>');

//
if ($unit_id!=NULL) {

	$cnt_unit = count($unit_id) - 1;
	$bgcolor  = '#dcdcdc';

	for ($i=0; $i<=$cnt_unit; $i++) {

		$unit_row = $mdb2->queryRow("SELECT * FROM unit_data WHERE unit_id='$unit_id[$i]'");
		if (PEAR::isError($unit_row)) {
		} else {
			$unit_data[$i][0] = $unit_row[0];
			$unit_data[$i][1] = $unit_row[1];
			$unit_data[$i][2] = $unit_row[2];
			$unit_data[$i][3] = $unit_row[3];
			$unit_data[$i][4] = $unit_row[4];
		}

		// [station_data] 取得
		$res_query = $mdb2->query("SELECT * FROM station_data WHERE unit_id='$unit_id[$i]'");
		$res_query = err_check($res_query);

		$st_cnt = 0;
		while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$st_data[$i][0][$st_cnt] = $row['st_id'];
			$st_data[$i][1][$st_cnt] = $row['st_index'];
			$st_data[$i][2][$st_cnt] = $row['p_name'];
			$st_data[$i][3][$st_cnt] = $row['s_name'];
			$st_cnt++;
		}

		// 列ごとに色を変える(PBFはまた別の色で)
		if ($bgcolor == '#dcdcdc') {
			$bgcolor = '#f8f8ff';
		} elseif ($bgcolor == '#f8f8ff') {
			$bgcolor = '#dcdcdc';
		}

		$color_set = "<td bgcolor=\"" . $bgcolor . "\">";

		print('<tr>');

		// ユニットID
		print($color_set);
		print($unit_data[$i][0]);
		print('</td>');

		// 基板名
		print($color_set);
		print($unit_data[$i][2]);
		print('</td>');

		// 製品名
		print($color_set);
		if ($unit_data[$i][3]==NULL) {
			print('<br>');
		} else {
			print($unit_data[$i][3]);
		}
		print('</td>');

		// 面
		print($color_set);
		switch ($unit_data[$i][1]) {
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

		// プログラム名
		print($color_set);
		print($st_data[$i][2][0]);
		print('</td>');

		// ステーション名
		print($color_set);
		print($st_data[$i][3][0]);
		print('</td>');

		print($color_set);
		$btn1 = "<input type='button' name='edit' onClick=\"location.href='excel/excel_out2.php?id=";
		$btn1 = $btn1 . $unit_data[$i][0] . "'\" value='Excel出力'></td>";
		print($btn1);

		print('</tr>');

	}

}

print('</table>');


?>
