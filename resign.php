<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>注册</title>
</head>
<body>
<form method="post">
    <label>账号</label><input name="account"/><br/>
    <label>密码</label><input name="password" type="password"/><br/>
    <button type="submit">提交</button>
</form>
<?php
require 'commonFunction.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $_POST["account"];
    $password = $_POST["password"];
    $password = sha1($password);
    $mysqli = linkToSQL();
    $order = "INSERT INTO mass (account,password) VALUES('$account','$password')";
    $rst = $mysqli->query($order);
    if ($rst)
        echo "添加成功！" . $account . "/" . $password;
    else
        echo "(" . $mysqli->error . ")";
}
?>
</body>