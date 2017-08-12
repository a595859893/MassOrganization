<?php
	if($_SERVER["REQUEST_METHOD"]=="POST"){
		$type = $_POST["type"];
		$line = array();
		$mysqli = new mysqli('localhost','root','SYSUcc123','ipe_db','3306');
		$mysqli->query("set character set 'utf8'");
		$mysqli->query("set names 'utf8'");
		
		if($mysqli->connect_errno){
			$line["error"] = "服务器连接失败，错误提示：".$mysqli->error;
			$line["success"] = false;
			echo json_encode($line);
			$mysqli->close();
			exit();
		}
		
		if($type == "Send"){
			$character	= $_POST["character"];
			$content 	= $_POST["content"];
			$uid		= $_POST["UID"];
			
			if($uid==-1){
				$order = "INSERT INTO conversation (word,content,good) ";
				$order .="VALUES('$character','$content',0)";

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
			
		}else if($type=="Get"){
			$num = $_POST["num"];
			
			$rst = $mysqli->query("SELECT * FROM conversation as a WHERE $num>(SELECT count(*) FROM conversation WHERE UID>a.UID) ORDER BY a.UID DESC");
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
					$line[] = $row;
					$looptag++;
				}
				$line["success"] = true;
				$line["length"] = $looptag;
			}else{
				$line["error"] = "话题获取错误，错误提示: ".$mysqli->error;
				$line["success"] = false;
			}
		}
		
		echo json_encode($line);
		$mysqli->close();
	}
?>
