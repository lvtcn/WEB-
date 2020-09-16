<?php
/******************************************
 ****AuThor:2039750417@qq.com
 ****Title :微信公众号配置
 *******************************************/

class WxOaController
{
    private $wx_appid = "wxd544371ba72dbb9a";
    private $wx_appsecret = "ed2fdf540e62bb003f1088dfda332f13";
    private $wx_redirect_uri = "http://glyj.t.lvtcn.com/api?c=WxOa&a=callLogin";
    private $access_token;

    // 获取 oauth2Accesstoken
    private function oauth2Access($code)
    {
        if ($code) {
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wx_appid}&secret={$this->wx_appsecret}&code={$code}&grant_type=authorization_code";
            $result = json_decode(http_curl_request($url));
//            dd($result);
            if (empty($result->errcode)) {
                return $result;
            } else {
                Api_Info_Json($result->errcode, $result->errmsg);
            }
        } else {
            Api_Info_Json(0, "没有找到 login code");
        }
    }

    // 获取 accesstoken
    private function toAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->wx_appid}&secret={$this->wx_appsecret}";
        $result = json_decode(http_curl_request($url));
        if (empty($result->errcode)) {
            $this->access_token = $result->access_token;
        } else {
            Api_Info_Json($result->errcode, $result->errmsg);
        }
    }

    // 登录验证
    public function api_caklogin()
    {
        $this->wx_redirect_uri = urlencode($this->wx_redirect_uri);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->wx_appid}&redirect_uri={$this->wx_redirect_uri}&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
        $uval = dbvget("wxus", "openid", "token='" . gtc('loginid') . "'");
        if ($uval) {
            Api_Info_Json(1, "已登录");
        } else {
            Api_Info_Json(0, "未登录", $url);
        }
    }

    // 回调登录
    public function callLogin()
    {
        $tbl = "wxus";
        $code = strget("code");
        $result = $this->oauth2Access($code);
        $openid = $result->openid;
        $val = dbvget($tbl, "token", "openid='{$openid}'");
        if (empty($val)) {
            $token = gettoken($openid);
            $formdata = getformdata($tbl, 0);
            $formdata['token'] = $token;
            $formdata['openid'] = $openid;
            $formdata['createtime'] = time();
            saveformdata($formdata, $tbl);
        } else {
            $token = $val;
        }
        stc('loginid', $token);
        go("/");
    }

    // 调取发布消息通道
    public function sendTempWxmass($openid = '', $keyword2 = '', $remark = '')
    {
        $openid = strget("openid");
        $keyword2 = strget("keyword2");
        $remark = strget("remark");
        $datakeyword = [
            'first' => ['value' => '恭喜您兑换成功 . 感谢支持 ！', 'color' => '#173177'],
            'keyword1' => ['value' => "兑换成功", 'color' => '#173177'],
            'keyword2' => ['value' => $keyword2, 'color' => '#173177'],
            'remark' => ['value' => '订单编号：' . $remark, 'color' => '#173177'],
        ];
        return $this->toWxMassage('lingqumes', $openid, $datakeyword);
    }

    // 发起消息推送
    public function toWxMassage($dataType = 'lingqumes', $touser = 'oMPQywRL_vdWWLsw0FUauPHBjS48', $datakeyword, $page = '')
    {
        $page = $page ? $page : $GLOBALS['conf']['weburl'];
        $this->toAccessToken();
        $dataMassType = $this->dataMassType($dataType, $datakeyword);
        $pramData = [
            'touser' => $touser, // 要发送的用户openid
            'template_id' => $dataMassType['template_id'], // 消息模板id
            'url' => $page, // 点击跳转页面地址
            'data' => $dataMassType['data'], // 消息模板内容
        ];
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->access_token}";
        $result = json_decode(http_curl_request($url, json_encode($pramData), 1));
        file_put_contents("towsmassagedata.txt", $touser . ':' . json_encode($result), FILE_APPEND);
        if (empty($result->errcode)) {
            return $result;
        } else {
            // 容易报错，没有关注的用户
//            Api_Info_Json($result->errcode, $result->errmsg);
        }
    }

    // 消息模板处理
    private function dataMassType($dataType, $datakeyword)
    {
        $arr = [];
        switch ($dataType) {
            case 'lingqumes':
                $data = [];
                $template_id = "eD9cntiM_mgc_H281TcShysbRaADWnDIFVse8vl1lwg";
                break;
            default:
                $data = [];
                $template_id = '';
                break;
        }
        $arr['data'] = !empty($data) ? array_merge($data, $datakeyword) : $datakeyword;
        $arr['template_id'] = $template_id;
        return $arr;
    }

    /**
     * 获取jssdk签名
     */
    public function signature()
    {
        $this->toAccessToken();

        //随机字符串
        $str = time() . rand(111111, 999999);
        $nonce_str = substr(md5($str), 5, 8);
        $timestamp = time(); //当前时间戳
        $weburl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        // 获取微信 jsapi_ticket;
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$this->access_token}&type=jsapi";
        $result = json_decode(http_curl_request($url));
        $jsapi_ticket = $result->ticket;

        // signature 签名流程
        $data = [
            'jsapi_ticket' => $jsapi_ticket,
            'noncestr' => $nonce_str,
            'timestamp' => $timestamp,
            'url' => $weburl
        ];
        $param = "";
        foreach ($data as $k => $v) {
            $param .= $k . '=' . $v . '&';
        }
        $p = rtrim($param, '&');
        $signature = sha1($p);

        // 前端所需js参数
        $response['appId'] = $this->wx_appid;
        $response['nonceStr'] = $nonce_str;
        $response['timestamp'] = $timestamp;
        $response['url'] = $weburl;
        $response['signature'] = $signature;
        $response['rawString'] = $p;
        return json_encode($response);
    }
}