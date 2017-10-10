<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];
    $openID = getOpenID();
    $line = array();
    $mysqli = linkToSQL();

    if ($serverType == "getList") {
        $startUID = addslashes($_POST["startUID"]);
        $num = addslashes($_POST["num"]);
        $order = "SELECT * FROM mass";
        if ($startUID > 0)
            $order .= " WHERE UID<$startUID";

        $order .= " ORDER BY good DESC";

        if ($num > 0)
            $order .= " LIMIT $num";


        $rst = $mysqli->query($order);
        if ($rst) {
            $line["success"] = true;
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $UID = $row["UID"];
                $massinfo = array();

                $massinfo["type"] = $row["type"];
                $massinfo["name"] = $row["name"];
                $massinfo["intro"] = $row["intro"];
                $massinfo["member"] = $row["member"];
                $massinfo["number"] = $row["number"];
                $massinfo["good"] = $row["good"];
                $massinfo["UID"] = $row["UID"];
                $rst2 = $mysqli->query("SELECT * from massGood WHERE openID='$openID' AND massUID=$UID");
                $massinfo["igood"] = ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) ? true : false;
                $rst2 = $mysqli->query("SELECT * from massMark WHERE openID='$openID' AND markUID=$UID");
                $massinfo["imark"] = ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) ? true : false;

                $line[] = $massinfo;
                $num++;
            }
            $line["length"] = $num;
        } else  $line["error"] = setError(0, "列表获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ($serverType == "good") {
        $massUID = addslashes($_POST["massUID"]);

        $exist = false;
        $rst = $mysqli->query("SELECT * from massGood WHERE openID='$openID' AND massUID=$massUID  LIMIT 1");

        if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
            $rst2 = $mysqli->query("DELETE FROM massGood WHERE openID='$openID' AND massUID=$massUID");
            $mysqli->query("UPDATE mass SET good=good-1 WHERE UID=$massUID");
            $line["good"] = false;
            if (!$rst2) $line["error"] = setError(0, "点赞删除时，数据库错误，提示：" . $mysqli->error);
        } else {
            $rst2 = $mysqli->query("INSERT INTO massGood (openID,massUID) VALUES('$openID',$massUID)");
            $mysqli->query("UPDATE mass SET good=good+1 WHERE UID=$massUID");
            $line["good"] = true;
            if (!$rst2) $line["error"] = setError(0, "点赞时，数据库错误，提示：" . $mysqli->error);
        }
    } elseif ($serverType == "mark") {
        $markUID = addslashes($_POST["markUID"]);

        $exist = false;
        $rst = $mysqli->query("SELECT * from massMark WHERE openID='$openID' AND markUID=$markUID LIMIT 1");

        if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
            $rst2 = $mysqli->query("DELETE FROM massMark WHERE openID='$openID' AND markUID=$markUID");
            $mysqli->query("UPDATE mass SET number=number-1 WHERE UID=$markUID");
            $line["mark"] = false;
            if (!$rst2) $line["error"] = setError(0, "收藏删除时，数据库错误，提示：" . $mysqli->error);
        } else {
            $rst2 = $mysqli->query("INSERT INTO massMark (openID,markUID) VALUES('$openID',$markUID)");
            $mysqli->query("UPDATE mass SET number=number+1 WHERE UID=$markUID");
            $line["mark"] = true;
            if (!$rst2) $line["error"] = setError(0, "收藏添加时，数据库错误，提示：" . $mysqli->error);
        }
    } elseif ($serverType == "getMass") {
        $uid = addslashes($_POST["UID"]);
        $rst = $mysqli->query("SELECT type,name,intro,number,member,good,logo,QRcode,UID FROM mass WHERE UID=$uid");

        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $rst2 = $mysqli->query("SELECT * FROM massGood WHERE openID='$openID' AND massUID=$uid LIMIT 1");
                $row["igood"] = ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) ? true : false;
                $rst2 = $mysqli->query("SELECT * FROM massMark WHERE openID='$openID' AND markUID=$uid LIMIT 1");
                $row["imark"] = ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) ? true : false;
                $line["mass"] = $row;


                $num = 0;
                $line["diary"] = array();
                $rst2 = $mysqli->query("SELECT * FROM massDiary WHERE massUID=$uid");
                while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $id = $row["UID"];

                    $rst3 = $mysqli->query("SELECT * FROM massDiaryGood WHERE openID='$openID' AND diaryUID=$id LIMIT 1");
                    $row2["igood"] = ($row3 = $rst3->fetch_array(MYSQLI_ASSOC)) ? true : false;
                    $line["diary"][] = $row2;
                    $num++;
                }
                $line["diary"]["length"] = $num;

                $num = 0;
                $line["recruitment"] = array();
                $rst = $mysqli->query("SELECT * FROM recruitment WHERE massUID=$uid");
                while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                    $line["recruitment"][] = $row;
                    $num++;
                }
                $line["recruitment"]["length"] = $num;
            } else $line["error"] = setError(0, "社团不存在");
        } else  $line["error"] = setError(0, "获取社团时，数据库错误，提示：" . $mysqli->error);
    } elseif ($serverType == "goodDiary") {
        $diaryUID = addslashes($_POST["diaryUID"]);

        $rst = $mysqli->query("SELECT * from massDiaryGood WHERE openID='$openID' AND diaryUID=$diaryUID");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $rst2 = $mysqli->query("DELETE FROM massDiaryGood WHERE openID='$openID' AND diaryUID=$diaryUID");
                $mysqli->query("UPDATE massDiary SET good=good-1 WHERE UID=$diaryUID");
                $line["good"] = false;
                if (!$rst2) $line["error"] = setError(0, "点赞删除时，数据库错误，提示：" . $mysqli->error);
            } else {
                $rst2 = $mysqli->query("INSERT INTO massDiaryGood (openID,diaryUID) VALUES('$openID',$diaryUID)");
                $mysqli->query("UPDATE massDiary SET good=good+1 WHERE UID=$diaryUID");
                $line["good"] = true;
                if (!$rst2) $line["error"] = setError(0, "点赞时，数据库错误，提示：" . $mysqli->error);
            }
        } else $line["error"] = setError(0, "点赞判断时，数据库错误，提示：" . $mysqli->error);
    } else if ("getImg" == $serverType) {
        $UID = addslashes($_POST["UID"]);
        $type = addslashes($_POST["type"]);
        if ($type == "logo" || $type == "QRcode") {
            $rst = $mysqli->query("SELECT $type from mass WHERE UID='$UID'");
            if ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["img"] = $row["$type"];
            else
                $line["error"] = setError(-1, "图像不存在");
        } else $line["error"] = setError(-1, "类型非法");


    } else $line["error"] = setError(-1, "不匹配的类型");

    echo json_encode($line);
    $mysqli->close();
}
?>
