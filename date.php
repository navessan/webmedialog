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

include("./include/auth.php");
//include_once("./lib/timespan_settings.php");

include_once("./include/header.php");

function print_calendar()
{
	//$month_names = array(1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december');
	$month_names = array(1=>"Январь", 2=>"Февраль", 3=>"Март", 4=>"Апрель", 5=>"Май", 6=>"Июнь", 7=>"Июль", 8=>"Август", 9=>"Сентябрь", 10=>"Октябрь", 11=>"Ноябрь", 12=>"Декабрь");
	$day_names = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС');
	$allow_past = true;
	$date_format = 'Ymd';
	$pagename=basename($_SERVER["PHP_SELF"]);
	//----------
	$month = isset($_GET['month'])? $_GET['month'] : date('n');
	$pd = mktime (0,0,0,$month,1,date('Y'));// timestamp of the first day
	$zd = -(date('w', $pd)? (date('w', $pd)-1) : 6)+1;// monday before
	$kd = date('t', $pd);// last day of moth
	echo '
    <div class="month_title">
      <a href="'.$pagename.'?month='.($month-1).'" class="month_move">&laquo;</a>
      <div class="month_name">'.$month_names[date('n', mktime(0,0,0,$month,1,date('Y')))].' '.date('Y', mktime(0,0,0,$month,1,date('Y'))).'</div>
      <a href="'.$pagename.'?month='.($month+1).'" class="month_move">&raquo;</a>
      <div class="r"></div>
    </div>';
	for ($d=0;$d<7;$d++) {
		echo '
    <div class="week_day">'.$day_names[$d].'</div>';
	}
	echo '
    <div class="r"></div>';
	for ($d=$zd;$d<=$kd;$d++) {
		$i = mktime (0,0,0,$month,$d,date('Y'));
		if ($i >= $pd) {
			$today = (date('Ymd') == date('Ymd', $i))? '_today' : '';
			$minulost = (date('Ymd') >= date('Ymd', $i+86400)) && !$allow_past;
			echo '
    <div class="day'.$today.'">'.($minulost? date('j', $i) : '<a title="'.date('Ymd', $i).'" href="javascript:insertdate(\''.date($date_format, $i).'\')">'.date('j', $i).'</a>').'</div>';
		} else {
			echo '
    <div class="no_day">&nbsp;</div>';
		}
		if (date('w', $i) == 0 && $i >= $pd) {
			echo '
    <div class="r"></div>';
		}
	}
}

print "date hello";

print '<fieldset>
<input type="text" name="date" id="date" />
<a href="javascript:viewcalendar()">calendar</a>
</fieldset>';

print_calendar();
//app_log(" test ");
//test




?>
