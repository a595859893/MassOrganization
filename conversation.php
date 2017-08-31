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
        $type = $_POST["type"];
        $uid = $_POST["UID"];

        if ($uid == -1) {
            $order = "INSERT INTO conversation (openID,word,content,type,time) ";
            $order .= "VALUES('$openID','$character','$content','$type',FROM_UNIXTIME($time))";

            $rst = $mysqli->query($order);

            if (!$rst) $line["error"] = setError(0, "话题发起时，数据库错误，提示：" . $mysqli->error);
        } else {
            $order = "INSERT INTO conversationReview (word,content,targetUID,time) ";
            $order .= "VALUES('$character','$content',$uid,FROM_UNIXTIME($time))";

            $rst = $mysqli->query($order);

            if (!$rst) $line["error"] = setError(0, "评论发起时，数据库错误，提示：" . $mysqli->error);
        }
    } else if ($serverType == "Get") {
        $num = $_POST["num"];
        $line["success"] = true;
        $line["debunk"] = array();
        $line["conversation"] = array();
        $line["hotTopic"] = array();

        $type = 'debunk';
        $rst = $mysqli->query("SELECT * FROM conversation as a WHERE type='$type' ORDER BY a.UID DESC");
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $id = $row["UID"];
                $rst2 = $mysqli->query("SELECT * FROM conversationReview WHERE targetUID=$id ORDER BY UID DESC");
                $reviewNum = 0;
                $row["review"] = array();
                while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $reviewNum++;
                    $row["review"][] = $row2;
                }
                $row["review"]["length"] = $reviewNum;

                $line["debunk"][] = $row;
                $looptag++;
            }
            $line["debunk"]["length"] = $looptag;
        } else {
            $line["error"] = "匿槽获取错误，错误提示: " . $mysqli->error;
            $line["success"] = false;
        }

        $type = 'conversation';

        $rst = $mysqli->query("SELECT * FROM conversation WHERE type='$type' ORDER BY good DESC");
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $id = $row["UID"];
                $line["conversation"][] = $row;
                $looptag++;
            }
            $line["conversation"]["length"] = $looptag;
        } else $line["error"] = setError(0, "话题获取时，数据库错误，提示：" . $mysqli->error);

        $rst = $mysqli->query("SELECT * FROM conversationHot ORDER BY time DESC");
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $uid = $row["conID"];
                $rst2 = $mysqli->query("SELECT * FROM conversation WHERE UID=$uid");
                if ($rst) {
                    while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                        $line["hotTopic"][] = $row2;
                        $looptag++;
                    }
                } else $line["error"] = setError(0, "热门话题评论获取时，数据库错误，提示：" . $mysqli->error);
            }
            $line["hotTopic"]["length"] = $looptag;
        } else $line["error"] = setError(0, "热门话题获取时，数据库错误，提示：" . $mysqli->error);

    } else if ($serverType == "Good") {
        $topicID = $_POST["topicID"];

        $openID = getOpenID();
        $exist = false;
        $rst = $mysqli->query("SELECT * from conversationGood WHERE openID='$openID' AND topicID=$topicID LIMIT 1");

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
    } else if ($serverType == "getHotReview") {
        $uid = $_POST["UID"];
        $line["review"] = array();
        $line["success"] = true;

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

        $type = 'hot';
        $rst = $mysqli->query("SELECT * FROM conversation WHERE type='hot' AND targetUID=$uid ORDER BY time DESC");
        if ($rst) {
            $looptag = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $id = $row["UID"];
                $rst2 = $mysqli->query("SELECT * FROM conversationReview WHERE targetUID=$id ORDER BY UID DESC");
                $reviewNum = 0;
                $row["review"] = array();
                while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $reviewNum++;
                    $row["review"][] = $row2;
                }
                $row["review"]["length"] = $reviewNum;

                $line["hotCon"][] = $row;
                $looptag++;
            }
            $line["hotCon"]["length"] = $looptag;
        } else $line["error"] = setError(0, "匿槽获取阶段2时，数据库错误，提示：" . $mysqli->error);

    }

    echo json_encode($line);
    $mysqli->close();
}
?>
