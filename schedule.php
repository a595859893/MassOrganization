<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];

    $mysqli = linkToSQL();
    $openID = getOpenID();
    $line = array();

    if ("addDate" == $serverType) {
        $actName = $_POST["actName"];
        $actStart = $_POST["actStart"];
        $actEnd = $_POST["actEnd"];

        $order = "INSERT INTO dateRemind (openID,start,end,name) ";
        $order .= "VALUES('$openID','$actStart','$actEnd','$actName')";
        $rst = $mysqli->query($order);
        if (!$rst) $line["error"] = setError(0, "添加日程时，数据库错误，提示：" . $mysqli->error);
    } elseif ("getDate" == $serverType) {
        $order = "SELECT * FROM dateRemind WHERE openID='$openID'";
        $rst = $mysqli->query($order);
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line[] = $row;
            }
        } else $line["error"] = setError(0, "获取日程时，数据库错误，提示：" . $mysqli->error);
    } elseif ("delDate" == $serverType) {
        $uid = $_POST["UID"];
        $order = "DELETE FROM dateRemind WHERE UID=$uid";
        $rst = $mysqli->query($order);
        if (!$rst) $line["error"] = setError(0, "删除日程时，数据库错误，提示：" . $mysqli->error);
    } else $line["error"] = setError(0, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>