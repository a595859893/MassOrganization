<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_REQUEST["serverType"];

    $openID = getOpenID();
    $mysqli = linkToSQL();
    $line = array();

    if ($serverType == "GetList") {
        $place = $_REQUEST["place"];
        $type = $_REQUEST["type"];
        $order = $_REQUEST["order"];

        $place = "'" . preg_replace("/,/", "','", $place) . "','不限'";
        $type = "'" . preg_replace("/,/", "','", $type) . "','不限'";
        $orderStr = "SELECT * FROM activity WHERE campus in(" . $place . ")AND type in(" . $type . ")";
        if ($order == "最新")
            $orderStr .= "ORDER BY time DESC";
        else if ($order == "最热")
            $orderStr .= "ORDER BY mark DESC";

        $rst = $mysqli->query($orderStr);
        if ($rst) {
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $actID = $row["UID"];
                $exist = false;
                $rst2 = $mysqli->query("SELECT * from activityMark WHERE openID='$openID' AND actID=$actID");
                if ($rst2) {
                    if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) $exist = true;
                    $row["mark"] = $exist;
                    $line[] = $row;
                    $num++;
                } else $line["error"] = setError(0, "收藏获取时，数据库错误，错误提示:" . $mysqli->error);
            }
            $line["length"] = $num;
        } else $line["error"] = setError(0, "活动获取时，数据库错误，错误提示:" . $mysqli->error);

    } else if ($serverType == "Mark") {
        $actID = $_POST["actID"];

        $exist = false;
        $rst = $mysqli->query("SELECT * from activityMark WHERE openID='$openID' AND actID=$actID");
        if ($row = $rst->fetch_array(MYSQLI_ASSOC)) $exist = true;

        if (!$exist) {
            $rst2 = $mysqli->query("INSERT INTO activityMark (openID,actID) VALUES('$openID',$actID)");
            $line["mark"] = true;
            if ($rst2) {
                $mysqli->query("UPDATE activity SET mark=mark+1 WHERE UID=$actID");
                $line["success"] = true;
            } else $line["error"] = setError(0, "收藏时，数据库错误，错误提示:" . $mysqli->error);
        } else {
            $rst2 = $mysqli->query("DELETE FROM activityMark WHERE openID='$openID' AND actID=$actID");
            $line["mark"] = false;
            if ($rst2) {
                $mysqli->query("UPDATE activity SET mark=mark-1 WHERE UID=$actID");
                $line["success"] = true;
            } else $line["error"] = setError(0, "收藏删除时，数据库错误，错误提示:" . $mysqli->error);
        }
    } else if ($serverType == "Post") {
        $json = $_REQUEST["json"];
        $uid = $_REQUEST["UID"];

        if ($rst = $mysqli->query("INSERT INTO actList (openID,json,actUID)VALUES('$openID','$json',$uid)")) {
            $line["success"] = true;
        } else $line["error"] = setError(0, "表单发布时，数据库错误，错误提示:" . $mysqli->error);
    } else if ($serverType == "Billboard") {
        $num = $_REQUEST["num"];

        $line["success"] = true;
        $line["mass"] = array();
        $line["activity"] = array();

        $rst = $mysqli->query("SELECT * FROM mass as a WHERE $num>(SELECT count(*) FROM mass WHERE good>a.good) ORDER BY a.good DESC");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["mass"][] = $row;
        } else $line["error"] = setError(0, "排行榜社团获取时，数据库错误，错误提示:" . $mysqli->error);
        $rst = $mysqli->query("SELECT * FROM activity as a WHERE $num>(SELECT count(*) FROM mass WHERE mark>a.mark) ORDER BY a.mark DESC");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["activity"][] = $row;
        } else $line["error"] = setError(0, "排行榜获活动取时，数据库错误，错误提示:" . $mysqli->error);
    } else $line["error"] = setError(-1, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>
