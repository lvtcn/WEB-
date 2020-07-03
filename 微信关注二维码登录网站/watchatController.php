<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @author Siyuan! <siyuanjunr@qq.com>
 * Name: 微信管理控制器
 */

class watchatController
{
    /*
     * 1 第一步：创建当前公众号带参数二维码
     * 2 第二步：配置用户扫码回调，同时存储关注用户信息
     * */

    private $appid = "";
    private $appsecret = "";

    public function __construct()
    {
        global $conf;

        $this->appid = $conf['watch_fuwuhao_appid'];
        $this->appsecret = $conf['watch_fuwuhao_secret'];
    }

    // 微信公众号服务器地址(URL)配置方法地址 不可更改
    // http://zrw.t.lvtcn.com/api?c=watchat&a=uwxGetOauth_valid
    public function uwxGetOauth_valid()
    {
        //回调处理验证类
//        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/public/temp/log/wxuserinfo.log", "扫码回调", FILE_APPEND);
        include_once "wechatCallbackapiController.php";
        $wechatObj = new wechatCallbackapiController();
        $wechatObj->valid();
    }

    //获取登陆二维码
    public function uwxGetOauth_signin()
    {
        $tbl = "user_qrcode";
        $formdata = getformdata($tbl);
        $formdata['addtime'] = time();
        $returnid = saveformdata($formdata, $tbl);
        $scene_id = $returnid;
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
        $access_token_array = json_decode(curlGet($url), true);
        $access_token = $access_token_array['access_token'];
        //echo $access_token;exit;http://www.sucaihuo.com/project/wxvalid/index.php
        $qrcode_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;

        $post_data = array();
        $post_data['expire_seconds'] = 3600 * 24; //有效时间
        $post_data['action_name'] = 'QR_SCENE';
        $post_data['action_info']['scene']['scene_id'] = $scene_id; //传参二维码主键id，微信端可获取
        $json = curlPost($qrcode_url, json_encode($post_data));
        if (!$json['errcode']) {
            $ticket = $json['ticket'];
            $ticket_img = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
            stc("wuid_" . __cookieenlogin__, page_encode($scene_id));
            Api_Info_Json($json['errcode'], "获取成功", $ticket_img);
        } else {
            $return = '发生错误：错误代码 ' . $json['errcode'] . '，微信错误信息：' . $json['errmsg'];
            Api_Info_Json($json['errcode'], $return);
        }
    }

    //是否关注登录检测
    public function uwx_check_login()
    {
        $tbl = "user_qrcode";
        $wuid = page_decode(gtc("wuid_" . __cookieenlogin__));
        if ($wuid) {
            $openid = dbvget($tbl, "openid", "id='{$wuid}'");
            if ($openid) {
                $udata = data("us", "wx_user_qrcode_openid", "{$openid}");
                if ($udata['id']) {
                    if(empty($udata['uname'])) {
                        $url = "/user/u_phone_signin";
                    } else {
                        $url = "/";
                    }
                    stc(__cookieenlogin__, page_encode($udata['id']));
                    stc("wuid_" . __cookieenlogin__, 0);
                    Api_Info_Json(10000, '', $url);
                } else {
                    Api_Info_Json(0);
                }
            } else {
                Api_Info_Json(0, '打开手机操作呀，看这个干哈:)');
            }
        }
    }
}