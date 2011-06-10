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

/* set default action */
if (isset($_REQUEST["action"])) {
	$action = $_REQUEST["action"];
}else{
	$action = "";
}

/* Get the username */
	if ($action == "login") {
		/* LDAP and Builtin get username from Form */
		$username = get_request_var_post("login_username");
	}else{
		$username = "";
	}

$username = sanitize_search_string($username);

/* process login */
$copy_user = false;
$user_auth = false;
$user_enabled = 1;
$user = array();
$realm = 0;
if ($action == 'login') 
{

	/* Builtin Auth */
	$user = db_fetch_row("SELECT * FROM web_user_auth WHERE username = '" . $username . "' AND password = '" . md5(get_request_var_post("login_password")) . "' AND realm = 0");
}


	/* Process the user  */
	if (sizeof($user) > 0) 
	{
		app_log("LOGIN: User '" . $user["username"] . "' Authenticated", false, "AUTH");
		//db_execute("INSERT INTO web_user_log (username,user_id,result,ip,time) VALUES ('" . $username ."'," . $user["id"] . ",1,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");
		db_execute("INSERT INTO web_user_log (username,user_id,result,ip,time) VALUES ('" . $username ."'," . $user["id"] . ",1,'" . $_SERVER["REMOTE_ADDR"] . "',GETDATE())");
		/* is user enabled */
		$user_enabled = $user["enabled"];
		if ($user_enabled != "on") 
		{
			/* Display error */
			auth_display_custom_error_message("Access Denied, user account disabled.");
			exit;
		}

		/* set the php session */
		$_SESSION["sess_user_id"] = $user["id"];
		$_SESSION["sess_ngac_user_code"] = $user["ngac_user_code"];
		$_SESSION["sess_ngac_terminal_id"] = $user["ngac_terminal_id"];

		/* handle "force change password" */
		if ($user["must_change_password"] == "on") 
		{
			$_SESSION["sess_change_password"] = true;
		}

		/* ok, at the point the user has been sucessfully authenticated; so we must
		decide what to do next */
		switch ($user["login_opts"]) 
		{
			case '1': /* referer */
					$referer = "index.php";
					header("Location: " . $referer);
				break;
			case '2': /* default console page */
				header("Location: index.php"); break;
		}
		exit;

	}else
	{
		if ($user_auth) 
		{
			/* No guest account defined */
			auth_display_custom_error_message("Access Denied, please contact you Administrator.");
			app_log("LOGIN: Access Denied, No guest enabled or template user to copy", false, "AUTH");
			exit;
		}else
		{
			/* BAD username/password builtin and LDAP */
			//db_execute("INSERT INTO web_user_log (username,user_id,result,ip,time) VALUES ('" . $username . "',0,0,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");
			db_execute("INSERT INTO web_user_log (username,user_id,result,ip,time) VALUES ('" . $username . "',0,0,'" . $_SERVER["REMOTE_ADDR"] . "',GETDATE())");
		}
	}


/* auth_display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @arg $message - the actual text of the error message to display */
function auth_display_custom_error_message($message) {
	/* kill the session */
	setcookie(session_name(),"",time() - 3600,"/");
	/* print error */
	print "<html>\n<head>\n";
        print "     <title>" . "WebNitgenAccessManager" . "</title>\n";
        print "     <link href=\"include/main.css\" rel=\"stylesheet\">";
	print "</head>\n";
	print "<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n<br><br>\n";
	display_custom_error_message($message);
        print "</body>\n</html>\n";
}

?>

<html>
<head>
	<title>Login to Webnitgen</title>
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
<body bgcolor="#FFFFFF" onload="document.login.login_username.focus()">
	<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">
	<input type="hidden" name="action" value="login">
	<table align="center">
		<tr>
			<td colspan="2"><img src="images/auth_login.gif" border="0" alt=""></td>
		</tr>

		<?php
		if ($action == "login") {?>
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><font color="#FF0000"><strong>Invalid User Name/Password Please Retype</strong></font></td>
		</tr>
		<?php }
		if ($user_enabled == "0") {?>
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><font color="#FF0000"><strong>User Account Disabled</strong></font></td>
		</tr>
		<?php } ?>

		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2">Please enter your user name and password below:</td>
		</tr>
		<tr height="10"><td></td></tr>
		<tr>
			<td>User Name:</td>
			<td><input type="text" name="login_username" size="40" style="width: 295px;" value="<?php print clean_html_output($username); ?>"></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="login_password" size="40" style="width: 295px;"></td>
		</tr>
		<tr height="10"><td></td></tr>
		<tr>
			<td><input type="submit" value="Login"></td>
		</tr>
	</table>
	</form>
</body>
</html>
