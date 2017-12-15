<?php
//-------------------------------------------------------------------
// search1_inc1_sub.php
// データファイル名検索(セットアップ)[共用部品検索部]
//
// 2008/12/05 共用部品の検索部を外部ファイル化？
//
//-------------------------------------------------------------------

// 共用部品 登録初期化(念の為実施)
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
//	$res_array = array_search($set_part[0][$part_tmp], $set_part[1]);
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

?>
