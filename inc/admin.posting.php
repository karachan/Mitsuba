<?php
function deletePostMod($conn, $board, $postno, $onlyimgdel = 0)
{
	
	if (is_numeric($postno))
	{
		$board = mysqli_real_escape_string($conn, $board);
		if (!isBoard($conn, $board))
		{
			return -16;
		}
		$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$postno);
		if (mysqli_num_rows($result) == 1)
		{
			$postdata = mysqli_fetch_assoc($result);
		
			if ($onlyimgdel == 1)
			{
				if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
				{
						
					$filename = $postdata['filename'];
					if (substr($filename, 0, 8) == "spoiler:")
					{
						$filename = substr($filename, 8);
					}
					unlink("./".$board."/src/".$postdata['filename']);
					unlink("./".$board."/src/thumb/".$postdata['filename']);
					mysqli_query($conn, "UPDATE posts_".$board." SET filename='deleted' WHERE id=".$postno.";");
					if ($postdata['resto'] != 0)
					{
						generateView($conn, $board, $postdata['resto']);
						generateView($conn, $board);
					} else {
						generateView($conn, $board, $postno);
						generateView($conn, $board);
					}
					return 1; //done-image
				} else {
					return -3;
				}
			} else {
				if ($postdata['resto'] == 0) //we'll have to delete whole thread
				{
					$files = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE filename != '' AND resto=".$postdata['id']);
					while ($file = mysqli_fetch_assoc($files))
					{
						$filename = $file['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						unlink("./".$board."/src/".$filename);
						unlink("./".$board."/src/thumb/".$$filename);
					}
					if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
					{
						$filename = $postdata['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						unlink("./".$board."/src/".$filename);
						unlink("./".$board."/src/thumb/".$filename);
					}
					mysqli_query($conn, "DELETE FROM posts_".$board." WHERE resto=".$postno.";");
					mysqli_query($conn, "DELETE FROM posts_".$board." WHERE id=".$postno.";");
					unlink("./".$board."/res/".$postno.".html");
					//generateView($conn, $board, $postno);
					generateView($conn, $board);
					return 2; //done post
				} else {
					if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
					{
						
						$filename = $postdata['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						unlink("./".$board."/src/".$filename);
						unlink("./".$board."/src/thumb/".$filename);
					}
					mysqli_query($conn, "DELETE FROM posts_".$board." WHERE id=".$postno.";");
					generateView($conn, $board, $postdata['resto']);
					generateView($conn, $board);
					return 2;
				}
			}
			
		} else {
			return -2;
		}
	} else {
		return -2;
	}
}

function generatePost($conn, $board, $id)
{
	if ((empty($id)) || (!is_numeric($id)))
	{
		return -15;
	}
	if ((empty($id)) || (!isBoard($conn, $board)))
	{
		return -16;
	}
	$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$id);
	if (mysqli_num_rows($result) == 1)
	{
		$post = mysqli_fetch_assoc($result);
		if ($post['resto'] == 0)
		{
			generateView($conn, $board, $post['id']);
		} else {
			generateView($conn, $board, $post['resto']);
		}
		generateView($conn, $board);
	}
}

function addPostMod($conn, $board, $name, $email, $subject, $comment, $password, $filename, $orig_filename, $resto = 0, $md5 = "", $spoiler = 0, $capcode = 0, $raw = 0, $sticky = 0, $locked = 0, $nolimit = 0)
{
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	if (!is_numeric($resto))
	{
		$resto = 0;
	}
	if ((!is_numeric($raw)) || ($_SESSION['type'] == 0))
	{
		$raw = 0;
	}
	if ((!is_numeric($capcode)) || ($_SESSION['type'] == 0))
	{
		$capcode = 0;
	}
	if ((!is_numeric($sticky)) || ($_SESSION['type'] == 0))
	{
		$sticky = 0;
	}
	if ((!is_numeric($locked)) || ($_SESSION['type'] == 0))
	{
		$locked = 0;
	}
	
	if ($resto != 0)
	{
		$sticky = 0;
		$locked = 0;
	}
	
	if (($resto == 0) && (empty($filename)))
	{
		echo "<center><h1>Error: No file selected.</h1><br /><a href='./".$board."'>RETURN</a></center>";
		return;
	}
	
	if ((empty($filename)) && (empty($comment)))
	{
		echo "<center><h1>Error: No file selected.</h1><br /><a href='./".$board."'>RETURN</a></center>";
		return;
	}

	$bdata = getBoardData($conn, $board);
	if ((!empty($filename)) && ($spoiler == 1) && ($bdata['spoilers'] == 1))
	{
		$filename = "spoiler:".$filename;
	}
	$thread = "";
	$tinfo = "";
	$replies = 0;
	if ($resto != 0)
	{
		$thread = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$resto);
		
		if (($bdata['bumplimit'] > 0) && ($nolimit == 0))
		{
			$replies = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE resto=".$resto);
			$replies = mysqli_num_rows($replies);
		}
		
		if (mysqli_num_rows($thread) == 0)
		{
			echo "<center><h1>Error: Cannot reply to thread because thread does not exist.</h1><br /><a href='./".$board."'>RETURN</a></center>";
			return;
		}
		$tinfo = mysqli_fetch_assoc($thread);
		
	}
	$lastbumped = time();
	$trip = "";
	if (($bdata['noname'] == 1) && ($_SESSION['type']==0))
	{
		$name = "Anonymous";
	} else {
		$name = processString($conn, $name, 1);
		if (isset($name['trip']))
		{
			$trip = $name['trip'];
			$name = $name['name'];
		}
	}
	$poster_id = "";
	if ($bdata['ids'] == 1)
	{
		if ($resto != 0)
		{
			$poster_id = mkid($_SERVER['REMOTE_ADDR'], $resto, $board);
		}
		
	}
	//mysqli_query($conn, "INSERT INTO posts_".$board." (date, name, trip, poster_id, email, subject, comment, password, orig_filename, filename, resto, ip, lastbumped, filehash, sticky, sage, locked, capcode, raw)".
	//"VALUES (".time().", '".$name."', '".$trip."', '".mysqli_real_escape_string($conn, $poster_id)."', '".processString($conn, $email)."', '".processString($conn, $subject)."', '".preprocessComment($conn, $comment)."', '".md5($password)."', '".processString($conn, $orig_filename)."', '".$filename."', ".$resto.", '".$_SERVER['REMOTE_ADDR']."', ".$lastbumped.", '".$md5."', 0, 0, 0, 0, 0)");
	//$id = mysqli_insert_id($conn);
	$md5 = mysqli_real_escape_string($conn, $md5);
	mysqli_query($conn, "INSERT INTO posts_".$board." (date, name, trip, poster_id, email, subject, comment, password, orig_filename, filename, resto, ip, lastbumped, filehash, sticky, sage, locked, capcode, raw)".
	"VALUES (".time().", '".$name."', '".$trip."', '".mysqli_real_escape_string($conn, $poster_id)."', '".processString($conn, $email)."', '".processString($conn, $subject)."', '".preprocessComment($conn, $comment)."', '".md5($password)."', '".processString($conn, $orig_filename)."', '".$filename."', ".$resto.", '".$_SERVER['REMOTE_ADDR']."', ".$lastbumped.", '".$md5."', ".$sticky.", 0, ".$locked.", ".$capcode.", ".$raw.")");
	$id = mysqli_insert_id($conn);
	$poster_id = "";
	if ($bdata['ids'] == 1)
	{
		if ($resto == 0)
		{
			$poster_id = mkid($_SERVER['REMOTE_ADDR'], $id, $board);
		}
		
	}
	if ($poster_id != "")
	{
		mysqli_query($conn, "UPDATE posts_".$board." SET poster_id='".mysqli_real_escape_string($conn, $poster_id)."' WHERE id=".$id);
	}
	if ($resto != 0)
	{
		if (($email == "sage") || ($email == "nokosage") || ($email == "nonokosage") || ($tinfo['sage'] == 1) || ($replies > $bdata['bumplimit']))
		{
		
		} else {
			mysqli_query($conn, "UPDATE posts_".$board." SET lastbumped=".time()." WHERE id=".$resto);
		}
	
	
	}
	if (($email == "nonoko") || ($email == "nonokosage"))
	{
		echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."'".'">';
		
	} else {
		if ($resto != 0)
		{
			echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."&t=".$resto."#p".$id."".'">';
		} else {
			echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."&t=".$id."'".'">';
			
		}
	}
	pruneOld($conn, $board);
	if ($resto == 0)
	{
		generateView($conn, $board, $id);
	} else {
		generateView($conn, $board, $resto);
	}
	generateView($conn, $board);
}
?>