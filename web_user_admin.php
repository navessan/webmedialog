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

include("./include/auth.php");

define("MAX_DISPLAY_PAGES", 21);

$user_actions = array(
	1 => "Delete",
	2 => "Copy",
	3 => "Enable",
	4 => "Disable",
	5 => "Batch Copy"
	);

switch (get_request_var_request("action")) {
	case 'actions':
		form_actions();
		break;

	case 'save':
		form_save();
		break;

	case 'perm_remove':
		perm_remove();
		break;

	case 'user_realms_edit':
		include_once("include/header.php");
		user_edit();
		include_once("include/footer.php");
		break;

	case 'graph_settings_edit':
		include_once("include/header.php");
		user_edit();
		include_once("include/footer.php");
		break;

	case 'graph_perms_edit':
		include_once("include/header.php");
		user_edit();
		include_once("include/footer.php");
		break;

	case 'user_edit':
		include_once("include/header.php");
		user_edit();
		include_once("include/footer.php");
		break;

	default:
		include_once("include/header.php");
		user();
		include_once("include/footer.php");
		break;
}

/* --------------------------
    Actions Function
   -------------------------- */

function form_actions() {
	global $colors, $user_actions, $auth_realms;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		if (get_request_var_post("drp_action") != "2") {
			$selected_items = unserialize(stripslashes(get_request_var_post("selected_items")));
		}

		if (get_request_var_post("drp_action") == "1") { /* delete */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_remove($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "2") { /* copy */
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("selected_items"));
			input_validate_input_number(get_request_var_post("new_realm"));
			/* ==================================================== */

			$new_username = get_request_var_post("new_username");
			$new_realm = get_request_var_post("new_realm", 0);
			$template_user = db_fetch_row("SELECT username, realm FROM web_user_auth WHERE id = " . get_request_var_post("selected_items"));
			$overwrite = array( "full_name" => get_request_var_post("new_fullname") );

			if (strlen($new_username)) {
				if (sizeof(db_fetch_assoc("SELECT username FROM web_user_auth WHERE username = '" . $new_username . "' AND realm = " . $new_realm))) {
					raise_message(19);
				} else {
					if (user_copy($template_user["username"], $new_username, $template_user["realm"], $new_realm, false, $overwrite) === false) {
						raise_message(2);
					} else {
						raise_message(1);
					}
				}
			}
		}

		if (get_request_var_post("drp_action") == "3") { /* enable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_enable($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "4") { /* disable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_disable($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "5") { /* batch copy */
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("template_user"));
			/* ==================================================== */

			$copy_error = false;
			$template = db_fetch_row("SELECT username, realm FROM web_user_auth WHERE id = " . get_request_var_post("template_user"));
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				$user = db_fetch_row("SELECT username, realm FROM web_user_auth WHERE id = " . $selected_items[$i]);
				if ((isset($user)) && (isset($template))) {
					if (user_copy($template["username"], $user["username"], $template["realm"], $user["realm"], true) === false) {
						$copy_error = true;
					}
				}
			}
			if ($copy_error) {
				raise_message(2);
			} else {
				raise_message(1);
			}
		}


		header("Location: web_user_admin.php");
		exit;
	}

	/* loop through each of the users and process them */
	$user_list = "";
	$user_array = array();
	$i = 0;
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			if (get_request_var_post("drp_action") != "2") {
				$user_list .= "<li>" . db_fetch_cell("SELECT username FROM web_user_auth WHERE id=" . $matches[1]) . "<br>";
			}
			$user_array[$i] = $matches[1];
		}

		$i++;
	}

	/* Check for deleting of Graph Export User */
	if ((get_request_var_post("drp_action") == "1") && (sizeof($user_array))) { /* delete */
		$exportuser = read_config_option('export_user_id');
		if (in_array($exportuser, $user_array)) {
			raise_message(22);
			header("Location: web_user_admin.php");
			exit;

		}
	}

	include_once("./include/header.php");

	html_start_box("<strong>" . $user_actions[get_request_var_post("drp_action")] . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='web_user_admin.php' method='post'>\n";

	if ((get_request_var_post("drp_action") == "1") && (sizeof($user_array))) { /* delete */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to delete the following users?</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}
	$user_id = "";
	if ((get_request_var_post("drp_action") == "2") && (sizeof($user_array))) { /* copy */
		$user_id = $user_array[0];
		$user_realm = db_fetch_cell("SELECT realm FROM web_user_auth WHERE id = " . $user_id);

		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Would you like to copy this user?<br><br>
				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Template Username: <i>" . db_fetch_cell("SELECT username FROM web_user_auth WHERE id=" . $user_id) . "</i>
				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
				New Username: ";
		print form_text_box("new_username", "", "", 25);
		print "				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					New Full Name: ";
		print form_text_box("new_fullname", "", "", 35);
		print "				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					New Realm: \n";
		print form_dropdown("new_realm", $auth_realms, "", "", $user_realm, "", 0);
		print "				</td>

			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "3") && (sizeof($user_array))) { /* enable */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to enable the following users?</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "4") && (sizeof($user_array))) { /* disable */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to disable the following users?</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "5") && (sizeof($user_array))) { /* batch copy */
		$usernames = db_fetch_assoc("SELECT id,username FROM web_user_auth WHERE realm = 0 ORDER BY username");
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>Are you sure you want to overwrite the selected users with the selected template users settings and permissions?  Original user Full Name, Password, Realm and Enable status will be retained, all other fields will be overwritten from template user.<br><br></td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Template User: \n";
		print form_dropdown("template_user", $usernames, "username", "id", "", "", 0);
		print "		</td>

			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Users to update:
					$user_list</p>
				</td>
			</tr>\n";
	}

	if (sizeof($user_array) == 0) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one user.</span></td></tr>\n";
		$save_html = "<a href='web_user_admin.php'><img src='images/button_cancel.gif' alt='Cancel' align='absmiddle' border='0'></a>";

	}else{
		$save_html = "<a href='web_user_admin.php'><img src='images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a> <input type='image' src='images/button_yes.gif' alt='Save' align='absmiddle'>";
	}

	print " <tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>";
	if (get_request_var_post("drp_action") == "2") { /* copy */
		print "				<input type='hidden' name='selected_items' value='" . $user_id . "'>\n";
	}else{
		print "				<input type='hidden' name='selected_items' value='" . (isset($user_array) ? serialize($user_array) : '') . "'>\n";
	}
	print "				<input type='hidden' name='drp_action' value='" . get_request_var_post("drp_action") . "'>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/footer.php");

}


/* --------------------------
    Save Function
   -------------------------- */

function form_save() {
	global $settings_graphs;


	/* user management save */
	if (isset($_POST["save_component_user"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("realm"));
		/* ==================================================== */

		if ((get_request_var_post("password") == "") && (get_request_var_post("password_confirm") == "")) {
			$password = db_fetch_cell("SELECT password FROM web_user_auth WHERE id = " . get_request_var_post("id"));
		}else{
			$password = md5(get_request_var_post("password"));
		}

		/* check duplicate username */
		if (sizeof(db_fetch_row("select * from web_user_auth where realm = " . get_request_var_post("realm") . " and username = '" . get_request_var_post("username") . "' and id != " . get_request_var_post("id")))) {
			raise_message(12);
		}

		/* check for guest or template user */
		$username = db_fetch_cell("select username from web_user_auth where id = " . get_request_var_post("id"));
		if ($username != get_request_var_post("username")) {
			if ($username == read_config_option("user_template")) {
				raise_message(20);
			}
			if ($username == read_config_option("guest_user")) {
				raise_message(20);
			}
		}

		/* check to make sure the passwords match; if not error */
		if (get_request_var_post("password") != get_request_var_post("password_confirm")) {
			raise_message(4);
		}

		form_input_validate(get_request_var_post("password"), "password", "" . preg_quote(get_request_var_post("password_confirm")) . "", true, 4);
		form_input_validate(get_request_var_post("password_confirm"), "password_confirm", "" . preg_quote(get_request_var_post("password")) . "", true, 4);

		$save["id"] = get_request_var_post("id");
		$save["username"] = form_input_validate(get_request_var_post("username"), "username", "^[A-Za-z0-9\._\\\@\ -]+$", false, 3);
		$save["full_name"] = form_input_validate(get_request_var_post("full_name"), "full_name", "", true, 3);
		$save["password"] = $password;
		$save["must_change_password"] = form_input_validate(get_request_var_post("must_change_password", ""), "must_change_password", "", true, 3);

		$save["login_opts"] = form_input_validate(get_request_var_post("login_opts"), "login_opts", "", true, 3);
		$save["policy_graphs"] = form_input_validate(get_request_var_post("policy_graphs", get_request_var_post("_policy_graphs")), "policy_graphs", "", true, 3);
		$save["policy_trees"] = form_input_validate(get_request_var_post("policy_trees", get_request_var_post("_policy_trees")), "policy_trees", "", true, 3);
		$save["policy_hosts"] = form_input_validate(get_request_var_post("policy_hosts", get_request_var_post("_policy_hosts")), "policy_hosts", "", true, 3);
		$save["policy_graph_templates"] = form_input_validate(get_request_var_post("policy_graph_templates", get_request_var_post("_policy_graph_templates")), "policy_graph_templates", "", true, 3);
		$save["realm"] = get_request_var_post("realm", 0);
		$save["enabled"] = form_input_validate(get_request_var_post("enabled", ""), "enabled", "", true, 3);

		if (!is_error_message()) {
			$user_id = sql_save($save, "user_auth");

			if ($user_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}

			if (isset($_POST["save_component_realm_perms"])) {
				db_execute("DELETE FROM web_user_auth_realm WHERE user_id = " . $user_id);

				while (list($var, $val) = each($_POST)) {
					if (eregi("^[section]", $var)) {
						if (substr($var, 0, 7) == "section") {
						    db_execute("REPLACE INTO web_user_auth_realm (user_id,realm_id) VALUES (" . $user_id . "," . substr($var, 7) . ")");
						}
					}
				}
			}
		}
	}

	/* redirect to the appropriate page */
	if (is_error_message()) {
		header("Location: web_user_admin.php?action=user_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}else{
		header("Location: web_user_admin.php");
	}
}



function user_realms_edit() {
	global $colors, $user_auth_realms;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	?>
	<table width='100%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'>Realm permissions control which sections of Cacti this user will have access to.</span>
			</td>
		</tr>
	</table>
	<?php

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	print "	<tr bgcolor='#" . $colors["header"] . "'>
			<td class='textHeaderDark'><strong>Realm Permissions</strong></td>
			<td width='1%' align='center' bgcolor='#819bc0' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='all' title='Select All' onClick='SelectAll(\"section\",this.checked)'></td>\n
		</tr>\n";

	?>

	<tr>
		<td colspan="2" width="100%">
			<table width="100%">
				<tr>
					<td align="top" width="50%">
						<?php
						$i = 0;
						while (list($realm_id, $realm_name) = each($user_auth_realms)) {
							if (sizeof(db_fetch_assoc("SELECT realm_id FROM web_user_auth_realm WHERE user_id = " . get_request_var("id", 0) . " AND realm_id = " . $realm_id)) > 0) {
								$old_value = "on";
							}else{
								$old_value = "";
							}

							$column1 = floor((sizeof($user_auth_realms) / 2) + (sizeof($user_auth_realms) % 2));

							if ($i == $column1) {
								print "</td><td valign='top' width='50%'>";
							}

							form_checkbox("section" . $realm_id, $old_value, $realm_name, "", "", "", (!empty($_GET["id"]) ? 1 : 0)); print "<br>";

							$i++;
						}
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?php
	html_end_box();

	form_hidden_box("save_component_realm_perms","1","");
}

/* --------------------------
    User Administration
   -------------------------- */

function user_edit() {
	global $colors, $fields_user_user_edit_host;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$user = db_fetch_row("SELECT * FROM web_user_auth WHERE id = " . get_request_var("id"));
		$header_label = "[edit: " . $user["username"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>User Management</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_user_user_edit_host, (isset($user) ? $user : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		/* draw user admin nav tabs */
		?>
		<table class='tabs' width='100%' cellspacing='0' cellpadding='3' align='center'>
			<tr>
				<td width='1'></td>
				<td <?php print (((get_request_var("action") == "user_realms_edit") || (get_request_var("action") == "user_edit")) ? "bgcolor='silver'" : "bgcolor='#DFDFDF'");?> nowrap='nowrap' width='150' align='center' class='tab'>
					<span class='textHeader'><a href='web_user_admin.php?action=user_realms_edit&id=<?php print $_GET["id"];?>'>Realm Permissions</a></span>
				</td>
				<td></td>
			</tr>
		</table>
		<?php
	}

	if (get_request_var("action") == "graph_settings_edit") {
		graph_settings_edit();
	}elseif (get_request_var("action") == "user_realms_edit") {
		user_realms_edit();
	}elseif (get_request_var("action") == "graph_perms_edit") {
		graph_perms_edit();
	}else{
		user_realms_edit();
	}

	form_save_button("user_admin.php");
}

function user() {
	global $colors, $auth_realms, $user_actions;
	$rows_per_page=50;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_user_admin_current_page");
		kill_session_var("sess_user_admin_filter");
		kill_session_var("sess_user_admin_sort_column");
		kill_session_var("sess_user_admin_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_user_admin_current_page", "1");
	load_current_session_value("filter", "sess_user_admin_filter", "");
	load_current_session_value("sort_column", "sess_user_admin_sort_column", "username");
	load_current_session_value("sort_direction", "sess_user_admin_sort_direction", "ASC");

	html_start_box("<strong>User Management</strong>", "100%", $colors["header"], "3", "center", "web_user_admin.php?action=user_edit");

	?>
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_user_admin">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="50">
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print clean_html_output(get_request_var_request("filter"));?>">
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="image" src="images/button_go.gif" alt="Go" border="0" align="absmiddle">
						<input type="image" src="images/button_clear.gif" name="clear" alt="Clear" border="0" align="absmiddle">
					</td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var_request("filter"))) {
		$sql_where = "WHERE (web_user_auth.username LIKE '%" . get_request_var_request("filter") . "%' OR web_user_auth.full_name LIKE '%" . get_request_var_request("filter") . "%')";
	}else{
		$sql_where = "";
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(web_user_auth.id)
		FROM web_user_auth
		$sql_where");

	$user_list = db_fetch_assoc("SELECT top "
		.$rows_per_page
		." id,
		web_user_auth.username,
		full_name,
		enabled,
		ngac_user_code,
		ngac_terminal_id,
		max(time) as dtime
		FROM web_user_auth
		LEFT JOIN web_user_log ON (web_user_auth.id = web_user_log.user_id)
		$sql_where
		group by web_user_auth.id, web_user_auth.username, full_name, realm,  enabled,  ngac_user_code, ngac_terminal_id
		ORDER BY " . get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction") .
		"");//" LIMIT " . (read_config_option("num_rows_device") * (get_request_var_request("page") - 1)) . "," . read_config_option("num_rows_device"));

	/* generate page list */
	$url_page_select = get_page_list(get_request_var_request("page"), MAX_DISPLAY_PAGES, $rows_per_page, $total_rows, "web_user_admin.php?filter=" . get_request_var_request("filter"));

	$nav = "<tr bgcolor='#" . $colors["header"] . "'>
		<td colspan='7'>
			<table width='100%' cellspacing='0' cellpadding='0' border='0'>
				<tr>
					<td align='left' class='textHeaderDark'>
						<strong>&lt;&lt; "; if (get_request_var_request("page") > 1) { $nav .= "<a class='linkOverDark' href='user_admin.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page") - 1) . "'>"; } $nav .= "Previous"; if (get_request_var_request("page") > 1) { $nav .= "</a>"; } $nav .= "</strong>
					</td>\n
					<td align='center' class='textHeaderDark'>
						Showing Rows " . (($rows_per_page * (get_request_var_request("page") - 1)) + 1) . " to " . ((($total_rows < $rows_per_page) || ($total_rows < ($rows_per_page * get_request_var_request("page")))) ? $total_rows : ($rows_per_page * get_request_var_request("page"))) . " of $total_rows [$url_page_select]
					</td>\n
					<td align='right' class='textHeaderDark'>
						<strong>"; if ((get_request_var_request("page") * $rows_per_page) < $total_rows) { $nav .= "<a class='linkOverDark' href='web_user_admin.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page") + 1) . "'>"; } $nav .= "Next"; if ((get_request_var_request("page") * $rows_per_page) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
					</td>\n
				</tr>
			</table>
		</td>
		</tr>\n";

	print $nav;

	$display_text = array(
		"username" => array("User Name", "ASC"),
		"full_name" => array("Full Name", "ASC"),
		"enabled" => array("Enabled", "ASC"),
		"realm" => array("Realm", "ASC"),
		"ngac_user_code" => array("ngac_user_code", "ASC"),
		"ngac_terminal_id" => array("ngac_terminal_id", "ASC"),
		"dtime" => array("Last Login", "DESC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));


	$i = 0;
	if (sizeof($user_list) > 0) {
		foreach ($user_list as $user) {
			if (empty($user["dtime"]) || ($user["dtime"] == "12/31/1969")) {
				$last_login = "N/A";
			}else{
				$last_login = strftime("%A, %B %d, %Y %H:%M:%S ", strtotime($user["dtime"]));;
			}
			if ($user["enabled"] == "on") {
				$enabled = "Yes";
			}else{
				$enabled = "No";
			}

			form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $user["id"]); $i++;
			form_selectable_cell("<a class='linkEditMain' href='web_user_admin.php?action=user_edit&id=" . $user["id"] . "'>" .
			(strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["username"]) : $user["username"])
			, $user["id"]);
			form_selectable_cell((strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["full_name"]) : $user["full_name"]), $user["id"]);
			form_selectable_cell($enabled, $user["id"]);
			form_selectable_cell($auth_realms[$user["realm"]], $user["id"]);
			if ($user["ngac_user_id"] == "1") {
				form_selectable_cell("ALLOW", $user["id"]);
			}else{
				form_selectable_cell("DENY", $user["id"]);
			}
			form_selectable_cell($last_login, $user["id"]);
			form_checkbox_cell($user["username"], $user["id"]);
			form_end_row();
		}

		print $nav;
	}else{
		print "<tr><td><em>No Users</em></td></tr>";
	}
	html_end_box(false);

	draw_actions_dropdown($user_actions);

}
?>

