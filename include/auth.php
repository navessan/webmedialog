<?php
/*
 PHP web gui for mssql database
 +-------------------------------------------------------------------------+
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
include("./include/global.php");


	/* handle change password dialog */
/*	if (isset($_SESSION['sess_change_password'])) 
	{
		header ("Location: auth_changepassword.php?ref=" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"));
		exit;
	}

	/* don't even bother with the guest code if we're already logged in */
/*	if ((isset($guest_account)) && (empty($_SESSION["sess_user_id"]))) {
		$guest_user_id = db_fetch_cell("select id from web_user_auth where username='" . read_config_option("guest_user") . "' and realm = 0 and enabled = 'on'");

		/* cannot find guest user */
/*		if (!empty($guest_user_id)) {
			$_SESSION["sess_user_id"] = $guest_user_id;
		}
	}

	/* if we are a guest user in a non-guest area, wipe credentials */
/*	if (!empty($_SESSION["sess_user_id"])) {
		if ((!isset($guest_account)) && (db_fetch_cell("select id from web_user_auth where username='" . read_config_option("guest_user") . "'") == $_SESSION["sess_user_id"])) {
			kill_session_var("sess_user_id");
		}
	}

	if (empty($_SESSION["sess_user_id"])) {
		include("./auth_login.php");
		exit;
	}else
	if (!empty($_SESSION["sess_user_id"])) 
	{
		$realm_id = 0;

		if (isset($user_auth_realm_filenames{basename($_SERVER["PHP_SELF"])})) 
		{
			$realm_id = $user_auth_realm_filenames{basename($_SERVER["PHP_SELF"])};
		}

/*		if ((!db_fetch_assoc("select
			web_user_auth_realm.realm_id
			from
			web_user_auth_realm
			where web_user_auth_realm.user_id='" . $_SESSION["sess_user_id"] . "'
			and web_user_auth_realm.realm_id='$realm_id'")) || (empty($realm_id))) {

			?>
			<html>
			<head>
				<title>WebManger</title>
				<link href="include/main.css" rel="stylesheet">
			</style>
			</head>

			<br><br>

			<table width="450" align='center'>
				<tr>
					<td colspan='2'><img src='images/auth_deny.gif' border='0' alt='Access Denied'></td>
				</tr>
				<tr height='10'><td></td></tr>
				<tr>
					<td class='textArea' colspan='2'>You are not permitted to access this section. If you feel that you
					need access to this particular section, please contact the administrator.</td>
				</tr>
				<tr>
					<td class='textArea' colspan='2' align='center'>( <a href='' onclick='javascript: history.back();'>Return</a> | <a href='index.php'>Login</a> )</td>
				</tr>
			</table>

			</body>
			</html>
			<?php
			exit;
		}
	}
*/
?>
