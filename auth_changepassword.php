<?php
/*
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


$user = db_fetch_row("select * from web_user_auth where id=" . $_SESSION["sess_user_id"]);

/* default to !bad_password */
$bad_password = false;

/* set default action */
if (!isset($_REQUEST["action"])) 
{
	$_REQUEST["action"] = ""; 
}

switch ($_REQUEST["action"]) {
case 'changepassword':
	if (($_POST["password"] == $_POST["confirm"]) && ($_POST["password"] != "")) {
		db_execute("insert into web_user_log (username,result,ip) values('" . $user["username"] . "',3,'" . $_SERVER["REMOTE_ADDR"] . "')");
		db_execute("update web_user_auth set must_change_password='',password='" . md5($_POST["password"]) . "' where id=" . $_SESSION["sess_user_id"]);

		kill_session_var("sess_change_password");

		/* ok, at the point the user has been sucessfully authenticated; so we must
		decide what to do next */

		header("Location: index.php");

	}else{
		$bad_password = true;
	}

	break;
}
?>
<html>
<head>
	<title>Login to WebNitgenAccessManager</title>
	<STYLE TYPE="text/css">
	<!--
		BODY, TABLE, TR, TD {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;}
		A {text-decoration: none;}
		A:active { text-decoration: none;}
		A:hover {text-decoration: underline; color: #333333;}
		A:visited {color: Blue;}
	-->
	</style>
</head>

<body onload="document.login.password.focus()">

<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">

<table align="center">
	<tr>
		<td colspan="2"><img src="images/auth_login.gif" border="0" alt=""></td>
	</tr>
	<?php if ($bad_password == true) {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>Your passwords do not match, please retype:</strong></font></td>
	</tr>
	<?php }?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2">
			<strong><font color="#FF0000">*** Forced Password Change ***</font></strong><br><br>
			Please enter a new password:
		</td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password" size="40"></td>
	</tr>
	<tr>
		<td>Confirm:</td>
		<td><input type="password" name="confirm" size="40"></td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td><input type="submit" value="Save"></td>
	</tr>
</table>

<input type="hidden" name="action" value="changepassword">
<input type="hidden" name="ref" value="<?php print $_REQUEST["ref"];?>">

</form>

</body>
</html>
