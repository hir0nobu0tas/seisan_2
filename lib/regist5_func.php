<?php
//-----------------------------------------------------------------------------
//[生産管理システム]
//
// * 部品リール払い出し処理[regist5.php]用外部関数
//
//  2008/07/01
//  2008/07/02
//
//-----------------------------------------------------------------------------

//---------------------------------------------------------------
// リール登録処理
//
// 引数   $mdb2        = 接続したDBオブジェクト
//        $sel_process = 登録処理の種別(1=払い出し、2=返却、3=登録削除
//        $check_no    = リール管理番号
//        $sel_mc      = M/C担当
//        $set_date    = 登録日
//        $rem_manage  = 備考
//
// 戻り値 なし
//---------------------------------------------------------------
function reel_reg($mdb2, $sel_process, $check_no, $sel_mc, $set_date, $rem_manage) {

	// 払い出しリール登録
	if ($check_no!=NULL) {
		$res = $mdb2->queryRow("SELECT * FROM reel_no WHERE check_no='$check_no'");
		if (PEAR::isError($res)) {
		} elseif ($res[0]!=NULL) {
			$reel_id = $res[0];
		}
	}

	if ($reel_id!=NULL) {

		$res = $mdb2->queryRow("SELECT * FROM reel_manage WHERE reel_id='$reel_id'");
		if (PEAR::isError($res)) {
		} elseif ($res[0]==NULL) {

			// 新規登録
			$res = $mdb2->query("INSERT INTO reel_manage(reel_id, sel_mc, out_date, ret_date, rem_manage) VALUES(
								".$mdb2->quote($reel_id, 'Integer').",
								".$mdb2->quote($sel_mc, 'Integer').",
								".$mdb2->quote($set_date, 'Date').",
								".$mdb2->quote(NULL, 'Date').",
								".$mdb2->quote($rem_manage, 'Text').")");

		} else {

			$manage_id = $res[0];

			if ($sel_process==1 or $sel_process==2) {

				// トランザクションブロック開始
				$res = $mdb2->beginTransaction();

				// 更新
				// 払い出し → 払い出し日登録、返却日をNULLへ
				// 返却     → 返却日登録
				$res = $mdb2->exec("UPDATE reel_manage SET sel_mc='$sel_mc' WHERE manage_id='$manage_id'");

				if ($sel_process==1) {
					$res = $mdb2->exec("UPDATE reel_manage SET out_date='$set_date' WHERE manage_id='$manage_id'");
					$res = $mdb2->exec("UPDATE reel_manage SET ret_date=NULL WHERE manage_id='$manage_id'");
				} elseif ($sel_process==2) {
					$res = $mdb2->exec("UPDATE reel_manage SET ret_date='$set_date' WHERE manage_id='$manage_id'");
				}

				$res = $mdb2->exec("UPDATE reel_manage SET rem_manage='$rem_manage' WHERE manage_id='$manage_id'");

				// トランザクションブロック終了
				$res = transaction_end($mdb2, $res);

			} elseif ($sel_process==3) {

				// 登録削除
				$res = $mdb2->exec("DELETE FROM reel_manage WHERE manage_id='$manage_id'");

			}

		}

	}

}

//---------------------------------------------------------------
// 登録処理 名称
//
// 引数   $sel_process = 登録処理の種別
//
// 戻り値 return       = 登録処理名称
//---------------------------------------------------------------
function process_name($sel_process) {

	switch ($sel_process) {
	case NULL:
		$process_disp = '';
		break;
	case 0:
		$process_disp = '';
		break;
	case 1:
		$process_disp = '払い出し(日)';
		break;
	case 2:
		$process_disp = '返却(日)';
		break;
	case 3:
		$process_disp = '登録削除';
		break;
	case 4:
		$process_disp = '払い出し(月)';
		break;
	case 5:
		$process_disp = '未返却(月)';
		break;
	}

	return ($process_disp);

}

//---------------------------------------------------------------
// M/C担当 名称
//
// 引数   $sel_mc = 払い出し先の種別(0=社内、1=外注)
//
// 戻り値 return  = 払い出し先名称
//---------------------------------------------------------------
function mc_name($sel_mc) {

	switch ($sel_mc) {
	case NULL:
		$mc_disp = '';
		break;
	case 0:
		$mc_disp = '社内';
		break;
	case 1:
		$mc_disp = '外注';
		break;
	}

	return ($mc_disp);

}

//---------------------------------------------------------------
// 一覧表示用SQL文 設定
//
// 引数   $sel_process = 処理設定
//
// 戻り値 return       = $list_sql(SQL文)
//---------------------------------------------------------------
function list_sql_set($sel_process, $set_date) {

	// 表示範囲 設定
	// 引数の日付を分解して指定年月の1ヶ月分を表示
	list($set_y, $set_m, $set_d) = split('[/.-]', $set_date);

	// 一覧表示(開始)
	$range_s  = date('Y-m-d', mktime (0, 0, 0, $set_m, 1, $set_y));

	// 一覧表示(終了)
	$range_e  = date('Y-m-d', mktime (0, 0, 0, $set_m + 1, 0, $set_y));

	switch ($sel_process) {
	case 0:
		$list_sql = "SELECT * FROM reel_manage WHERE out_date BETWEEN '$range_s' AND '$range_e' ORDER BY manage_id";
		break;
	case 1:
		$list_sql = "SELECT * FROM reel_manage WHERE out_date='$set_date' ORDER BY manage_id";
		break;
	case 2:
		$list_sql = "SELECT * FROM reel_manage WHERE ret_date='$set_date' ORDER BY manage_id";
		break;
	case 3:
		$list_sql = "SELECT * FROM reel_manage WHERE out_date BETWEEN '$range_s' AND '$range_e' ORDER BY manage_id";
		break;
	case 4:
		$list_sql = "SELECT * FROM reel_manage WHERE out_date BETWEEN '$range_s' AND '$range_e' ORDER BY manage_id";
		break;
	case 5:
		$list_sql = "SELECT * FROM reel_manage WHERE out_date BETWEEN '$range_s' AND '$range_e' AND ret_date IS NULL ORDER BY manage_id";
		break;
	}

	return ($list_sql);

}

?>
