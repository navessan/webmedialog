<?php
/*
 +-------------------------------------------------------------------------+
 | PHP web gui for medialog planning mssql database                        |
 +-------------------------------------------------------------------------+
 | Copyright (C) 2011 cprzd.ru                                             |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
*/
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Web Расписание</title>
	<meta content="text/html; charset=windows-1251" name="Content">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<meta name="keywords" content="web, database, gui">
	<meta name="description" content="web database gui">
	<link href="images/favicon.ico" rel="shortcut icon"/>
	<link href="include/main.css" rel="stylesheet">
	
	<script type="text/javascript" src="include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="include/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="include/jscalendar/calendar-setup.js"></script>
</head>

<body bgcolor="#F1F0CB">
<div class="inv">
<table width="100%" align="center">
	<tr>
		<td class="textArea">
			<a href="about.php"> <b>Web Расписаниеs v<?php echo $config["version"]?></b></a>
		</td>
		<td align="right">
			Logged in as <strong>
			<?php 
/*			print db_fetch_cell("select username from web_user_auth where id=" . $_SESSION["sess_user_id"]);
			print " ".$_SESSION["sess_user_code"];
			//$_SESSION["sess_user_id"] = $user["user_id"];
			//print "<br>".$_SESSION["sess_user_code"];
*/			?>
			</strong><?php print " from ".$_SERVER['REMOTE_ADDR'];?>
 			(<a href="logout.php">Logout</a>)&nbsp;
		</td>
	</tr>
</table>
</div>
<table width="50%" align="left">
	<tr>
		<td class="textArea"><a href="planning.php">Отделения</a></td>
		<td class="textArea"><a href="planning.php">planning</a></td>
		<td class="textArea"><a href="date.php">datetest</a></td>
	</tr>
</table>
<br>
<div class="err"><?php display_output_messages();?></div>

