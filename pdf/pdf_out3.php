<?php
/*

 SMT�����ײ襷���ƥ� �ݥ���ȥե� ���楳���� ����

 2007/05/23

 
 */
//---------------------------------------------------------------
// �������
//---------------------------------------------------------------
// ��ͭ�ؿ�
include '../inc/parameter_inc.php';
include '../lib/com_func1.php';
include '../lib/modulus43.php';
include '../lib/pdf_func.php';

// PEAR�饤�֥��
require_once 'HTML/QuickForm.php';
require_once 'MDB2.php';

// MBFPDF���ɤ߹��ߤȳ�ĥ���饹���
define('FPDF_FONTPATH','../lib/fpdf/font/');
require_once '../lib/fpdf/fpdf.php';
require_once '../lib/fpdf/mbfpdf.php';
$EUC2SJIS = true;	// EUC-JP����

//---------------------------------------------------------------
	
// �إå���&�եå�����ɽ��
class HeaderFooterExtended extends MBFPDF {
	function Header() {
		pdf_Header($this, '�ݥ���ȥե����� ���楳����');
	}
//	function Footer() {
//		pdf_Footer($this);
//	}
}

// A4�Ļ���
$pdf=new HeaderFooterExtended('P','mm','A4');
$pdf->AddMBFont('MS-Gothic', 'SJIS');
//$pdf->AddMBFont('CODE39', 'SJIS');
$pdf->AddFont('CODE39', '', 'CODE39.php');
$pdf->SetTitle('Worker of IMT');
$pdf->SetAutoPageBreak(false);	// ��ư���ڡ����ػ�
$pdf->AddPage();

$pdf->SetXY(30, 30);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,10, 'ENTER', 1, 0, "C", 0);
$pdf->Cell(30,10, Modulus43_Disp('0D'), 1, 0, "C", 0);
	
$pdf->SetXY(30, 40);
	
$pdf->SetFont('CODE39', '', 20);
$pdf->Cell(60,15, Modulus43_Code('0D'), 1, 0, "C", 0);

$pdf->SetXY(100, 30);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,10, 'TAB', 1, 0, "C", 0);
$pdf->Cell(30,10, Modulus43_Disp('09'), 1, 0, "C", 0);
	
$pdf->SetXY(100, 40);
	
$pdf->SetFont('CODE39', '', 20);
$pdf->Cell(60,15, Modulus43_Code('09'), 1, 0, "C", 0);
$pdf->Ln();


$pdf->SetXY(30, 70);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,10, 'INSERT', 1, 0, "C", 0);
$pdf->Cell(30,10, Modulus43_Disp('0E'), 1, 0, "C", 0);
	
$pdf->SetXY(30, 80);
	
$pdf->SetFont('CODE39', '', 20);
$pdf->Cell(60,15, Modulus43_Code('0E'), 1, 0, "C", 0);

$pdf->SetXY(100, 70);

$pdf->SetFont('MS-Gothic', '', 12);
$pdf->Cell(30,10, 'DELETE', 1, 0, "C", 0);
$pdf->Cell(30,10, Modulus43_Disp('0F'), 1, 0, "C", 0);
	
$pdf->SetXY(100, 80);
	
$pdf->SetFont('CODE39', '', 20);
$pdf->Cell(60,15, Modulus43_Code('0F'), 1, 0, "C", 0);
$pdf->Ln();

$pdf->AliasNbPages();
$pdf->Output();

?>
