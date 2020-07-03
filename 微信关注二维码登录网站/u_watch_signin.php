<?php
if (!defined('BY')) {
    exit('Access Denied');
}
$title = $sys_webname . "-微信登录";
require "{$app}/src/header.php";
?>
    <div class="wp">
        <div class="flex justify-center align-center pt100">
            <div class="padding"
                 style="background: url(/public/static/default/images/loading_1c96706b.gif) no-repeat center; background-size: auto auto;">
                <img class="wechat__qrcode" alt="" style="display: none" height="300"/>
                <div class="text-gray padding-top-xs text-center text-sm">请扫码关注“珍乳网公众号”进行注册</div>
            </div>
        </div>
    </div>
    <script>
        var checkOrder = function () {
            $.post("/api?c=watchat&a=uwx_check_login", function (data) {
                if (data['code'] === 10000) {
                    window.location.href = data['data'];
                }
            });
        }

        $.get("/api?c=watchat&a=uwxGetOauth_signin", function (data) {
            console.log(data)
            if (data['code'] === 0) {
                $(".wechat__qrcode").attr({"src": data['data']}).show();
                setInterval(function () {
                    checkOrder()
                }, 3000);
            } else {
                layer.msg(data['message']);
            }
        })
    </script>

<?php
require "{$app}/src/footer.php";