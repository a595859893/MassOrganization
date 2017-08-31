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
    return  $_SESSION["openID"];
}

function setError($code, $desc)
{
    $err = array();
    $err["code"] = $code;
    $err["desc"] = $desc;
    return $err;
}

?>