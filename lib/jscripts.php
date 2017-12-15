<script language="JavaScript1.1">
<!--
function formConfirm(type) {
	if (type == "update") {
    	rtn = confirm("データを変更します。\nよろしいですか？");
  	} else {
    	rtn = confirm("データを削除します。\nよろしいですか？");
  	}

	if (rtn) {
    	return true;
  	}
  	return false;
}

function clock(){
	setTimeout("clock()",1000);
	datec = new Date();
	y = datec.getYear();
	M = datec.getMonth()+1;
	d = datec.getDate();
	h = datec.getHours();
	m = datec.getMinutes();
	s = datec.getSeconds();
	if (h < 10) h = "0" + h;
	if (m < 10) m = "0" + m;
	if (s < 10) s = "0" + s;
	if (y < 2000) y += 1900;
	time = y + "/" + M + "/" + d + "/" + h + ":" + m + ":" + s + ":";
	document.timeform.timetext.value = time;
}

//-->
</script>
