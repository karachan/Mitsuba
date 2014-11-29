<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("spamfilter.view");
		$search = "";
		$reason = "";
		$expires = "";
		$regex = 0;
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
$mitsuba->admin->reqPermission("spamfilter.add");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			$continue = 0;
			if (empty($_POST['search'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $search = $_POST['search']; $continue = 1; }
			if (empty($_POST['reason'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $reason = $_POST['reason']; $continue = 1; }
			if ($continue == 1)
			{
				$search = $conn->real_escape_string($_POST['search']);
				$reason = $conn->real_escape_string($_POST['reason']);
				$boards = "";
				
				if (isset($_POST['regex']) ) {
					$regex = 1;
				} else if (isset($_POST['filename'])) {
					$regex = 2;
				}
				
				if ((!empty($_POST['all'])) && ($_POST['all']==1))
				{
					$boards = "%";
				} else {
					if (!empty($_POST['boards']))
					{
						foreach ($_POST['boards'] as $board)
						{
							$boards .= $board.",";
						}
					} else {
						$board = "%";
					}
				}
				if ($boards != "%") { $boards = substr($boards, 0, strlen($boards) - 1); }
				$expires = $_POST['expires'];
				$perma = 1;
				if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
				{
					$expires = "never";
				} else {
					$expirex = $mitsuba->common->parse_time($expires);
					if (($expirex == false) && ($perma == 0))
					{
						echo "<b style='color: red;'>".$lang['mod/fool']."</b>";
					}
				}
				$conn->query("INSERT INTO spamfilter (`search`, `reason`, `boards`, `expires`, `active`, `regex`) VALUES ('".$search."', '".$reason."', '".$boards."', '".$expires."', 1, '".$regex."');");
				$mitsuba->admin->logAction("Added new spamfilter: <div><span style='padding: 2px 5px; border: 1px dotted; display: inline-block;'>".$search."</span> ($reason, $expires) </div>");
			}
			$search = "";
			$reason = "";
			$expires = "";
			$regex = 0;
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['id']))) {
$mitsuba->admin->reqPermission("spamfilter.update");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			$continue = 0;
			if (empty($_POST['search'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $search = $_POST['search']; $continue = 1; }
			if (empty($_POST['reason'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $reason = $_POST['reason']; $continue = 1; }
			if ($continue == 1)
			{
				$search = $conn->real_escape_string($_POST['search']);
				$id = $_POST['id'];
				if (!is_numeric($id)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
				$reason = $conn->real_escape_string($_POST['reason']);
				
				if (isset($_POST['regex']) ) {
					$regex = 1;
				} else if (isset($_POST['filename'])) {
					$regex = 2;
				}
				
				$boards = "";
				if ((!empty($_POST['all'])) && ($_POST['all']==1))
				{
					$boards = "%";
				} else {
					if (!empty($_POST['boards']))
					{
						foreach ($_POST['boards'] as $board)
						{
							$boards .= $board.",";
						}
					} else {
						$board = "%";
					}
				}
				if ($boards != "%") { $boards = substr($boards, 0, strlen($boards) - 1); }
				$expires = $_POST['expires'];
				$perma = 1;
				if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
				{
					$expires = "never";
				} else {
					$expirex = $mitsuba->common->parse_time($expires);
					if (($expirex == false) && ($perma == 0))
					{
						echo "<b style='color: red;'>".$lang['mod/fool']."</b>";
					}
				}

				$old = $conn->query("SELECT * FROM spamfilter WHERE id = ".$id)->fetch_assoc();

				$conn->query("UPDATE spamfilter SET `search`='".$search."', `reason`='".$reason."', `boards`='".$boards."', `expires`='".$expires."', `active`= 1, `regex`='".$regex."' WHERE id=".$id);

				$mitsuba->admin->logAction("Edited spamfilter: <div><span style='padding: 2px 5px; border: 1px dotted; display: inline-block;'>".$old['search']."</span> (".$old['reason'].', '.$old['expires'].") </div><div><span style='padding: 2px 5px; border: 1px dotted; display: inline-block;'>".$search."</span> ($reason, $expires) </div>");	

			}
			$search = "";
			$reason = "";
			$expires = "";
			$regex = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
$mitsuba->admin->reqPermission("spamfilter.delete");
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }

			$old = $conn->query("SELECT * FROM spamfilter WHERE id = ".$n)->fetch_assoc();

			$conn->query("DELETE FROM spamfilter WHERE id=".$n);

			$mitsuba->admin->logAction("Deleted spamfilter: <div><span style='padding: 2px 5px; border: 1px dotted; display: inline-block;'>".$old['search']."</span> (".$old['reason'].', '.$old['expires'].") </div>");
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_spamfilter']); ?>

<table>
<thead>
<tr>
<td><?php echo $lang['mod/wf_search']; ?></td>
<td><?php echo $lang['mod/type']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/boards']; ?></td>
<td><?php echo $lang['mod/expires']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php

	$_data = $conn->query("SELECT short FROM boards");
	$_boards = array();
	while ($row = $_data->fetch_assoc()) $_boards[] = $row['short'];

$result = $conn->query("SELECT * FROM spamfilter ORDER BY search ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td class='text-center'>".htmlspecialchars($row['search'])."</td>";
echo "<td class='text-center'>"; if ($row['regex'] == 1) { echo("Regex");} else if ($row['regex'] == 2) {echo("Filename");} else {echo("Spamfilter");} echo "</td>";
echo "<td class='text-center'>".htmlspecialchars($row['reason'])."</td>";
if ($row['boards']=="%")
{
	echo "<td class='text-center'>All boards</td>";
} else {
	$banBoards = explode(',', $row['boards']);
	if (0.6 * sizeof($_boards) < sizeof($banBoards))
		echo "<td class='text-center'>All boards <b>excluding</b>: ".implode(', ', array_diff($_boards, $banBoards))."</td>";
	else
		echo "<td class='text-center'>".implode(', ', $banBoards)."</td>";
}
echo "<td class='text-center'>".$row['expires']."</td>";
echo "<td class='text-center'><a href='?/spamfilter&d=1&n=".$row['id']."'>".$lang['mod/delete']."</a> <a href='?/spamfilter/edit&n=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<br /><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/sf_add']); ?>

<form action="?/spamfilter" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="mode" value="add">
<?php echo $lang['mod/wf_search']; ?>: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo htmlspecialchars($reason); ?>"/><br />
<?php echo $lang['mod/expires']; ?>: <input type="text" name="expires" value="<?php echo htmlspecialchars($expires); ?>"/><br />
<?php echo $lang['mod/regex'] ?>: <input type="checkbox" name="regex" <?php if($regex == 1) { echo "checked"; }?>"/><br />
<?php echo $lang['mod/fn_spam'] ?>: <input type="checkbox" name="filename" <?php if($regex == 2) { echo "checked"; }?>"/><br />
<br /><br />
<?php
$mitsuba->admin->ui->getBoardList();
?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>