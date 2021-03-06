<?php
require 'commonFunction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverType = $_POST["serverType"];

    $mysqli = linkToSQL();
    $openID = getOpenID();
    $UID = $_SESSION["uid"];
    $line = array();
    $time = time();
    if ("acfForm" == $serverType) {
        $name = addslashes($_POST["name"]);
        $location = addslashes($_POST["location"]);
        $holdtime = addslashes($_POST["time"]);
        $endtimestr = isset($_POST["endTimeStr"]) ? addslashes($_POST["endTimeStr"]) : "1";
        $organizer = addslashes($_POST["organizer"]);
        $intro = addslashes($_POST["introduction"]);
        $type = addslashes($_POST["type"]);
        $json = addslashes($_POST["json"]);
        $campus = addslashes($_POST["campus"]);
        $logo = addslashes($_POST["logo"]);
        $url = addslashes($_POST["url"]);

        if (checkWechatUIRL($url)) {
            $order = "INSERT INTO activity (name,location,holdtime,endTimeStr,time,organizer,introduction,type,campus,logo,url,json,organizerUID) ";
            $order .= "VALUES('$name','$location','$holdtime','$endtimestr',FROM_UNIXTIME($time),'$organizer','$intro','$type','$campus','$logo','$url','$json',$UID)";

            $rst = $mysqli->query($order);
            if ($rst) {
                $mass_title = '新活动' . $name . '发布了!';
                $mass_content = '我们发布了新的活动，快来看呀！';

                $order = "INSERT INTO massDiary (title,content,time,logo,massUID) ";
                $order .= "VALUES('$mass_title','$mass_content',FROM_UNIXTIME($time),'$logo',$UID)";

                $rst = $mysqli->query($order);
            } else $line["error"] = setError(0, "活动发起时，数据库错误，提示：" . $mysqli->error);
        } else $line["error"] = setError(10, "URL不合法");


    } elseif ("recruitment" == $serverType) {
        $type = addslashes($_POST["type"]);
        $title = addslashes($_POST["title"]);
        $object = addslashes($_POST["object"]);
        $end = addslashes($_POST["end"]);
        $start = addslashes($_POST["start"]);
        $end = addslashes($_POST["end"]);
        $link = addslashes($_POST["link"]);

        $order = "INSERT INTO recruitment (type,title,object,start,end,link,time,massUID) ";
        $order .= "VALUES('$type','$title','$object','$start','$end','$link',FROM_UNIXTIME($time),$UID)";

        $rst = $mysqli->query($order);

        if (!$rst) $line["error"] = setError(0, "招新发起时，数据库错误，提示：" . $mysqli->error);
    } elseif ("massConfig" == $serverType) {
        $type = addslashes($_POST["type"]);
        $name = addslashes($_POST["name"]);
        $head = addslashes($_POST["head"]);
        $logo = addslashes($_POST["logo"]);
        $desc = addslashes($_POST["desc"]);
        $tel = addslashes($_POST["tel"]);
        $QRcode = addslashes($_POST["QRcode"]);
        $start = true;

        $order = "UPDATE mass SET";
        if ($name) {
            $order .= ($start ? " " : ",") . "name='$name'";
            $start = false;
        }
        if ($head) {
            $order .= ($start ? " " : ",") . "member='$head'";
            $start = false;
        }
        if ($logo) {
            $order .= ($start ? " " : ",") . "logo='$logo'";
            $start = false;
        }
        if ($desc) {
            $order .= ($start ? " " : ",") . "intro='$desc'";
            $start = false;
        }
        if ($type) {
            $order .= ($start ? " " : ",") . "type='$type'";
            $start = false;
        }
        if ($tel) {
            $order .= ($start ? " " : ",") . "phone='$tel'";
            $start = false;
        }
        if ($QRcode) {
            $order .= ($start ? " " : ",") . "QRcode='$QRcode'";
            $start = false;
        }
        $order .= " WHERE UID=$UID";

        $rst = $mysqli->query($order);
        if (!$rst) $line["error"] = setError(0, "社团信息修改时，数据库错误，提示：" . $mysqli->error);
    } elseif ("massDiary" == $serverType) {
        $title = addslashes($_POST["title"]);
        $content = addslashes($_POST["content"]);
        $logo = addslashes($_POST["logo"]);
        $img = addslashes($_POST["img"]);
        $url = addslashes($_POST["url"]);
        if (checkWechatUIRL($url)) {
            $order = "INSERT INTO massDiary (title,content,time,logo,img,url,massUID) ";
            $order .= "VALUES('$title','$content',FROM_UNIXTIME($time),'$logo','$img','$url',$UID)";
            $line = array();
            $rst = $mysqli->query($order);
            if (!$rst) $line["error"] = setError(0, "社团日志发布时，数据库错误，提示：" . $mysqli->error);
        } else $line["error"] = setError(10, "URL不合法");
    } elseif ("getActivity" == $serverType) {
        $line["mark"] = array();
        $line["list"] = array();
        $line["mass"] = array();

        $markNum = 0;
        $listNum = 0;
        $massNum = 0;

        $rst = $mysqli->query("SELECT * from activityMark WHERE openID='$openID'");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $uid = $row["actID"];
                $rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid LIMIT 1");
                if ($rst2) {
                    if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                        $markNum++;
                        $line["mark"][] = $row2;
                    } else $line["error"] = setError(1, "不存在的活动收藏");
                } else $line["error"] = setError(0, "收藏进一步获取时，数据库错误，提示：" . $mysqli->error);
            }
            $line["mark"]["length"] = $markNum;
        } else $line["error"] = setError(0, "收藏获取时，数据库错误，提示：" . $mysqli->error);

        $rst = $mysqli->query("SELECT * FROM actList WHERE openID='$openID'");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $uid = $row["actUID"];
                $rst2 = $mysqli->query("SELECT * FROM activity WHERE UID=$uid LIMIT 1");
                if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $listNum++;
                    $line["list"][] = $row2;
                } else $line["error"] = setError(1, "不存在的活动报名");
            }
            $line["list"]["length"] = $listNum;
        } else $line["error"] = setError(0, "报名获取时，数据库错误，提示：" . $mysqli->error);

        $rst = $mysqli->query("SELECT * FROM massMark WHERE openID='$openID'");
        if ($rst) {
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $uid = $row["markUID"];
                $rst2 = $mysqli->query("SELECT * FROM mass WHERE UID=$uid LIMIT 1");
                if ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                    $massNum++;
                    $line["mass"][] = $row2;
                } else $line["error"] = setError(1, "不存在的社团");
            }
            $line["mass"]["length"] = $massNum;
        } else $line["error"] = setError(0, "社团获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ("getActList" == $serverType) {

        $rst = $mysqli->query("SELECT UID,name FROM activity WHERE organizerUID=$UID");
        if ($rst) {
            $line["activity"] = array();
            $num = 0;
            while ($row = $rst->fetch_array(MYSQLI_ASSOC)) {
                $actUID = $row["UID"];
                $rst2 = $mysqli->query("SELECT * FROM actList WHERE actUID=$actUID");
                if ($rst2) {
                    $num2 = 0;
                    $row["list"] = array();
                    while ($row2 = $rst2->fetch_array(MYSQLI_ASSOC)) {
                        $row["list"][] = $row2;
                        $num2++;
                    }
                    $row["list"]["length"] = $num2;
                } else $line["error"] = setError(0, "活动报名列表获取时，数据库错误，提示：" . $mysqli->error);
                $line["activity"][] = $row;
                $num++;
            }
            $line["activity"]["length"] = $num;
        } else $line["error"] = setError(0, "已发布活动获取时，数据库错误，提示：" . $mysqli->error);
    } elseif ("destroyAct" == $serverType) {
        $actUID = $_POST["actUID"];
        $line = array();
        $rst = $mysqli->query("DELETE FROM actList WHERE actUID=$actUID");
        if (!$rst) $line["error"] = setError(0, "删除报名列表时，数据库错误，提示：" . $mysqli->error);
        $rst = $mysqli->query("DELETE FROM activityMark WHERE actID=$actUID");
        if (!$rst) $line["error"] = setError(0, "删除收藏列表时，数据库错误，提示：" . $mysqli->error);
        $rst = $mysqli->query("DELETE FROM activity WHERE UID=$actUID");
        if (!$rst) $line["error"] = setError(0, "删除活动时，数据库错误，提示：" . $mysqli->error);
    } elseif ("destroyDiary" == $serverType) {
        $uid = $_POST["UID"];
        $line = array();
        $rst = $mysqli->query("DELETE FROM massDiaryGood WHERE diaryUID=$uid");
        if (!$rst) $line["error"] = setError(0, "删除日志点赞列表时，数据库错误，提示：" . $mysqli->error);
        $rst = $mysqli->query("DELETE FROM massDiary WHERE UID=$uid");
        if (!$rst) $line["error"] = setError(0, "删除日志时，数据库错误，提示：" . $mysqli->error);
    } elseif ("destroyRecruitment" == $serverType) {
        $uid = $_POST["UID"];
        $line = array();
        $rst = $mysqli->query("DELETE FROM recruitment WHERE UID=$uid");
        if (!$rst) $line["error"] = setError(0, "删除招新时，数据库错误，提示：" . $mysqli->error);
    } else $line["error"] = setError(-1, "不匹配的类型");
    echo json_encode($line);
    $mysqli->close();
}
?>