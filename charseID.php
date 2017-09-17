<?php
require "commonFunction.php";
$time = time();
$mysqli = linkToSQL();
$rst = $mysqli->query("SELECT * FROM message");
while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
    if ($row["openID"] != "") {
        $appid = "wx67a84c01324490ca";
        $appSecret = "5f734c9f9aae69d158461f66da95cd1e";
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appSecret&code=$code&grant_type=authorization_code";
        $token = json_decode(file_get_contents($token_url));
        if (!isset($token->errcode)) {
            $openID = $token->openid;
            $token_access = $token->access_token;
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$token_access&openid=$openID&lang=zh_CN";
            $info = file_get_contents($info_url);
            $info_json = json_decode($info);
            if (!isset($info_json->errcode)) {
                $_SESSION["openID"] = $openID;
                $_SESSION["info"] = $info;

                $line["info"] = $info_json;

                $order = "SELECT * FROM openID WHERE openID='$openID' LIMIT 1";
                $rst = $mysqli->query($order);
                if ($rst) {
                    if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                        $line["openID"] = $row;
                    } else {
                        $line["openID"] = $openID;
                        $order = "INSERT INTO openID (openID) VALUES ('$openID')";
                        $rst2 = $mysqli->query($order);
                        if (!$rst2) $line["error"] = setError(0, "注册数据存在时，数据库错误，提示：" . $mysqli->error);
                    }
                } else $line["error"] = setError(0, "判断数据存在时，数据库错误，提示：" . $mysqli->error);
            } else $line["error"] = setError(4, "info获取错误，错误代码" . $info_json->errcode);
        } else $line["error"] = setError(4, "token获取错误，错误代码" . $token->errcode);
    }
}
$rst = $mysqli->query("UPDATE dateRemind SET remind=true WHERE remind=false AND start<FROM_UNIXTIME($time+3600)");
?>