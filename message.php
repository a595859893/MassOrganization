<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];
    $line = array();
    $mysqli = linkToSQL();
    $openID = getOpenID();
    $time = time();

    if ($serverType == "Send") {
        $type = $_POST["type"];
        $title = $_POST["title"];
        $content = $_POST["content"];
        $targetOpenID = $_POST["openID"];

        $order = "INSERT INTO message (title,content,sender,type,openID) ";
        $order .= "VALUES('$title','$content','$openID','$type','$targetOpenID')";
        $rst = $mysqli->query($order);

        if (!$rst) $line["error"] = setError(0, "消息发布时，数据库错误，提示：" . $mysqli->error);
    } elseif ($serverType == "SendGroup") {
        $type = $_POST["type"];
        $title = $_POST["title"];
        $content = $_POST["content"];
        $groupType = $_POST["groupType"];
        $groupUID = $_POST["groupUID"];

        if ($groupUID == "uid")
            $groupUID = $_SESSION["uid"];
        if ($content == "uid")
            $content = $_SESSION["uid"];

        if ("MassMark" == $groupType) {
            $order = "SELECT * from massMark WHERE markUID=$groupUID";
        } elseif ("ActivityMark" == $groupType) {
            $order = "SELECT * from activityMark WHERE actID=$groupUID";
        } elseif ("ActivityList" == $groupType) {
            $order = "SELECT * from actList WHERE actUID=$groupUID";
        } else $line["error"] = setError(0, "不匹配的群组类型");

        $rst = $mysqli->query($order);
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line[] = $row;
                $targetOpenID = $row["openID"];
                $order = "INSERT INTO message (title,content,sender,type,openID) ";
                $order .= "VALUES('$title','$content','$openID','$type','$targetOpenID')";
                $rst2 = $mysqli->query($order);
                if (!$rst2) $line["error"] = setError(0, "消息发布时，数据库错误，提示：" . $mysqli->error);
            }
            $line["uid"] = $groupUID;
        } else $line["error"] = setError(0, "群组获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ($serverType == "Get") {
        $startUID = $_POST["startUID"];
        $num = $_POST["num"];
        $order = "SELECT * FROM message WHERE openID='$openID'";
        if ($startUID > 0)
            $order .= "AND UID>$startUID";
        $order .= "ORDER BY UID DESC";
        if ($num > 0)
            $order .= "LIMIT $num";
        $rst = $mysqli->query($order);
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line[] = $row;
                $looptag++;
            }
            $line["length"] = $looptag;
        } else  $line["error"] = setError(0, "信息获取时，数据库错误，提示：" . $mysqli->error);
    } else $line["error"] = setError(0, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>
