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


$logfile_options = array(1 =>
	"Logfile Only",
	"Logfile and Syslog/Eventlog",
	"Syslog/Eventlog Only");




$logfile_verbosity = array(
	VERBOSITY_NONE => "NONE - Syslog Only if Selected",
	VERBOSITY_LOW => "LOW - Statistics and Errors",
	VERBOSITY_MEDIUM => "MEDIUM - Statistics, Errors and Results",
	VERBOSITY_HIGH => "HIGH - Statistics, Errors, Results and Major I/O Events",
	VERBOSITY_DEBUG => "DEBUG - Statistics, Errors, Results, I/O and Program Flow",
	VERBOSITY_DEVDBG => "DEVEL - Developer DEBUG Level");	
	

$cdef_item_types = array(
	1 => "Function",
	2 => "Operator",
	4 => "Special Data Source",
	5 => "Another CDEF",
	6 => "Custom String");



$log_tail_lines = array(
	-1 => "All Lines",
	10 => "10 Lines",
	15 => "15 Lines",
	20 => "20 Lines",
	50 => "50 Lines",
	100 => "100 Lines",
	200 => "200 Lines",
	500 => "500 Lines",
	1000 => "1000 Lines",
	2000 => "2000 Lines",
	3000 => "3000 Lines",
	5000 => "5000 Lines",
	10000 => "10000 Lines"
	);

$item_rows = array(
	10 => "10",
	15 => "15",
	20 => "20",
	25 => "25",
	30 => "30",
	40 => "40",
	50 => "50",
	100 => "100",
	250 => "250",
	500 => "500",
	1000 => "1000",
	2000 => "2000",
	5000 => "5000"
	);

$graphs_per_page = array(
	4 => "4",
	6 => "6",
	8 => "8",
	10 => "10",
	14 => "14",
	20 => "20",
	24 => "24",
	30 => "30",
	40 => "40",
	50 => "50",
	100 => "100"
	);

$page_refresh_interval = array(
	5 => "5 Seconds",
	10 => "10 Seconds",
	20 => "20 Seconds",
	30 => "30 Seconds",
	60 => "1 Minute",
	300 => "5 Minutes",
	600 => "10 Minutes",
	9999999 => "Never");

$user_auth_realms = array(
	1 => "User Administration",
	2 => "View User",
	3 => "Edit User"
	);

$user_auth_realm_filenames = array(
	"about.php" => 2,
	"empl.php" => 2,
	"find_id.php" => 1,
	"nitgen_auth_log.php" => 2,
	"index.php" => 2,
	"settings.php" => 1,
	"web_user_admin.php" => 1,
	"utilities.php" => 1,
	"smtp_servers.php" => 1,
	"email_templates.php" => 1,
	"event_queue.php" => 1,
	"smtp_queue.php" => 1,
	"logout.php" => 1
	);




/* file: user_admin.php, action: user_edit (host) */
$fields_user_user_edit_host = array(
	"username" => array(
		"method" => "textbox",
		"friendly_name" => "User Name",
		"description" => "The login name for this user.",
		"value" => "|arg1:username|",
		"max_length" => "255"
		),
	"full_name" => array(
		"method" => "textbox",
		"friendly_name" => "Full Name",
		"description" => "A more descriptive name for this user, that can include spaces or special characters.",
		"value" => "|arg1:full_name|",
		"max_length" => "255"
		),
	"password" => array(
		"method" => "textbox_password",
		"friendly_name" => "Password",
		"description" => "Enter the password for this user twice. Remember that passwords are case sensitive!",
		"value" => "",
		"max_length" => "255"
		),
	"enabled" => array(
		"method" => "checkbox",
		"friendly_name" => "Enabled",
		"description" => "Determines if user is able to login.",
		"value" => "|arg1:enabled|",
		"default" => ""
		),
	"grp1" => array(
		"friendly_name" => "Account Options",
		"method" => "checkbox_group",
		"description" => "Set any user account-specific options here.",
		"items" => array(
			"must_change_password" => array(
				"value" => "|arg1:must_change_password|",
				"friendly_name" => "User Must Change Password at Next Login",
				"form_id" => "|arg1:id|",
				"default" => ""
				)
			)
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_user" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

$graph_timespans = array(
	GT_LAST_HALF_HOUR => "Last Half Hour",
	GT_LAST_HOUR => "Last Hour",
	GT_LAST_2_HOURS => "Last 2 Hours",
	GT_LAST_4_HOURS => "Last 4 Hours",
	GT_LAST_6_HOURS =>"Last 6 Hours",
	GT_LAST_12_HOURS =>"Last 12 Hours",
	GT_LAST_DAY =>"Last Day",
	GT_LAST_2_DAYS =>"Last 2 Days",
	GT_LAST_3_DAYS =>"Last 3 Days",
	GT_LAST_4_DAYS =>"Last 4 Days",
	GT_LAST_WEEK =>"Last Week",
	GT_LAST_2_WEEKS =>"Last 2 Weeks",
	GT_LAST_MONTH =>"Last Month",
	GT_LAST_2_MONTHS =>"Last 2 Months",
	GT_LAST_3_MONTHS =>"Last 3 Months",
	GT_LAST_4_MONTHS =>"Last 4 Months",
	GT_LAST_6_MONTHS =>"Last 6 Months",
	GT_LAST_YEAR =>"Last Year",
	GT_LAST_2_YEARS =>"Last 2 Years",
	GT_DAY_SHIFT => "Day Shift",
	GT_THIS_DAY => "This Day",
	GT_THIS_WEEK => "This Week",
	GT_THIS_MONTH => "This Month",
	GT_THIS_YEAR => "This Year",
	GT_PREV_DAY => "Previous Day",
	GT_PREV_WEEK => "Previous Week",
	GT_PREV_MONTH => "Previous Month",
	GT_PREV_YEAR => "Previous Year"
	);

$graph_timeshifts = array(
	GTS_HALF_HOUR => "30 Min",
	GTS_1_HOUR => "1 Hour",
	GTS_2_HOURS => "2 Hours",
	GTS_4_HOURS => "4 Hours",
	GTS_6_HOURS => "6 Hours",
	GTS_12_HOURS => "12 Hours",
	GTS_1_DAY => "1 Day",
	GTS_2_DAYS => "2 Days",
	GTS_3_DAYS => "3 Days",
	GTS_4_DAYS => "4 Days",
	GTS_1_WEEK => "1 Week",
	GTS_2_WEEKS => "2 Weeks",
	GTS_1_MONTH => "1 Month",
	GTS_2_MONTHS => "2 Months",
	GTS_3_MONTHS => "3 Months",
	GTS_4_MONTHS => "4 Months",
	GTS_6_MONTHS => "6 Months",
	GTS_1_YEAR => "1 Year",
	GTS_2_YEARS => "2 Years"
	);

$graph_weekdays = array(
	WD_SUNDAY => date("l", strtotime("Sunday")),
	WD_MONDAY => date("l", strtotime("Monday")),
	WD_TUESDAY => date("l", strtotime("Tuesday")),
	WD_WEDNESDAY => date("l", strtotime("Wednesday")),
	WD_THURSDAY => date("l", strtotime("Thursday")),
	WD_FRIDAY => date("l", strtotime("Friday")),
	WD_SATURDAY => date("l", strtotime("Saturday"))
	);

$graph_dateformats = array(
	GD_MO_D_Y => "Month Number, Day, Year",
	GD_MN_D_Y => "Month Name, Day, Year",
	GD_D_MO_Y => "Day, Month Number, Year",
	GD_D_MN_Y => "Day, Month Name, Year",
	GD_Y_MO_D => "Year, Month Number, Day",
	GD_Y_MN_D => "Year, Month Name, Day"
	);

$graph_datechar = array(
	GDC_HYPHEN => "-",
	GDC_SLASH => "/"
	);
	
/* setting information */
$settings = array(
	"path" => array(
		"dependent_header" => array(
			"friendly_name" => "Required Tool Paths",
			"method" => "spacer",
			),
		"logging_header" => array(
			"friendly_name" => "Logging",
			"method" => "spacer",
			),
		"path_log" => array(
			"friendly_name" => "Log File Path",
			"description" => "The path to your log file",
			"method" => "filepath",
			"default" => $config["base_path"] . "/log/web.log",
			"max_length" => "255"
			)
		),
	"general" => array(
		"logging_header" => array(
			"friendly_name" => "Event Logging",
			"method" => "spacer",
			),
		"log_destination" => array(
			"friendly_name" => "Log File Destination",
			"description" => "How will Cacti handle event logging.",
			"method" => "drop_array",
			"default" => 1,
			"array" => $logfile_options,
			),
		"log_verbosity" => array(
			"friendly_name" => "Logging Level",
			"description" => "What level of detail do you want sent to the log file.  WARNING: Leaving in any other status than NONE or LOW can exaust your disk space rapidly.",
			"method" => "drop_array",
			"default" => VERBOSITY_LOW,
			"array" => $logfile_verbosity,
			),
		"check_web_access" => array(
			"friendly_name" => "Access Level",
			"description" => "",
			"default" => "1",
			)
		)
	);
	
	
?>
