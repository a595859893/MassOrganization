<?php
require "commonFunction.php";
$time = time();
$mysqli = linkToSQL();
$rst = $mysqli->query("SELECT * FROM dateRemind WHERE remind=false AND start<FROM_UNIXTIME($time+3600)");
while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
    if ($row["openID"] != "") {
        $date = array();
        $data["first"] = array();
        $data["first"]["value"] = "您报名的活动将在约一小时后开始";
        $data["keyword1"] = array();
        $data["keyword1"]["value"] = $row["name"];
        $data["keyword2"] = array();
        $data["keyword2"]["value"] = $row["start"];
        $data["keyword3"] = array();
        $data["keyword3"]["value"] = "-";
        $data["remark"] = array();
        $data["remark"]["value"] = "请您做好参加准备";
        sendMessageToWechat("os78O0giXJ26z8DRboVjyMLD9MzA",
            "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx67a84c01324490ca&redirect_uri=http%3A%2F%2Fwww.campuscircle.top%2F&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect",
            "WJT6hr_ZNBbtvEM_diRsj8FI84_TA5Obf7jSrEU6FUM", $data);
    }
}
$rst = $mysqli->query("UPDATE dateRemind SET remind=true WHERE remind=false AND start<FROM_UNIXTIME($time+3600)");
?>