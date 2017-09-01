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
        $order .= "VALUES('$title','$content','$sender','$type','$targetOpenID')";
        $rst = $mysqli->query($order);

        if (!$rst) $line["error"] = setError(0, "消息发布时，数据库错误，提示：" . $mysqli->error);
    } else if ($serverType == "Get") {
        $startUID = $_POST["startUID"];
        $num = $_POST["num"];

        $rst = $mysqli->query("SELECT * FROM message WHERE openID='$openID'AND UID>$startUID ORDER BY UID DESC LIMIT $num");
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
