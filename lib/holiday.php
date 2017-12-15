<?php

// 平成20年(2008年) 日東通信機カレンダー
// 2007-12-05
// 2008-02-25 平成20年(2008年) 4月から12月
// 2008-10-23 休日変更
// 2008-12-02 平成21年(2009年)1潤3月対応
// 2009-03-17 平成21年(2009年)4潤12月対応
// 2009-05-15 振替休日対応(帰休日は部署により違うので対応しない)
// 2009-08-03 出勤日変更 対応
// 2009-11-16 出勤日変更対応
// 2009-11-30 帰休日対応
// 2009-12-01 2010年対応
// 2009-12-24 2010-01分の帰休追加
// 2010-03-04 平成22年(2010年04月潤12月対応)
// 2010-11-01 11月の休日対応
// 2010-12-14 PHP5.3 エラー対応 全日をまず通常日に設定して休日を上書き
// 2011-02-03 2011年3月の休日修正
// 2011-03-10 平成23年(2011年) 4月-8月対応
// 2011-03-25 3月の振替対応
// 2011-07-06
// 2011-08-30
// 2011-12-06 2011年1月の休日修正
// 2012-01-11 2012年3月までの休日修正
// 2012-01-16 2012年2月の祝日修正
// 2012-04-04 2012年4月潤対応
// 2012-08-07 夏休み変更対応
// 2012-09-26 2012年10月潤対応
// 2013-03-14 2013年04月潤対応
// 2013-10-17 2013年11月 振替休日追加
// 2014-02-21 2014年03月対応
// 2014-03-17 2014年04月潤対応
// 2014-03-19 2014年03月まで修正していたので元に戻す
// 2015-08-06 2016年03月まで修正
// 2016-03-01 2016年04月潤対応
// 2017-02-01 2017年03月対応
// 2017-03-06 2017年04月対応
// 2017-03-16 2017年04月潤2018年02月対応
// 2017-08-08 2017年08月 稼働日変更
// 2017-09-01 2017年10月修正
// 2017-10-04 2017年10月修正
// 2017-12-08 冬休み修正

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
