<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];

    $mysqli = linkToSQL();
    $line = array();

    if ("login" == $serverType) {
        $account = $_POST["account"];
        $password = $_POST["password"];

        $rst = $mysqli->query("SELECT * FROM mass WHERE account='$account' LIMIT 1");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                if ($row["password"] == sha1($password)) {
                    $line = $row;
                    $_SESSION["uid"] = $row["UID"];
                } else $line["error"] = setError(1, "密码错误！");
            } else $line["error"] = setError(2, "账号不存在！");
        } else $line["error"] = setError(0, "登陆时，数据库错误，提示：" . $mysqli->error);
    } else if ("logout" == $serverType) {
        unset($_SESSION["uid"]);
    } else if ("checkState" == $serverType) {
        if (isset($_SESSION["uid"])) {
            $uid = $_SESSION["uid"];
            $line["state"] = true;
            $rst = $mysqli->query("SELECT * FROM mass WHERE UID=$uid LIMIT 1");
            if ($rst) {
                if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                    $line["account"] = $row;
                } else $line["error"] = setError(3, "不存在的UID");
            } else $line["error"] = setError(0, "检查登录状态时，数据库错误，提示：" . $mysqli->error);
        } else {
            $line["state"] = false;
        }
    } else if ("openID" == $serverType) {
        $code = $_POST["code"];
        $state = $_POST["state"];
        if (isset($_SESSION["openID"])) {
            $openID = $_SESSION["openID"];
            $order = "SELECT * FROM openID WHERE openID='$openID' LIMIT 1";
            $rst = $mysqli->query($order);
            if ($rst) {
                if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                    $line["openID"] = $row;
                    $line["info"] = json_decode($_SESSION["info"]);
                } else $line["error"] = setError(5, "openID错误");
            } else $line["error"] = setError(0, "判断数据存在时，数据库错误，提示：" . $mysqli->error);
        } else {
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


    } elseif ("register" == $serverType) {
        $account = $_POST["account"];
        $password = $_POST["password"];
        $type = $_POST["type"];
        $name = $_POST["name"];
        $head = $_POST["head"];

        $order = "SELECT account FROM mass WHERE account='$account'";
        $rst = $mysqli->query($order);
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["error"] = setError(1, "用户名重复");
            else {
                $password = sha1($password);
                $mysqli = linkToSQL();
                $order = "INSERT INTO mass (account,password,type,name,member) VALUES('$account','$password','$type','$name','$head')";
                $rst = $mysqli->query($order);
                if (!$rst) $line["error"] = setError(0, "注册时，数据库错误，提示：" . $mysqli->error);
            }
        } else $line["error"] = setError(0, "注册时，数据库错误，提示：" . $mysqli->error);

    } else  $line["error"] = setError(0, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>