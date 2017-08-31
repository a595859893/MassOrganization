<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];
    $line = array();
    $mysqli = linkToSQL();

    if ($serverType == "getList") {
        $rst = $mysqli->query("SELECT * FROM recruitment");

        if ($rst) {
            $line["success"] = true;
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $UID = $row["massUID"];

                $rst2 = $mysqli->query("SELECT * from mass WHERE UID=$UID");
                while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC))
                    $row["host"] = $row2["name"];

                $line[] = $row;
                $num++;
            }
            $line["length"] = $num;
        } else {
            $line["success"] = false;
            $line["error"] = "评论发起错误，错误提示:(" . $mysqli->error . ")";
        }
    }

    echo json_encode($line);
    $mysqli->close();
}
?>
