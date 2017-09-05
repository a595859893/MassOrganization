<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];
    $line = array();
    $mysqli = linkToSQL();
    $openID = getOpenID();
    $time = time();

    if ($serverType == "Send") {
        $character = $_POST["character"];
        $content = $_POST["content"];
        $targetUID = $_POST["targetUID"] ? $_POST["targetUID"] : 0;
        $type = $_POST["type"];
        $uid = $_POST["UID"];

        if ($uid == -1) {
            $order = "INSERT INTO conversation (openID,word,content,type,time,targetUID) ";
            $order .= "VALUES('$openID','$character','$content','$type',FROM_UNIXTIME($time),$targetUID)";
            $rst = $mysqli->query($order);
            if (!$rst) $line["error"] = setError(0, "话题发起时，数据库错误，提示：" . $mysqli->error);
        } else {
            $order = "INSERT INTO conversationReview (word,content,targetUID,time) ";
            $order .= "VALUES('$character','$content',$uid,FROM_UNIXTIME($time))";
            $rst = $mysqli->query($order);
            if (!$rst) $line["error"] = setError(0, "评论发起时，数据库错误，提示：" . $mysqli->error);
        }
    } elseif ("Get" == $serverType) {
        $startUID = $_POST["startUID"];
        $UID = $_POST["UID"];
        $num = $_POST["num"];
        $type = $_POST["type"];
        $goodOrder = $_POST["goodOrder"];
        $order = "SELECT * FROM conversation WHERE type='$type'";
        if ($type == "hot")
            $order .= " AND targetUID=$UID";
        if ($startUID > 0)
            $order .= " AND UID<$startUID";
        if ($goodOrder)
            $order .= " ORDER BY good DESC";
        else
            $order .= " ORDER BY UID DESC";
        if ($num > 0)
            $order .= " LIMIT $num";
        $rst = $mysqli->query($order);
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $id = $row["UID"];

                $rst2 = $mysqli->query("SELECT * from conversationGood WHERE openID='$openID' AND topicID=$id LIMIT 1");
                if ($rst2) {
                    $row["igood"] = ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) ? true : false;
                } else $line["error"] = setError(0, "点赞判断时，数据库错误，提示：" . $mysqli->error);

                $reviewNum = 0;
                $row["review"] = array();
                $rst2 = $mysqli->query("SELECT * FROM conversationReview WHERE targetUID=$id ORDER BY UID DESC");
                while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $reviewNum++;
                    $row["review"][] = $row2;
                }
                $row["review"]["length"] = $reviewNum;

                $line[] = $row;
                $looptag++;
            }
            $line["length"] = $looptag;
        } else  $line["error"] = setError(0, "评论及匿槽获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ("GetHotTopic" == $serverType) {
        $startUID = $_POST["startUID"];
        $num = $_POST["num"];

        $rst = $mysqli->query("SELECT * FROM conversationHot ORDER BY time DESC");
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $uid = $row["conID"];
                $rst2 = $mysqli->query("SELECT * FROM conversation WHERE UID=$uid");
                if ($rst) {
                    while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                        $line[] = $row2;
                        $looptag++;
                    }
                } else $line["error"] = setError(0, "热门话题评论获取时，数据库错误，提示：" . $mysqli->error);
            }
            $line["length"] = $looptag;
        } else $line["error"] = setError(0, "热门话题获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ($serverType == "Good") {
        $topicID = $_POST["topicID"];

        $exist = false;
        $rst = $mysqli->query("SELECT * from conversationGood WHERE openID='$openID' AND topicID=$topicID LIMIT 1");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $rst2 = $mysqli->query("DELETE FROM conversationGood WHERE openID='$openID' AND topicID=$topicID");
                $mysqli->query("UPDATE conversation SET good=good-1 WHERE UID=$topicID");
                $line["good"] = false;
                if (!$rst2) $line["error"] = setError(0, "点赞删除时，数据库错误，提示：" . $mysqli->error);
            } else {
                $rst2 = $mysqli->query("INSERT INTO conversationGood (openID,topicID) VALUES('$openID',$topicID)");
                $mysqli->query("UPDATE conversation SET good=good+1 WHERE UID=$topicID");
                $line["good"] = true;
                if (!$rst2) $line["error"] = setError(0, "点赞时，数据库错误，提示：" . $mysqli->error);
            }
        } else $line["error"] = setError(0, "点赞判断时，数据库错误，提示：" . $mysqli->error);
    } elseif ("getHotReview" == $serverType) {
        $uid = $_POST["UID"];
        $line["hotCon"] = array();

        $rst = $mysqli->query("SELECT * FROM conversation WHERE UID=$uid LIMIT 1");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["state"] = $row;
        } else  $line["error"] = setError(0, "热门话题获取阶段1时，数据库错误，提示：" . $mysqli->error);

        $rst = $mysqli->query("SELECT * FROM conversationHot WHERE conID=$uid  LIMIT 1");
        if ($rst) {
            if ($row = $rst->fetch_array(MYSQLI_ASSOC))
                $line["hot"] = $row;
        } else  $line["error"] = setError(0, "热门话题获取阶段2时，数据库错误，提示：" . $mysqli->error);
    }

    echo json_encode($line);
    $mysqli->close();
}
?>
