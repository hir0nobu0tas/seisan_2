<?php

// ʿ��20ǯ(2008ǯ) �����̿�����������
// 2007-12-05
// 2008-02-25 ʿ��20ǯ(2008ǯ) 4���12��
// 2008-10-23 �����ѹ�
// 2008-12-02 ʿ��21ǯ(2009ǯ)1��3���б�
// 2009-03-17 ʿ��21ǯ(2009ǯ)4��12���б�
// 2009-05-15 ���ص����б�(������������ˤ��㤦�Τ��б����ʤ�)
// 2009-08-03 �ж����ѹ� �б�
// 2009-11-16 �ж����ѹ��б�
// 2009-11-30 �������б�
// 2009-12-01 2010ǯ�б�
// 2009-12-24 2010-01ʬ�ε����ɲ�
// 2010-03-04 ʿ��22ǯ(2010ǯ04���12���б�)
// 2010-11-01 11��ε����б�
// 2010-12-14 PHP5.3 ���顼�б� ������ޤ��̾��������ꤷ�Ƶ�������
// 2011-02-03 2011ǯ3��ε�������
// 2011-03-10 ʿ��23ǯ(2011ǯ) 4��-8���б�
// 2011-03-25 3��ο����б�
// 2011-07-06
// 2011-08-30
// 2011-12-06 2011ǯ1��ε�������
// 2012-01-11 2012ǯ3��ޤǤε�������
// 2012-01-16 2012ǯ2��ν�������
// 2012-04-04 2012ǯ4����б�
// 2012-08-07 �Ƶ٤��ѹ��б�
// 2012-09-26 2012ǯ10����б�
// 2013-03-14 2013ǯ04����б�
// 2013-10-17 2013ǯ11�� ���ص����ɲ�
// 2014-02-21 2014ǯ03���б�
// 2014-03-17 2014ǯ04����б�
// 2014-03-19 2014ǯ03��ޤǽ������Ƥ����ΤǸ����᤹
// 2015-08-06 2016ǯ03��ޤǽ���
// 2016-03-01 2016ǯ04����б�
// 2017-02-01 2017ǯ03���б�
// 2017-03-06 2017ǯ04���б�
// 2017-03-16 2017ǯ04���2018ǯ02���б�
// 2017-08-08 2017ǯ08�� ��Ư���ѹ�
// 2017-09-01 2017ǯ10���
// 2017-10-04 2017ǯ10���
// 2017-12-08 �ߵ٤߽���

switch ($month) {
case 1:
	// 2018-01
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[1] = 1;
	$this->holiday[2] = 1;
	$this->holiday[3] = 1;
	$this->holiday[4] = 1;
	$this->holiday[5] = 1;
	$this->holiday[6] = 1;
	$this->holiday[7] = 1;
	$this->holiday[8] = 1;
	$this->holiday[13] = 1;
	$this->holiday[14] = 1;
	$this->holiday[20] = 1;
	$this->holiday[21] = 1;
	$this->holiday[27] = 1;
	$this->holiday[28] = 1;
	break;

case 2:
	// 2018-02
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[3] = 1;
	$this->holiday[4] = 1;
	$this->holiday[10] = 1;
	$this->holiday[11] = 1;
	$this->holiday[12] = 1;
	$this->holiday[17] = 1;
	$this->holiday[18] = 1;
	$this->holiday[24] = 1;
	$this->holiday[25] = 1;
	break;

case 3:
	// 2017-03
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[4] = 1;
	$this->holiday[5] = 1;
	// $this->holiday[8] = 1;
	$this->holiday[11] = 1;
	$this->holiday[12] = 1;
	$this->holiday[18] = 1;
	$this->holiday[19] = 1;
	$this->holiday[20] = 1;
	$this->holiday[25] = 1;
	$this->holiday[26] = 1;
	break;

case 4:
	// 2017-04
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[1] = 1;
	$this->holiday[2] = 1;
	$this->holiday[8] = 1;
	$this->holiday[9] = 1;
	$this->holiday[15] = 1;
	$this->holiday[16] = 1;
	$this->holiday[22] = 1;
	$this->holiday[23] = 1;
	$this->holiday[29] = 1;
	$this->holiday[30] = 1;
	break;

case 5:
	// 2017-05
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[1] = 1;
	$this->holiday[2] = 1;
	$this->holiday[3] = 1;
	$this->holiday[4] = 1;
	$this->holiday[5] = 1;
	$this->holiday[6] = 1;
	$this->holiday[7] = 1;
	$this->holiday[13] = 1;
	$this->holiday[14] = 1;
	$this->holiday[20] = 1;
	$this->holiday[21] = 1;
	$this->holiday[27] = 1;
	$this->holiday[28] = 1;
	break;

case 6:
	// 2017-06
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[3] = 1;
	$this->holiday[4] = 1;
	$this->holiday[10] = 1;
	$this->holiday[11] = 1;
	$this->holiday[17] = 1;
	$this->holiday[18] = 1;
	$this->holiday[24] = 1;
	$this->holiday[25] = 1;
	break;

case 7:
	// 2017-07
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[1] = 1;
	$this->holiday[2] = 1;
	$this->holiday[8] = 1;
	$this->holiday[9] = 1;
	$this->holiday[15] = 1;
	$this->holiday[16] = 1;
	$this->holiday[17] = 1;
	$this->holiday[22] = 1;
	$this->holiday[23] = 1;
	$this->holiday[29] = 1;
	$this->holiday[30] = 1;
	break;

case 8:
	// 2017-08
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[5] = 1;
	$this->holiday[6] = 1;
	$this->holiday[12] = 1;
	$this->holiday[13] = 1;
	$this->holiday[14] = 1;
	$this->holiday[15] = 1;
	$this->holiday[16] = 1;
	$this->holiday[17] = 1;
	$this->holiday[18] = 1;
	$this->holiday[19] = 1;
	$this->holiday[20] = 1;
	$this->holiday[26] = 1;
	$this->holiday[27] = 1;
	break;

case 9:
	// 2017-09
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[2] = 1;
	$this->holiday[3] = 1;
	$this->holiday[9] = 1;
	$this->holiday[10] = 1;
	$this->holiday[16] = 1;
	$this->holiday[17] = 1;
	$this->holiday[18] = 1;
	$this->holiday[23] = 1;
	$this->holiday[24] = 1;
	$this->holiday[30] = 1;
	break;

case 10:
	// 2017-10
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[1] = 1;
	$this->holiday[7] = 1;
	$this->holiday[8] = 1;
	$this->holiday[9] = 1;
	$this->holiday[14] = 1;
	$this->holiday[15] = 1;
	$this->holiday[21] = 1;
	$this->holiday[22] = 1;
	$this->holiday[28] = 1;
	$this->holiday[29] = 1;
	break;

case 11:
	// 2017-11
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[3] = 1;
	$this->holiday[4] = 1;
	$this->holiday[5] = 1;
	$this->holiday[11] = 1;
	$this->holiday[12] = 1;
	$this->holiday[18] = 1;
	$this->holiday[19] = 1;
	$this->holiday[23] = 1;
	$this->holiday[24] = 1;
	$this->holiday[25] = 1;
	$this->holiday[26] = 1;
	break;

case 12:
	// 2017-12
	for ($i=1; $i<=31; $i++) {
		$this->holiday[$i] = 0;
	}

	$this->holiday[2] = 1;
	$this->holiday[3] = 1;
	$this->holiday[9] = 1;
	$this->holiday[10] = 1;
	$this->holiday[16] = 1;
	$this->holiday[17] = 1;
	$this->holiday[23] = 1;
	$this->holiday[24] = 1;
	$this->holiday[29] = 1;
	$this->holiday[30] = 1;
	$this->holiday[31] = 1;
	break;

}

?>
