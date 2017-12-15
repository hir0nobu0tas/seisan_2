<?php
//-------------------------------------------------------------------
// com_func2.php
//-------------------------------------------------------------------

//-------------------------------------------------------------------
// テキストファイル->配列
//
// 引数	 $txt_data = フルパスファイル名
// 戻り値  return    = 1行毎に配列格納
//-------------------------------------------------------------------
function txt2array($txt_data) {

	// ファイルを開く
	$fp = fopen($txt_data, "r");

	// 行単位で配列に格納(行送りなどの為)
	$i = 0;
	while (($buffer=fgets($fp))!==FALSE) {
		$row[$i] = $buffer;
		$i++;
	}

	// ファイルを閉じる
	fclose($fp);

	return($row);

}

//---------------------------------------------------------------
// リールサイズ
//
// 引数   $r_size =
//
// 戻り値 return  = リールサイズ表記
//---------------------------------------------------------------
function r_size_name($r_size) {

	switch ($r_size) {
	case NULL:
		$size_disp = '<br>';
		break;
	case 0:
		$size_disp = '中(250mm)';
		break;
	case 1:
		$size_disp = '小(180mm)';
		break;
	case 2:
		$size_disp = '大(330mm)';
		break;
	case 3:
		$size_disp = '特大(380mm)';
		break;
	case 4:
		$size_disp = '千代田専用品';
		break;
	case 5:
		$size_disp = '防湿庫/乾燥庫';
		break;
	}

	return ($size_disp);

}

//---------------------------------------------------------------
// 高額品
//
// 引数   $exp_item =
//
// 戻り値 return    = 高額品/
//---------------------------------------------------------------
function exp_item_name($exp_item) {

	switch ($exp_item) {
	case 0:
		$exp_disp = '';
		break;
	case 1:
		$exp_disp= '高額';
		break;
	}

	return ($exp_disp);

}

?>
