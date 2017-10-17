<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];

    $mysqli = linkToSQL();
    $line = array();
    if ($serverType == "getList") {
        $type = addslashes($_REQUEST["type"]);
        $num = addslashes($_REQUEST["num"]);
        $startUID = addslashes($_REQUEST["startUID"]);

        $order = "SELECT * FROM recruitment WHERE type='$type'";
        if ($startUID > 0)
            $order .= " AND UID<$startUID";
        $order .= " ORDER BY UID DESC";
        if ($num > 0)
            $order .= " LIMIT $num";

        $rst = $mysqli->query($order);
        if ($rst) {
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line[] = $row;
                $num++;
            }
            $line["length"] = $num;
        } else  $line["error"] = setError(0, "列表获取时，数据库错误，提示：" . $mysqli->error);
    } else if ("getNameAndImg" == $serverType) {
        $UID = addslashes($_REQUEST["UID"]);

        $rst = $mysqli->query("SELECT name,logo from mass WHERE UID=$UID LIMIT 1");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line["host"] = $row["name"];
                $line["logo"] = $row["logo"];
            } else
                $line["error"] = setError(0, "招新不存在");
        } else  $line["error"] = setError(0, "社团获取时，数据库错误，提示：" . $mysqli->error);

    } else $line["error"] = setError(0, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>
