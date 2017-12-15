<!--
watch2.php

時計表示(javascript版)

2006/10

-->
<SCRIPT type="text/javascript"><!--

//時計周りの余白(上右下左)
marg="10 0 10 0";

//文字色(色名[forestgreenなど]、16進数でも指定可能)
//fontc="#228B22";
fontc="#191917";

//文字サイズ
//fonts="10px";
fonts="14px";

//文字間隔
lette="0.15em";

//文字の位置(左)left(中央)center(右)right
texta="center";

// 背景色(色名、16進数[#FFFFFFなど]でも指定可能)
backc="#ffffff";

// 背景画像
backi="../graphics/seisan_banner2.png";

// 枠線の色、太さ、スタイル(なし)none(実線)solid(二重線)double(破線)dashed(点線)dottedなど
//borde="darkseagreen 1px dashed";
//borde="none";
borde="darkblue 1px solid";

function Watch2() {
	now = new Date();
	year = now.getYear();
	month = now.getMonth()+1;
	day = now.getDate();
	youbi = new Array("(日)","(月)","(火)","(水)","(木)","(金)","(土)");
	hour = now.getHours();
	minute = now.getMinutes();
	second = now.getSeconds();
	if (year < 1000) { year += 1900 }
	if (hour < 10) { hour = '0' + hour }
	if (minute < 10) { minute = '0' + minute }
	if (second < 10) { second = '0' + second }
	//document.watch.disp.value =year + '年' + month + '月' + day + '日' + youbi[now.getDay()] +  hour + ':' + minute + ':' + second;
	document.watch.disp.value =hour + ':' + minute + ':' + second;
	setTimeout('Watch2()',1000);
}

document.write("<form name=watch style='margin:"+marg+"'>");
document.write("<input type=text name=disp size=15 style='vertical-align:middle;font-weight:bold;");
document.write("font-size:"+fonts+";letter-spacing:"+lette+";border:"+borde+";text-align:"+texta+";");
document.write("color:"+fontc+";background-color:"+backc+";background-image:url("+backi+");'>");
document.write("</form>");

Watch2();

// --></SCRIPT>
