<?php
if (!defined('BY')) {
    die('Access Denied');
}
require $_SERVER['DOCUMENT_ROOT'] . "/app/Http/Controllers/home/WxOaController.php";
$wxJdk = new WxOaController;
$wxconfig = $wxJdk->signature();
?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script>
    new WOW().init();

    var wxjson = <?php echo $wxconfig; ?>;
    console.log(wxjson);
    wxjson.debug = false;
    wxjson.jsApiList = [
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone'
    ]
    wx.config(wxjson);
    wx.ready(function () {
        var options = {
            title: '趣客社区',
            link: 'http://www.lvtcn.com/',
            imgUrl: 'http://www.lvtcn.com/static/home/images/logo.png',
            desc: '趣客是一个分享趣生活动态的社区平台， 你可以在这么了解你所希望的生活愿景， 做生活的倾听者。',
            success: function (res) {
                layer.open({
                    content: '已分享',
                    btn: '确认',
                })
            },
            cancel: function () {
                layer.open({
                    content: '分享取消',
                    btn: '确认',
                })
            }
        }
        wx.onMenuShareTimeline(options);
        wx.onMenuShareAppMessage(options);
        wx.onMenuShareQQ(options);
        wx.onMenuShareWeibo(options);
        wx.onMenuShareQZone(options);
    });

    function onBridgeReady(data) {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', data,
            function (res) {
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                    layer.open({
                        content: '恭喜您，支付成功！ 5s 待跳转...',
                        btn: '确认',
                        success: function (ok) {
                            setTimeout(function () {
                                window.location.href = '/';
                            }, 5000);
                        }
                    })
                }
            }
        )
    }

    // if (typeof WeixinJSBridge == "undefined") {
    //     if (document.addEventListener) {
    //         document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    //     } else if (document.attachEvent) {
    //         document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
    //         document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    //     }
    // }

    $(document).on("click", ".paysubmit", function () {
        _btn = $(this)
        _ishas = _btn.hasClass("disabled");
        if (_ishas) {
            return;
        }
        _frmid = _btn.attr("frmid");
        _action = _btn.attr("action");
        _btn.attr("disabled", "disabled");
        _btn.addClass("disabled");
        layer.open({type: 2});
        $.ajax({
            type: "POST",
            url: _action,
            data: $('#' + _frmid).serialize(),
            datatype: "html",
            success: function (data) {
                var models = data;
                layer.closeAll();
                layer.open({
                    content: models.msg,
                    skin: 'msg',
                    time: 2,
                    success: function () {
                        if (models.url) {
                            setTimeout(function () {
                                window.location.href = models.url;
                            }, 1000);
                        }
                        if (models.code === 1) {
                            onBridgeReady(models.resources);
                        } else {

                        }
                        _btn.removeAttr("disabled")
                        _btn.removeClass("disabled")
                    }
                })
            }
        });
        return false
    })

    $(function () {
        $.ajax({
            type: "GET",
            url: '/api?c=wxOa&a=api_caklogin',
            data: {},
            datatype: "html",
            success: function (data) {
                if (data.code === 0) {
                    window.location.href = data.resources;
                }
            }
        })
    })
</script>