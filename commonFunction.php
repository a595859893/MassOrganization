<?php
session_start();

function linkToSQL()
{
    $mysqli = new mysqli('localhost', 'root', 'SYSUcc123', 'ipe_db', '3306');
    $mysqli->query("set character set 'utf8'");
    $mysqli->query("set names 'utf8'");
    if ($mysqli->connect_errno) {
        $line["success"] = false;
        $line["error"] = "连接服务器失败!" . $mysqli->connect_errno;
        $mysqli->close();
        exit(json_encode($line));
        return false;
    }
    return $mysqli;
}

function getOpenID($mysqli)
{
    return $_SESSION["openID"];
}

function setError($code, $desc)
{
    $err = array();
    $err["code"] = $code;
    $err["desc"] = $desc;
    return $err;
}

function parseTime($time)
{
    $parsed = date_parse($time);
    $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
    return $seconds;
}

function getToken()
{
    $mysqli = linkToSQL();
    $rst = $mysqli->query("SELECT * FROM token ORDER BY UID DESC LIMIT 1");
    if ($rst) {
        if (($row = $rst->fetch_array(MYSQLI_ASSOC)) && ($row["time"] + $row["date"] > time()))
            return $row["token"];
        else {
            $appid = "wx67a84c01324490ca";
            $appSecret = "5f734c9f9aae69d158461f66da95cd1e";
            $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appSecret";
            $token = json_decode(file_get_contents($token_url));
            if (!isset($token->errcode)) {
                $token_access = $token->access_token;
                $time = $token->expires_in;
                $date = time();
                $rst = $mysqli->query("INSERT INTO token (time,date,token) VALUES ($time,$date,'$token_access')");
                if (!$rst) return false;

                return $row["token"];
            }
        }
    }
    return false;
}

function postServer($remote_server, $post_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    $result = curl_exec($ch);
    return $result;
}

function sendMessageToWechat($openID, $url, $tmeplate_id, $data)
{
    $token = getToken();

    $json = array();
    $json["touser"] = $openID;
    $json["template_id"] = $tmeplate_id;
    $json["url"] = $url;
    $json["data"] = $data;

    $json_Str = json_encode($json);
    $rst = postServer("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token", $json_Str);
    $rst_json = json_decode($rst);
    return ($rst_json->errcode == 0);
}

function checkWechatUIRL($url)
{
    return preg_match('/^http:\/\/mp.weixin\.qq\.com\/s/', $url) > 0;
}

?>