//寫cookies函數
function SetCookie(name,value)//兩個參數，一個是cookie的名字，一個是值
{
    var Days = 30; //此 cookie 將被保存30天
    var exp  = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name)//取cookies函數
{
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
     if(arr != null) return unescape(arr[2]); return null;

}
function delCookie(name)//刪除cookie
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}


//示例
//SetCookie ("xiaoqi", "3")
//alert(getCookie('xiaoqi'));


//URL字符串操作
function request(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

//為系統追加replaceAll方法
function replaceAll(oldStr, newStr) {
    return this.replace(new RegExp(oldStr,"gm"),newStr);
}
