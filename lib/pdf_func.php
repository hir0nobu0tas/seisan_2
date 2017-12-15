<?php
//-------------------------------------------------------------------
// pdf_func.php
//
// 2008/01/
//
//-------------------------------------------------------------------

//-------------------------------------------------------------------
// ヘッダー用関数
//-------------------------------------------------------------------
function pdf_Header(&$pdf, $item) {

	$pdf->SetAuthor('Nitto Tsushinki');
	$pdf->SetCreator('Nitto Production Management System');
	$pdf->SetFont('MS-Gothic', 'B', 12);
	$pdf->SetFillColor(200, 200, 255);
	$pdf->SetXY(20, 10);
	$pdf->Cell(110, 8, $item , 1, 0, "C", 1);
	$pdf->SetFillColor(153, 255, 204);
	$today = date("Y-m-d H:i");
	$pdf->Cell(65, 8, $today . ' 出力', 1, 0,"C", 1);
	$pdf->Ln();

}

//-------------------------------------------------------------------
// フッター用関数
//-------------------------------------------------------------------
function pdf_Footer(&$pdf) {

	$pdf->SetFont('MS-Gothic', '', 10);
	$pdf->SetXY(60, -10);
	$pdf->Cell(100, 8, 'Page ' . $pdf->PageNo() . ' / {nb}', 0, 0,"C", 0);

}

//-------------------------------------------------------------------
// ライン表示(指定した座標に長さ175で線を引く)
//
// 引数 $pdf = pdfオブジェクト
//      $x_pos = X軸座標
//      $y_pos = Y軸座標
//-------------------------------------------------------------------
function pdf_Line(&$pdf, $x_pos, $y_pos) {

	// 線幅 0.5mm 線色 青
	$pdf->SetLineWidth(0.5);
	$pdf->SetDrawColor(0, 0, 255);

	$pdf->Line($x_pos, $y_pos, $x_pos + 175, $y_pos);

	// 線幅 0.2mm 線色 黒(デフォルト)へ戻す
	$pdf->SetLineWidth(0.2);
	$pdf->SetDrawColor(0, 0, 0);

}

//-------------------------------------------------------------------
// オーダー情報 バーコード表示
//
// 引数 $pdf      = pdfオブジェクト
//-------------------------------------------------------------------
function pdf_Order_Cell(&$pdf, $pdf_data) {

	$order_id   = $pdf_data[0];
	$operate_no = $pdf_data[1];
	$dash_no    = $pdf_data[2];
	$qty10      = $pdf_data[3];
	$product    = $pdf_data[4];
	$unit_id    = $pdf_data[5];
	$board      = $pdf_data[6];
	$p_name     = $pdf_data[7];

	// 線幅 0.2mm
//	$pdf->SetLineWidth(0.2);

	$pdf->SetXY(20, 21);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(20,7, '工番', 1, 0, "C", 0);
	$pdf->Cell(45,7, $operate_no, 1, 0, "C", 0);
	$pdf->Cell(20,7, 'ダッシュ', 1, 0, "C", 0);
	$pdf->Cell(35,7, $dash_no, 1, 0, "C", 0);
	$pdf->Cell(20,7,'数量', 1, 0, "C", 0);
	$pdf->Cell(35,7,$qty10, 1, 0, "C", 0);

	$pdf->SetXY(20, 28);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(20,10,'品名', 1, 0, "C", 0);
	$pdf->Cell(65,10,$product, 1, 0, "L", 0);
	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(90,10, $order_id, 1, 0, "C", 0);

	$pdf->SetXY(20, 40);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(20,8, '基板ID', 1, 0, "C", 0);
	$pdf->Cell(65,8, $board, 1, 0, "L", 0);

	$pdf->SetXY(110, 40);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(30,8, 'プログラム名', 1, 0, "C", 0);
	$pdf->Cell(55,8, $p_name, 1, 0, "L", 0);

	// 線幅 0.2mm(デフォルトに戻す)
//	$pdf->SetLineWidth(0.2);

	// セパレータ
	pdf_Line($pdf, 20, 51);

}

//-------------------------------------------------------------------
// セット位置 バーコード表示
//
// 引数 $pdf      = pdfオブジェクト
//      $w_pos    = 表示位置
//      $set_id   = SET ID(カセット位置)
//-------------------------------------------------------------------
function pdf_Set_Cell(&$pdf, $mdb2, $w_pos, $set_id) {

	// [set_data]から行取得
	$res_row = $mdb2->queryRow("SELECT * FROM set_data WHERE set_id='$set_id'");
	if (PEAR::isError($res_row)) {
	} elseif ($res_row[0]!=NULL) {
		$hole_no = $res_row[3];
		$part    = $res_row[4];
	}

	$set_id_code = '*' . $set_id . '*';

//	if ($w_pos>=1 and $w_pos<=10 ) {
//
//		$x_pos = 20;
//		$y_pos = 33 + (21 * $w_pos);
//
//	} elseif ($w_pos>=11 and $w_pos<=20 ) {
//
//		$x_pos = 110;
//		$y_pos = 33 + (21 * ($w_pos - 10));
//
//	}
	if ($w_pos>=0 and $w_pos<=9 ) {

		$x_pos = 20;
		$y_pos = 33 + (21 * ($w_pos + 1));

	} elseif ($w_pos>=10 and $w_pos<=19 ) {

		$x_pos = 110;
		$y_pos = 33 + (21 * ($w_pos - 9));

	}

	// 線幅 0.2mm
//	$pdf->SetLineWidth(0.2);

	$pdf->SetXY($x_pos, $y_pos);

	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->SetFillColor(200, 255, 200);
	$pdf->Cell(20, 8, $hole_no, 1, 0, "C", 1);
	$pdf->Cell(65, 8, $part, 1, 0, "L", 0);

	$pdf->SetXY($x_pos, $y_pos + 8);

	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(85, 10, $set_id_code, 1, 0, "C", 0);

	// 線幅 0.2mm(デフォルトに戻す)
//	$pdf->SetLineWidth(0.2);

}

//-------------------------------------------------------------------
// 登録/キャンセル バーコード表示
//
// 引数 $pdf      = pdfオブジェクト
//-------------------------------------------------------------------
function pdf_Entry_Cell(&$pdf) {

	// セパレータ
	pdf_Line($pdf, 20, 264);

	// 線幅 0.2mm
//	$pdf->SetLineWidth(0.2);

	$pdf->SetXY(20, 266);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->SetFillColor(200, 200, 255);
	$pdf->Cell(35, 10, "システム登録", 1, 0, "C", 1);
	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(50, 10, "*0001*", 1, 0, "C", 0);

	$pdf->SetXY(110, 266);
	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->SetFillColor(255, 200, 200);
	$pdf->Cell(35, 10, "登録キャンセル", 1, 0, "C", 1);
	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(50, 10, "*0000*", 1, 0, "C", 0);

	// 線幅 0.2mm(デフォルトに戻す)
//	$pdf->SetLineWidth(0.2);

}

//-------------------------------------------------------------------
// 号機 バーコード表示
//
// 引数 $pdf   = pdfオブジェクト
//      $x_pos = X軸座標
//      $y_pos = Y軸座標
//
//-------------------------------------------------------------------
function pdf_Machine_Cell(&$pdf, $x_pos, $y_pos) {

	// 線幅 0.2mm
	$pdf->SetLineWidth(0.2);

	$pdf->SetXY($x_pos, $y_pos);

	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(27,8, '1号機', 1, 0, "C", 0);
	$pdf->Cell(28,8, Modulus43_Disp('1000'), 1, 0, "C", 0);

	$pdf->SetXY($x_pos + 60, $y_pos);

	$pdf->Cell(27,8, '2号機', 1, 0, "C", 0);
	$pdf->Cell(28,8, Modulus43_Disp('2000'), 1, 0, "C", 0);

	$pdf->SetXY($x_pos + 120, $y_pos);

	$pdf->Cell(27,8, '3号機', 1, 0, "C", 0);
	$pdf->Cell(28,8, Modulus43_Disp('3000'), 1, 0, "C", 0);

	$pdf->SetXY($x_pos, $y_pos + 8);

	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(55,12, Modulus43_Code('1000'), 1, 0, "C", 0);

	$pdf->SetXY($x_pos + 60, $y_pos + 8);

	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(55,12, Modulus43_Code('2000'), 1, 0, "C", 0);

	$pdf->SetXY($x_pos + 120, $y_pos + 8);

	$pdf->SetFont('CODE39', '', 20);
	$pdf->Cell(55,12, Modulus43_Code('3000'), 1, 0, "C", 0);

	$pdf->Ln();

	// 線幅 0.2mm(デフォルトに戻す)
	$pdf->SetLineWidth(0.2);

}

//-------------------------------------------------------------------
// 作業者 バーコード表示
//
// 引数 $pdf = pdfオブジェクト
//      $x_pos = X軸座標
//      $y_pos = Y軸座標
//
//-------------------------------------------------------------------
function pdf_Worker_Cell(&$pdf, $x_pos, $y_pos, $worker_no, $worker_name) {

	$pdf->SetXY($x_pos, $y_pos);

	$pdf->SetFont('MS-Gothic', '', 12);
//	$pdf->Cell(30, 10, $worker_name, 1, 0, "C", 0);
	$pdf->Cell(40, 10, $worker_name, 1, 0, "C", 0);

	$pdf->SetXY($x_pos, $y_pos + 10);

//	$pdf->Cell(30, 10, Modulus43_Disp($worker_no), 1, 0, "C", 0);
	$pdf->Cell(40, 10, Modulus43_Disp($worker_no), 1, 0, "C", 0);

//	$pdf->SetXY($x_pos + 30, $y_pos);
	$pdf->SetXY($x_pos + 40, $y_pos);

	$pdf->SetFont('CODE39', '', 20);
//	$pdf->Cell(50, 20, Modulus43_Code($worker_no), 1, 0, "C", 0);
	$pdf->Cell(50, 20, Modulus43_Code($worker_no), 1, 0, "C", 0);
	$pdf->Ln();

}

?>
