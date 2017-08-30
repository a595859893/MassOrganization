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

            if ($rst) {
                $line["success"] = true;
            } else {
                $line["success"] = false;
                $line["error"] = "话题发起错误，错误提示:(" . $mysqli->error . ")";
            }
        } else {
            $order = "INSERT INTO conversationReview (word,content,targetUID,time) ";
            $order .= "VALUES('$character','$content',$uid,FROM_UNIXTIME($time))";

            $rst = $mysqli->query($order);

            if ($rst) {
                $line["success"] = true;
            } else {
                $line["success"] = false;
                $line["error"] = "评论发起错误，错误提示:(" . $mysqli->error . ")";
            }
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
        } else {
            $line["error"] = "话题获取错误，错误提示: " . $mysqli->error;
            $line["success"] = false;
        }


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
                } else {
                    $line["error"] = "热门话题信息获取错误，错误提示: " . $mysqli->error;
                    $line["success"] = false;
                }
            }
            $line["hotTopic"]["length"] = $looptag;
        } else {
            $line["error"] = "热门话题获取错误，错误提示: " . $mysqli->error;
            $line["success"] = false;
        }

    } else if ($serverType == "Good") {
        $topicID = $_POST["topicID"];

        $openID = getOpenID();
        $exist = false;
        $rst = $mysqli->query("SELECT * from conversationGood WHERE openID='$openID' AND topicID=$topicID");
        while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
            $exist = true;
            $line["error"] = "点赞错误，错误提示: 已经点赞过了";
            $line["success"] = false;
            break;
        }

        if (!$exist) {
            $rst = $mysqli->query("INSERT INTO conversationGood (openID,topicID) VALUES('$openID',$topicID)");
            if ($rst) {
                $mysqli->query("UPDATE conversation SET good=good+1 WHERE UID=$topicID");
                $line["success"] = true;
            } else {
                $line["error"] = "点赞获取错误，错误提示: " . $mysqli->error;
                $line["success"] = false;
            }
        }
    } else if ($serverType == "getHotReview") {
        $uid = $_POST["UID"];
        $line["review"] = array();
        $line["success"] = true;

        $rst = $mysqli->query("SELECT * FROM conversation WHERE UID=$uid");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line["state"] = $row;
            }
        } else {
            $line["success"] = false;
            $line["error"] = "热门话题获取错误，错误提示:(" . $mysqli->error . ")";
        }

        $rst = $mysqli->query("SELECT * FROM conversationHot WHERE conID=$uid");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $line["hot"] = $row;
            }
        } else {
            $line["success"] = false;
            $line["error"] = "热门话题获取错误，错误提示:(" . $mysqli->error . ")";
        }

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
        } else {
            $line["error"] = "匿槽获取错误，错误提示: " . $mysqli->error;
            $line["success"] = false;
        }

    }

    echo json_encode($line);
    $mysqli->close();
}
?>
