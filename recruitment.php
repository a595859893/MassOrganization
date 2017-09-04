<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];

    $mysqli = linkToSQL();
    $line = array();
    if ($serverType == "getList") {
        $type = $_REQUEST["type"];
        $num = $_REQUEST["num"];
        $startUID = $_REQUEST["startUID"];

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
                $UID = $row["massUID"];

                $rst2 = $mysqli->query("SELECT name,logo from mass WHERE UID=$UID LIMIT 1");
                if ($rst2) {
                    if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                        $row["host"] = $row2["name"];
                        $row["logo"] = $row2["logo"];
                    } else
                        $line["error"] = setError(0, "招新不存在");
                } else  $line["error"] = setError(0, "社团获取时，数据库错误，提示：" . $mysqli->error);

                $line[] = $row;
                $num++;
            }
            $line["length"] = $num;
        } else  $line["error"] = setError(0, "列表获取时，数据库错误，提示：" . $mysqli->error);
    } else $line["error"] = setError(0, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>
