<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @author Siyuan! <siyuanjunr@qq.com>
 * Name: 微信回调信息处理控制器
 */

class wechatCallbackapiController
{
    private $appid = "";
    private $appsecret = "";

    //基本配置>服务器配置(已启用)>令牌(Token)
    private $token = "";

    public function __construct()
    {
        global $conf;

        $this->appid = $conf['watch_fuwuhao_appid'];
        $this->appsecret = $conf['watch_fuwuhao_secret'];
        $this->token = $conf['watch_fuwuhao_token'];
    }

    public function valid()
    {
        $echoStr = getapk("echostr");
        if ($echoStr) {
            if ($this->checkSignature()) { //验证通过
                file_put_contents('access_token.txt', "jj.{$echoStr}." . date("Y-m-d H:i:s"));
                echo $echoStr;
            } else {
                file_put_contents('access_token.txt', "dd." . date("Y-m-d H:i:s"));
            }
        } else {
            $this->responseMsg();
            exit;
        }
    }

    //确认授权后回调获取用户信息
    public function responseMsg()
    {
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)) {
            //转换形式良好的 XML 字符串为 SimpleXMLElement 对象，然后输出对象的键和元素
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($postObj) {
                $scene_id = str_replace("qrscene_", "", $postObj->EventKey);

                $openid = $postObj->FromUserName; //openid
                $Event = strtolower($postObj->Event);
                $is_first = -1;
                if ($Event == 'subscribe') { //首次关注
                    //插入表
                    $is_first = 0;
                } else if ($Event == 'scan') { //已关注
                    $is_first = 1;
                }

                $access_token = $this->getAccessToken();
                $userinfo = $this->getUserinfo($openid, $access_token);

                if ($userinfo) {
                    $userinfo['scene_id'] = $scene_id;
                    $userinfo['is_first'] = $is_first;
                    $this->userDataSave($userinfo);
                } else {
                    $filedata = getip() . " - 微信扫码回调获取用户信息失败 - " . date("Y-m-d H:i:s") . "\r\n";
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/public/temp/log/wxuserinfo.log", $filedata, FILE_APPEND);
                }
            }
        } else {
            Api_Info_Json(0, "咋不说哈呢");
        }
    }

    //存入用户数据
    private function userDataSave($userinfo)
    {
        global $db;

        $tbl = "user_qrcode";
        $tblus = "us";
        $data = data($tbl, "id", $userinfo['scene_id']);
        if ($data['id']) {
            $time = time();
            $formdata = getformdata($tbl, $userinfo['scene_id'], "addtime", false);
            $formdata['openid'] = $userinfo['openid'];
            $formdata['logintime'] = $time;
            $formdata['is_first'] = $userinfo['is_first'];
            $formdata['nickname'] = $userinfo['nickname'];
            $formdata['avatar'] = $userinfo['headimgurl'];
            $formdata['sex'] = $userinfo['sex'];
            $formdata['province'] = $userinfo['province'];
            $formdata['city'] = $userinfo['city'];
            $formdata['country'] = $userinfo['country'];
            saveformdata($formdata, $tbl);

            //检测[user_qrcode]是否已有当前用户信息
            $data = data($tblus, "wx_user_qrcode_openid", $userinfo['openid']);
            if (empty($data['id'])) {
                $formdata = getformdata($tblus);
                $formdata['wx_user_qrcode_openid'] = $userinfo['openid'];
                $formdata['createtime'] = $time;
                $formdata['lastlogintime'] = $time;
                saveformdata($formdata, $tblus);
            } else {
                $db->q("update #@__{$tblus} set lastlogintime='{$time}' where id='{$data['id']}'");
            }
        }
    }

    private function getUserinfo($openid, $access_token)
    {
        if ($access_token && $openid) {
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
            $userinfo = curlGet($url);
            $userinfo = json_decode($userinfo, true);
            return $userinfo;
        }
    }

    public function getAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
        $result = curlGet($url);
        $access_tokens = json_decode($result, true);
        $access_token = $access_tokens['access_token'];
        return $access_token;
    }

    private function checkSignature()
    {
        $signature = getapk("signature");
        $timestamp = getapk("timestamp");
        $nonce = getapk("nonce");

        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}