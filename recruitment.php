<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];
    $line = array();
    $mysqli = linkToSQL();

    if ($serverType == "getList") {
        $rst = $mysqli->query("SELECT * FROM recruitment");

        if ($rst) {
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $UID = $row["massUID"];

                $rst2 = $mysqli->query("SELECT * from mass WHERE UID=$UID");
                if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC))
                    $row["host"] = $row2["name"];
                else
                    $line["error"] = setError(0, "招新不存在");

                $line[] = $row;
                $num++;
            }
            $line["length"] = $num;
        } else  $line["error"] = setError(0, "列表获取时，数据库错误，提示：" . $mysqli->error);
    }

    echo json_encode($line);
    $mysqli->close();
}
?>
