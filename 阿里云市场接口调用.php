function json($strjson)
{
    exit(json_encode($strjson));
}

function jsonecho($status, $msg = '', $code = '', $url = '', $calback = '')
{
    $msg = array(
        "status" => $status,
        "msg" => $msg,
        "code" => $code,
        "callback" => $calback,
        "url" => $url
    );
    json($msg);
}

function curlGet($url, $headers = [])
{
    $ch = curl_init();
    $headers[] = 'Accept-Charset:utf-8';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// ip地址獲取
function ipAddrGet($number = 0)
{
    $url = "https://jisuip.market.alicloudapi.com/ip/location";
    $data = [
        'ip' => getip(),
    ];
    $data = http_build_query($data);
    $url .= "?{$data}";
    $appcode = "";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    try {
        $result = curlGet($url, $headers);
        $result = json_decode($result);
        if($result) {
            if($result->status == "01") {
                return $result;
            } else {
                jsonecho($result->status, $result->msg);
            }
        } else {
            jsonecho("error", "请求失败!");
        }
    } catch (Exception $exception) {
        jsonecho("error", $exception);
    }
}
