<?php

// 生産計画システム 生産管理票(バーコード)サンプル
// 2007/04/11

// 表示対象データの作成
// 実業務アプリケーションではDBからの読み込みになる
//$data = array();
$plan = $_GET["plan"];
$operate_no = '1234-00';
$dash_no = '012345';
$product = 'ABCDEFG-00';

// MBFPDFの読み込みと拡張クラス定義
require_once("fpdf/fpdf.php");
require_once("fpdf/mbfpdf.php");
$EUC2SJIS=true;		// EUC-JP使用
include 'lib/modulus43.php';

class HeaderFooterExtended extends MBFPDF {
	function Header() { theHeader($this); }
//	function Footer() { theFooter($this); }
}


// ヘッダー用関数
function theHeader(&$pdf) {

	$pdf->SetFont('MS-Gothic', 'B', 12);
	$pdf->SetFillColor(200, 200, 255);
	$pdf->SetX(70);
	$pdf->Cell(10, 10, '生産管理システム バーコードテスト', 0, 0, "C", 0);
	$pdf->Ln();

}

// フッター用関数
function theFooter(&$pdf) {
	$pdf->SetFont('MS-Gothic', '', 10);
	$pdf->SetXY(60, -15);
	$pdf->Cell(100, 10, 'Page ' . $pdf->PageNo() . ' / {nb}', 0, 0,"C", 0);
}

// 工程コード
function task_code($task) {
	switch ($task) {
	case 'SMT実装 開始':
		return ('01-01');
		break;
	case 'SMT実装 終了':
		return ('01-02');
		break;
	case 'X線検査 開始':
		return ('02-01');
		break;
	case 'X線検査 終了':
		return ('02-02');
		break;
	case '実装検査 開始':
		return ('03-01');
		break;
	case '実装検査 終了':
		return ('03-02');
		break;
	case 'SMT修正 開始':
		return ('04-01');
		break;
	case 'SMT修正 終了':
		return ('04-02');
		break;
	case 'IMT実装1 開始':
		return ('05-01');
		break;
	case 'IMT実装1 終了':
		return ('05-02');
		break;
	case 'IMTはんだ付け 開始':
		return ('06-01');
		break;
	case 'IMTはんだ付け 終了':
		return ('06-02');
		break;
	case 'IMT修正 開始':
		return ('07-01');
		break;
	case 'IMT修正 終了':
		return ('07-02');
		break;
	case 'IMT実装2 開始':
		return ('08-01');
		break;
	case 'IMT実装2 終了':
		return ('08-02');
		break;
	case '捨て基板切断 開始':
		return ('09-01');
		break;
	case '捨て基板切断 終了':
		return ('09-02');
		break;
	case '機構検査 開始':
		return ('10-01');
		break;
	case '機構検査 終了':
		return ('10-02');
		break;
	case '電気検査 開始':
		return ('11-01');
		break;
	case '電気検査 終了':
		return ('11-02');
		break;
	case '出荷 開始':
		return ('12-01');
		break;
	case '出荷 終了':
		return ('12-02');
		break;
	}
}

// 工程PDF出力
function task_disp($pdf, $x1, $y1, $task) {

	$task_code = task_code($task);

	// SMT実装開始
	$x2 = $x1;
	$y2 = $y1 + 10;
	$pdf->SetXY($x2, $y2);
	$disp0 = $task_code;
	$disp1 = Modulus43_Work($disp0);

	$pdf->SetFont('MS-Gothic', '', 12);
	$pdf->Cell(30,14, $task, 1, 0, "C", 0);
	$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

//	$pdf->SetFont('CODE39', '', 24);
	$pdf->SetFont('CODE39', '', 14);
	$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

	$pdf->SetFont('MS-Gothic', '', 12);
	$x2 = 50;
	$y2 = $y2 + 7;
	$pdf->SetXY($x2, $y2);
	$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
	$pdf->Ln();

}

// A4縦指定
$pdf=new HeaderFooterExtended('P','mm','A4');
$pdf->AddMBFont('MS-Gothic', 'SJIS');
$pdf->AddMBFont('CODE39', 'SJIS');
//$pdf->SetAuthor('生産管理システム');
$pdf->AddPage();

$x_pos = 20;
$y_pos = 15;

// CODE39 12
$x2 = 20;
$y2 = 25;
$pdf->SetXY($x2, $y2);
$disp0 = 'AEIA1510R05';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14, 'CODE39 12', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 12);
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x2 = 50;
$y2 = $y2 + 7;
$pdf->SetXY($x2, $y2);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

// CODE39 14
$x2 = 20;
$y2 = 45;
$pdf->SetXY($x2, $y2);
$disp0 = 'AEIA1510R05';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14, 'CODE39 14', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 14);
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x2 = 50;
$y2 = $y2 + 7;
$pdf->SetXY($x2, $y2);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

// CODE39 16
$x2 = 20;
$y2 = 65;
$pdf->SetXY($x2, $y2);
$disp0 = 'AEIA1510R05';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14, 'CODE39 16', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 16);
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x2 = 50;
$y2 = $y2 + 7;
$pdf->SetXY($x2, $y2);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

// CODE39 18
$x2 = 20;
$y2 = 85;
$pdf->SetXY($x2, $y2);
$disp0 = 'AEIA1510R05';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14, 'CODE39 18', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 18);
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x2 = 50;
$y2 = $y2 + 7;
$pdf->SetXY($x2, $y2);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

// CODE39 20
$x2 = 20;
$y2 = 105;
$pdf->SetXY($x2, $y2);
$disp0 = 'AEIA1510R05';
$disp1 = Modulus43_Work($disp0);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,14, 'CODE39 20', 1, 0, "C", 0);
$pdf->Cell(50,7, $disp0, 1, 0, "C", 0);

$pdf->SetFont('CODE39', '', 20);
$pdf->Cell(95,14, $disp1, 1, 0, "C", 0);

$pdf->SetFont('MS-Gothic', '', 12);
$x2 = 50;
$y2 = $y2 + 7;
$pdf->SetXY($x2, $y2);
$pdf->Cell(50,7,$disp1, 1, 0, "C", 0);
$pdf->Ln();

$pdf->AliasNbPages();
$pdf->Output();

?>
