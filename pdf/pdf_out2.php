<?php
/*

 SMT�����ײ襷���ƥ� �ݥ���ȥե� ��Ͽ��ȼ԰��� ����

 2007/04/25

 2007/05/09
 [product] -> [product_smt]���ѹ�

 2007/05/22
 �С������ɥե����[CODE39.ttf]�������߲�
 �ե���ȥ�ȥ�å��ե����롢�ե��������ե����������塢AddMBFont
 �Ǥ�̵�� AddFont �ǥե���Ȥ���Ͽ

 ¾��MS�����å�����Ѥ��Ƥ��뤬�����饤����Ȥ�Windows�ʤ�ǥե���Ȥǥե����
 �����äƤ���Ϥ��ʤΤǥС������ɥե���ȤΤ������ߤȤ���

 2007/07/05 [��2��]
 �����å��ǥ��å��ѻ�
 �����å��ǥ��å�̵���ξ����300��ʸ����1ʸ���θ��ɤȤ����ǡ��������ꡢ�ܸۤ���
 ��̵����Ƚ�� ¾�ΥС�������(¾����ɼ�ʤ�)�ȹ�碌�ƥ����å��ǥ��åȤ��ѻߤ���
 (�����å��ǥ��å�ͭ��ξ�����1��4,900��ʸ����1ʸ���θ���)

 2007/07/09 [��3��]
 �¹Ժ���Ѥ�ͽ���Ұ��ֹ��б� �Ұ��ֹ����Ƭ��[A]�Ȥ���


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
		pdf_Header($this, '�ݥ���ȥե����� ��ȼ԰���[��3��]');
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

$pdf->SetFont('MS-Gothic', '', 12);
$x_pos = 20;
$y_pos = 30;

// �С���������ɽ��

// DB��³
$mdb2 = db_connect();

$x_pos = 20; 
$y_pos = 30;

//$res_query = $mdb2->query('SELECT * FROM worker ORDER BY worker_id');
$res_query = $mdb2->query("SELECT * FROM worker WHERE rem_worker='IMT��ȼ�' ORDER BY worker_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	//
	pdf_Worker_Cell($pdf, $x_pos, $y_pos, $row['worker_no'], $row['worker_name']);

	if ($x_pos==20) {
		
		$x_pos = 115;
		$y_pos = $y_pos;
		
	} elseif ($x_pos==115) {
		
		$x_pos = 20;
		$y_pos = $y_pos + 30;
		
	}

	if ($y_pos>=280) {
		
		$pdf->AddPage();
		
		$x_pos = 20; 
		$y_pos = 30;
		
	}
	
}

$pdf->AddPage();
$x_pos = 20; 
$y_pos = 30;

$res_query = $mdb2->query("SELECT * FROM worker WHERE rem_worker='IMT��� ͽ��' ORDER BY worker_id");
$res_query = err_check($res_query);

while($row = $res_query->fetchRow(MDB2_FETCHMODE_ASSOC)) {

	//
	pdf_Worker_Cell($pdf, $x_pos, $y_pos, $row['worker_no'], $row['worker_name']);

	if ($x_pos==20) {
		
		$x_pos = 115;
		$y_pos = $y_pos;
		
	} elseif ($x_pos==115) {
		
		$x_pos = 20;
		$y_pos = $y_pos + 30;
		
	}

	if ($y_pos>=280) {
		
		$pdf->AddPage();
		
		$x_pos = 20; 
		$y_pos = 30;
		
	}
	
}

// DB����
db_disconnect($mdb2);

$pdf->AliasNbPages();
$pdf->Output();

?>
