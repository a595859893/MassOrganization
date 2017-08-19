<?php
	require 'commonFunction.php';

	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$serverType = $_POST["serverType"];
		$line = array();
		$mysqli = linkToSQL();
		$openID	= getOpenID();
		
		if($serverType == "Send"){
			$character	= $_POST["character"];
			$content 	= $_POST["content"];
			$type	 	= $_POST["type"];
			$uid		= $_POST["UID"];
			
			if($uid==-1){
				$order = "INSERT INTO conversation (openID,word,content,type) ";
				$order .="VALUES('$openID','$character','$content','$type')";

				$rst = $mysqli->query($order);
				
				if($rst){
					$line["success"] = true;
				}else{
					$line["success"] = false;
					$line["error"] = "话题发起错误，错误提示:(".$mysqli->error.")";
				}
			}else{
				$order = "INSERT INTO conversationReview (word,content,targetUID) ";
				$order .="VALUES('$character','$content',$uid)";

				$rst = $mysqli->query($order);
				
				if($rst){
					$line["success"] = true;
				}else{
					$line["success"] = false;
					$line["error"] = "评论发起错误，错误提示:(".$mysqli->error.")";
				}
			}
		}
		else if($serverType=="Get"){
			$num = $_POST["num"];
			$type = 'debunk';
			
			$rst = $mysqli->query("SELECT * FROM conversation as a WHERE $num>(SELECT count(*) FROM conversation WHERE UID>a.UID AND type='$type') ORDER BY a.UID DESC");
			if($rst){
				$looptag = 0;
				while($row = $rst->fetch_array(MYSQLI_ASSOC)){
					$id = $row["UID"];
					$rst2 = $mysqli->query("SELECT * FROM conversationReview WHERE targetUID=$id ORDER BY UID DESC");
					$reviewNum=0;
					$row["review"] = array();
					while($row2 = $rst2->fetch_array(MYSQLI_ASSOC)){
						$reviewNum++;
						$row["review"][] = $row2;
					}
					$row["review"]["length"]=$reviewNum;
					
					$rst3 = $mysqli->query("SELECT * from conversationGood WHERE topicID=$id");
					$goodNum=0;
					while($row3 = $rst3->fetch_array(MYSQLI_ASSOC)){
						$goodNum++;
					}
					$row["good"] = $goodNum;
					$line[] = $row;
					$looptag++;
				}
				$line["success"] = true;
				$line["length"] = $looptag;
			}else{
				$line["error"] = "话题获取错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
		}else if($serverType=="Good"){
			$topicID = $_POST["topicID"];
			
			$openID = getOpenID();
			$exist = false;
			$rst = $mysqli->query("SELECT * from conversationGood WHERE openID=$openID AND topicID=$topicID");
			while($row = $rst->fetch_array(MYSQLI_ASSOC)){
				$exist = true;
				$line["error"] = "点赞错误，错误提示: 已经点赞过了";
				$line["success"] = false;
				break;
			}
			
			if(!$exist){
				$rst = $mysqli->query("INSERT INTO conversationGood (openID,topicID) VALUES('$openID',$topicID)");
				if($rst)
					$line["success"] = true;
				else{
					$line["error"] = "点赞获取错误，错误提示: ".$mysqli->error;
					$line["success"] = false;
				}
			}
		}
		
		echo json_encode($line);
		$mysqli->close();
	}
?>
