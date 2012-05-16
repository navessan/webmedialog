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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

if(get_request_var("xml")==1) 
	header('Content-type: text/xml');
else 
	include_once("./include/header.php");

switch ($_REQUEST["action"]) {
	case 'save':

		break;
	case 'actions':

		break;
	case 'dep':
		if(get_request_var("xml")==1) 
			show_plan_xml();
		else 
			show_plan();

		break;
	case 'plsubj':
		if(get_request_var("xml")==1) 
			show_plsubj_xml();
		else 
			show_plsubj();

		break;
	case 'plday':

		pl_day_show();
		break;
		
	default:
		if(get_request_var("xml")==1)
			show_deps_xml();
		else
			show_deps();
		break;
}

if(!get_request_var("xml")==1) 
	include_once("./include/footer.php");


/**
 * add ":" char delimeter to time string
 * @param string $time
 * @return string
 */
function parse_time($time)
{
	$msg="";
	if(is_null($time)||($time==0)||($time=="0"))
		$msg="00:00";
	else
	{
		$msg=substr($time, 0,strlen($time)-2).":"
			.substr($time,strlen($time)-2,strlen($time));
	}
	if(strlen($msg)==4)
		$msg="0".$msg;
	return $msg;
}

/**
 * форматирует входную строку времени в вид 00:00
 * @param string $time
 * @return string
 */
function parse_time2($time)
{
	$msg="";
	$stamp1=0;

	if(is_null($time)||($time==0)||($time=="0"))
		return "00:00";
	
	$time_arr=explode(":",$time, 2);
	
	$hour=0;
	$minute=0;
	
	if(count($time_arr)==2)
	{
		$hour=$time_arr[0];
		$minute=$time_arr[1];
	}
	else 
	{
		if(strlen($time)>2){
			$hour=substr($time, 0,strlen($time)-2);
			$minute=substr($time,strlen($time)-2,strlen($time));
		}else
			$minute=$time;
	}
	$stamp1=mktime($hour,$minute);
	
	$msg=date("H:i",$stamp1);
		
	return $msg;
}

function add_time($time, $minutes_offset)
{
	$msg="";
	$stamp1=0;
	
	$time=parse_time2($time);
	
	$time_arr=explode(":",$time, 2);
		
	$hour=$time_arr[0];
	$minute=$time_arr[1];
	
	$stamp1=mktime($hour,$minute+$minutes_offset);
	
	$msg=date("H:i",$stamp1);
	return $msg;
}

/**
 * Convert Delhi Tcolor to HTML color format
 * @param int $delphi_color
 * @return string
 */
function delphi_color_to_html($delphi_color)
{
	$delphi_color=(int)$delphi_color;

	//TColor is bgr, but not rgb
	$red	= $delphi_color & 0x0000FF;
	$green	= $delphi_color & 0x00FF00;
	$blue	= $delphi_color & 0xFF0000;

	$html_color=($red<<16) + $green + ($blue>>16);	//rgb

	$html_color=dechex($html_color);

	return $html_color;
}

function show_deps()
{

	print '<p>Список отделений:</p>';

	$arr = pl_param_get_array();

	if (sizeof($arr) > 0)
	{
		pl_param_show_array($arr);
	}else
	{
		print "<tr><td><em>Нет доступа к отделениям</em></td></tr>";
	}


}

function show_deps_xml()
{
	$arr = pl_param_get_array();

	if (sizeof($arr) > 0)
	{
		$in_charset="cp1251";
		$out_charset="UTF-8";

		$arr=recursive_iconv($in_charset, $out_charset, $arr);
		$xml=export_xml($arr,"pl_params","pl_param",'1.0',$out_charset);

		if($xml)
			echo $xml;
		else
			echo "xml failed\n";
	}else
	{
		//print "<tr><td><em>Нет доступа к отделениям</em></td></tr>";
	}


}

function pl_param_get_array()
{
	global $WEB_MEDECINS_ID;
	$sql_where="";

	$tsql="/* web 'planparam' */
 SELECT 
 PL_PARAM.PL_PARAM_ID, PL_PARAM.NOM 
 FROM
 PL_PARAM PL_PARAM 
 LEFT OUTER JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID 
 JOIN MED_PLPARAM MED_PLPARAM ON PL_PARAM.PL_PARAM_ID = MED_PLPARAM.PL_PARAM_ID 
 WHERE
 (MED_PLPARAM.MEDECINS_ID=(".$WEB_MEDECINS_ID.")) and
 (PL_PARAM.ARCHIVE not in (1)) 
 order by nom";

	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function pl_param_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_PARAM_ID</td><td>Name</td>";
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_PARAM_ID'];
			print "</td><td>";
			print "<a href=\"planning.php?action=dep&id=".$row['PL_PARAM_ID']."\">";
			print $row['NOM'];
			print "</a></td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";


	}
}

function show_plan()
{
	global $WEB_MEDECINS_ID;
	print '<p>Список расписаний:</p>';
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (empty($_GET["id"]))
	{
		print '<p>Не выбрано отделение:</p>';
		return;
	}
	else
	$PL_PARAM_ID=$_GET["id"];
	
	//-----------
	//show department name and check user access
	$tsql="SELECT PL_PARAM.PL_PARAM_ID, PL_PARAM.NOM , FM_ORG.LABEL ".
		"FROM PL_PARAM PL_PARAM ". 
		"LEFT OUTER JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID ". 
		"JOIN MED_PLPARAM MED_PLPARAM ON PL_PARAM.PL_PARAM_ID = MED_PLPARAM.PL_PARAM_ID ". 
		"WHERE ".
		"(MED_PLPARAM.MEDECINS_ID=(".$WEB_MEDECINS_ID.")) and PL_PARAM.PL_PARAM_ID=".$PL_PARAM_ID.
		" and (PL_PARAM.ARCHIVE not in (1)) ". 
		"order by nom";

	$arr=db_fetch_row($tsql);
	if (sizeof($arr) > 0)
	{
		print "<p><a href=\"planning.php\">".$arr['LABEL']."</a> >> ".$arr['NOM']."</p>";
	}
	else 
	{
		print "<tr><td><em>Нет доступа к расписаниям</em></td></tr>";
		//exit
		return;		
	}
	
	//----------------

	$arr = pl_subj_param_get_array($PL_PARAM_ID);

	if (sizeof($arr) > 0)
	{
		pl_subj_param_show_array($arr);
	}else
	{
		print "<tr><td><em>Нет доступа к расписаниям</em></td></tr>";
	}
}

function show_plan_xml()
{
	global $WEB_MEDECINS_ID;
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (empty($_GET["id"]))
	{
		//print '<p>Не выбрано отделение:</p>';
		return;
	}
	else
	$PL_PARAM_ID=$_GET["id"];
	
	//-----------
	//show department name and check user access
	$tsql="SELECT PL_PARAM.PL_PARAM_ID, PL_PARAM.NOM , FM_ORG.LABEL ".
		"FROM PL_PARAM PL_PARAM ". 
		"LEFT OUTER JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID ". 
		"JOIN MED_PLPARAM MED_PLPARAM ON PL_PARAM.PL_PARAM_ID = MED_PLPARAM.PL_PARAM_ID ". 
		"WHERE ".
		"(MED_PLPARAM.MEDECINS_ID=(".$WEB_MEDECINS_ID.")) and PL_PARAM.PL_PARAM_ID=".$PL_PARAM_ID.
		" and (PL_PARAM.ARCHIVE not in (1)) ". 
		"order by nom";

	$arr=db_fetch_row($tsql);
	if (sizeof($arr) > 0)
	{
		//print "<p><a href=\"planning.php\">".$arr['LABEL']."</a> >> ".$arr['NOM']."</p>";
	}
	else 
	{
		//print "<tr><td><em>Нет доступа к расписаниям</em></td></tr>";
		//exit
		return;		
	}
	
	//----------------

	$arr = pl_subj_param_get_array($PL_PARAM_ID);

	if (sizeof($arr) > 0)
	{
		$in_charset="cp1251";
		$out_charset="UTF-8";

		$arr=recursive_iconv($in_charset, $out_charset, $arr);
		$xml=export_xml($arr,"pl_subjects","pl_subj",'1.0',$out_charset);

		if($xml)
			echo $xml;
		else
			echo "xml failed\n";

	}else
	{
		//print "<tr><td><em>Нет доступа к расписаниям</em></td></tr>";
	}
}

function pl_subj_param_get_array($PL_PARAM_ID)
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_PARAM_ID);
	/* ==================================================== */
	if(!$PL_PARAM_ID>0) return null;
	
	//-----------
	//запрос списка расписаний для выбранного отделения

	$tsql="select PL_SUBJ.PL_SUBJ_ID, PL_SUBJ.NAME, PL_SUBJ.LIEU, PL_SUBJ.PL_AGEND_ID1 ".
	",  PL_SUBJ.START_FROM, PL_SUBJ.FORBID_AFTER_DATE ".
	"from PL_SUBJ_PARAM inner join PL_SUBJ on PL_SUBJ_PARAM.PL_SUBJ_ID=PL_SUBJ.PL_SUBJ_ID ".
	"where PL_PARAM_ID in(".$PL_PARAM_ID.") ".
	"and(PL_SUBJ.ARCHIVE not in (1)) ".
	//TODO "and PL_SUBJ.web_access>0 ".
	"order by NAME";


	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function pl_subj_param_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_SUBJ_ID</td><td>Name</td><td>LIEU</td>";
		if(1)
		{
			print "<td>PL_AGEND_ID1</td>";
			//print "<td>PL_AGEND_ID1</td>";
			//print "<td>PL_AGEND_ID1</td>";			
		}
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_SUBJ_ID'];
			print "</td><td>";
			print "<a href=\"planning.php?action=plsubj&id=".$row['PL_SUBJ_ID']."\">";
			print $row['NAME'];
			print "</a></td>";
			print "<td>".$row['LIEU']."</td>";
			if(1)
			{
				print "<td>".$row['PL_AGEND_ID1']."</td>";
			}
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}
}

function show_plsubj()
{
	global $config;
	print '<p>Список дней для сетки расписания:</p>';
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */
	
	$PL_SUBJ_ID=0;

	if (!empty($_GET["id"]))
	{
			$PL_SUBJ_ID=$_GET["id"];
	}
	
	if (!$PL_SUBJ_ID>0)
	{
		print '<p>Не выбрана сетка расписания:</p>';
		return;
	}
	
	//-----------
	//show planning name
	$tsql="select PL_SUBJ.PL_SUBJ_ID, PL_SUBJ.NAME SUBJ_NAME, PL_AGEND_ID1 
	 , PL_PARAM.PL_PARAM_ID DEP_ID, PL_PARAM.NOM DEP_NAME
	 , FM_ORG.LABEL ORG_NAME
	 from PL_SUBJ
	 inner join PL_SUBJ_PARAM on PL_SUBJ_PARAM.PL_SUBJ_ID=PL_SUBJ.PL_SUBJ_ID
	 inner join PL_PARAM PL_PARAM ON PL_PARAM.PL_PARAM_ID=PL_SUBJ_PARAM.PL_PARAM_ID
	 inner JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID
	 where PL_SUBJ.PL_SUBJ_ID in(".$PL_SUBJ_ID.")
	 and(PL_SUBJ.ARCHIVE not in (1)) ";
	
	//print "library_path=".$config["library_path"]."<br>";
	//print "check_web_access=".$config["check_web_access"]."<br>";
	print read_config_option("check_web_access")."<br>";
	//print "version=".$config["version"]."<br>";
	//print read_config_option("version")."<br>";
	//print "log_verbosity=".$config["log_verbosity"];
	
	if($config["check_web_access"]>0)
	{	
		$tsql.=" and PL_SUBJ.web_access>0 ";
	}
	$tsql.=" order by NAME";

	$arr=db_fetch_row($tsql);
	if (empty($arr))
	{
		return;
	}
		print "<p><a href=\"planning.php\">".$arr['ORG_NAME']."</a>";
		print " >> <a href=\"planning.php?action=dep&id=".$arr['DEP_ID']."\">".$arr['DEP_NAME']."</a>";
		print " >> ".$arr['SUBJ_NAME']."</p>";
		$PL_AGEND_ID=$arr['PL_AGEND_ID1'];

	//-----------
	$date="";
	if (!empty($_GET["date"]))
	{
		$date=$_GET["date"];
	}
	
	if(!is_date($date))
	{
		print '<p>Не выбрана дата</p>';
		print "<p>сегодня ".date("Y.m.d")."</p>";
		$date=date("Ymd");
	}

	echo "<p>";
	print_calendar("planning.php?action=plsubj&id=".$PL_SUBJ_ID);
	echo "\n&nbsp</p>\n";
	
	$full_day=web_day_generate($PL_SUBJ_ID, $date);
	//print_r($full_day);
	echo "<p>Расписание на  ".date("Y.m.d",strtotime($date))."</p>";
	echo "<p><a href=\"planning.php?action=plsubj&id=$PL_SUBJ_ID&date=$date&xml=1\">xml</a></p>";
	
	web_day_show($full_day);
	
	pl_exam_show($PL_SUBJ_ID);
	
	print("<p>debug</p>");
	//-----------
	$PL_AGEND_ID=pl_agend_get_id_by_pl_subj_id($PL_SUBJ_ID, $date);
	
	pl_agend_show($PL_AGEND_ID, $date);
	pl_day_show_array(pl_day_get_array((pl_day_get_id_by_pl_agend_id($PL_AGEND_ID, $date))));
	
	pl_excl_show($PL_SUBJ_ID, $date);
	planning_show($PL_SUBJ_ID, $date);
	//-----------
	print("<p>debug2</p>");
	show_agend($PL_AGEND_ID);
	pl_excl_show($PL_SUBJ_ID);
		
}

function show_plsubj_xml()
{
	global $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */
	
	$PL_SUBJ_ID=0;

	if (!empty($_GET["id"]))
	{
			$PL_SUBJ_ID=$_GET["id"];
	}
	
	if (!$PL_SUBJ_ID>0)
	{
		//print '<p>Не выбрана сетка расписания:</p>';
		return;
	}
	
	//-----------
	//show planning name
	$tsql="select PL_SUBJ.PL_SUBJ_ID, PL_SUBJ.NAME SUBJ_NAME, PL_AGEND_ID1 
	 , PL_PARAM.PL_PARAM_ID DEP_ID, PL_PARAM.NOM DEP_NAME
	 , FM_ORG.LABEL ORG_NAME
	 from PL_SUBJ
	 inner join PL_SUBJ_PARAM on PL_SUBJ_PARAM.PL_SUBJ_ID=PL_SUBJ.PL_SUBJ_ID
	 inner join PL_PARAM PL_PARAM ON PL_PARAM.PL_PARAM_ID=PL_SUBJ_PARAM.PL_PARAM_ID
	 inner JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID
	 where PL_SUBJ.PL_SUBJ_ID in(".$PL_SUBJ_ID.")
	 and(PL_SUBJ.ARCHIVE not in (1)) ";
	
	//TODO check_web_access
	
	if($config["check_web_access"]>0)
	{	
		$tsql.=" and PL_SUBJ.web_access>0 ";
	}
	$tsql.=" order by NAME";

	$arr=db_fetch_row($tsql);
	if (empty($arr))
	{
		//empty array, no access
 		return;
	}

	//-----------
	$date="";
	if (!empty($_GET["date"]))
	{
		$date=$_GET["date"];
	}
	
	if(!is_date($date))
	{
		$date=date("Ymd");
	}

	$full_day=web_day_generate($PL_SUBJ_ID, $date);
	
	$in_charset="cp1251";
	$out_charset="UTF-8";
	
	$full_day=recursive_iconv($in_charset, $out_charset, $full_day);
	$xml=export_xml($full_day,"events","event",'1.0',$out_charset);	

	if($xml)
		echo $xml;
	else
		echo "xml failed\n";
		
}

function pl_day_get_message($row)
{
	/*"DAY_EVEN, DAY_MONTH, DAY_OF_MONTH, DAY_OF_WEEK, DAY_ORDER, DAY_WEEK, DAY_WEEK_MONTH, DAY_YEAR, PERIOD_FROM, PERIOD_TO"
	 * 
CREATE_DATE_TIME	Label=Дата создания записи  System=T  CanModify=F  IsVisible=T  
DAY_EVEN			Label=Признак четности  
DAY_MONTH			Label=Месяц    
DAY_OF_MONTH		Label=День месяца    
DAY_OF_WEEK			Label=Номер дня в неделе  
DAY_ORDER			Label=Порядок    
DAY_WEEK			Label=Номер недели в месяце      
DAY_WEEK_MONTH		Label=i-тый день недели в месяце  
DAY_YEAR			Label=Год    
DUREE_TRANCHE		Label=Длительность транша  
ENABLED				Label=Признак активности (да/нет)  
END_TIME			Label=Время окончания раб. дня  
INTERVAL_OFF		Label=Дней отдыхает  
INTERVAL_STARTFROM	Label=Отсчет с даты  
INTERVAL_WORK		Label=Дней работает  
KRN_CREATE_DATE		Label=Дата создания  
KRN_CREATE_USER_ID
KRN_MODIFY_DATE		Label=Дата изменения  
KRN_MODIFY_USER_ID		
MEDECINS_CREATE_ID	Label=Пользователь, создавший запись  
MEDECINS_MODIFY_ID	Label=Пользователь, последний изменивший запись  
MODIFY_DATE_TIME	Label=Дата последнего изменения записи  
NAME				Label=Наименование  
PERIOD_FROM			Label=День активен с  
PERIOD_TO			Label=День активен по  
PL_AGEND_ID			Label=Модель расписания  
PL_DAY_ID				   
PL_SUBJ_ID			Label=Расписание  
START_TIME			Label=Время начала раб. дня  
	*/
	
	$days=array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье");
	$months=array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
	$months_a=array("Январе", "Феврале", "Марте", "Апреле", "Мае", "Июне", "Июле", "Августе", "Сентябре", "Октябре", "Ноябре", "Декабре");

	$msg="";
	
	$DAY_MONTH=0;
	$DAY_OF_MONTH=0;
	$DAY_WEEK=0;
	$DAY_WEEK_MONTH=0;
	$DAY_OF_WEEK=0;
	$DAY_EVEN=0;
	$INTERVAL_OFF=0;		//Дней отдыхает  
	$INTERVAL_STARTFROM=0;	//Отсчет с даты  
	$INTERVAL_WORK=0;		//Дней работает
	
	//Месяц
	if (isset($row['DAY_MONTH']))	
		$DAY_MONTH=(int)$row['DAY_MONTH'];
	
	if(($DAY_MONTH>0)&&($DAY_MONTH<13))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="в ".$months_a[$DAY_MONTH-1];	//в массиве нумерация с 0
	}
		
	//День месяца
	if (isset($row['DAY_OF_MONTH']))	
		$DAY_OF_MONTH=(int)$row['DAY_OF_MONTH'];
	
	if(($DAY_OF_MONTH>0)&&($DAY_OF_MONTH<32))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_OF_MONTH." числа";
	}
	
	//Номер недели в месяце
	if (isset($row['DAY_WEEK']))	
		$DAY_WEEK=(int)$row['DAY_WEEK'];

	if(($DAY_WEEK>0)&&($DAY_WEEK<=6))	 /* 6 weeks sometimes :) */
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_WEEK." неделя";
	}
	
	//i-тый день недели в месяце
	if (isset($row['DAY_WEEK_MONTH']))	
		$DAY_WEEK_MONTH=(int)$row['DAY_WEEK_MONTH'];
		
	if(($DAY_WEEK_MONTH>0)&&($DAY_WEEK_MONTH<6))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_WEEK_MONTH."-ый";
	}
		
	//Номер дня в неделе
	if (isset($row['DAY_OF_WEEK']))	
		$DAY_OF_WEEK=(int)$row['DAY_OF_WEEK'];
	
	if(($DAY_OF_WEEK>0)&&($DAY_OF_WEEK<8))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$days[$DAY_OF_WEEK-1];	//в массиве нумерация с 0
	}
		
	//Признак четности
	if (isset($row['DAY_EVEN']))	
		$DAY_EVEN=(int)$row['DAY_EVEN'];
		
	if($DAY_EVEN==1)		
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="четные";
	}elseif ($DAY_EVEN==2)
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="нечетные";
	}
	
	//интервалы работы
	if (isset($row['INTERVAL_OFF']))	
		$INTERVAL_OFF=(int)$row['INTERVAL_OFF'];
		
	if (isset($row['INTERVAL_WORK']))
		$INTERVAL_WORK=(int)$row['INTERVAL_WORK'];
		
	if (isset($row['INTERVAL_STARTFROM']))	
		$INTERVAL_STARTFROM=(int)$row['INTERVAL_STARTFROM'];

	if(($INTERVAL_WORK>0)&&($INTERVAL_WORK<8)
	 &&(1))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$days[$DAY_OF_WEEK-1];	//в массиве нумерация с 0
	}	

	
	//Время начала раб. дня
	if(!is_null($row['START_TIME']))
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="c ".parse_time($row['START_TIME']);
	}
	//Время окончания раб. дня
	if(!is_null($row['END_TIME']))
	{
		if(strlen($msg)>0) $msg.=" ";
		$msg.="до ".parse_time($row['END_TIME']);
	}
	

	return $msg;
}

/**
 * debug function
 * @param int $PL_AGEND_ID
 * @param string $date
 */
function show_agend($PL_AGEND_ID, $date="")
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_AGEND_ID);
	if(strlen($date)>0) input_validate_input_date($date);	
	/* ==================================================== */

	$tsql="/* TfAgenda.dbrDays */ 
	 SELECT 
	 PL_DAY.PL_DAY_ID, PL_DAY.NAME
	 ,PL_DAY.START_TIME, PL_DAY.END_TIME 
	 ,DAY_EVEN, DAY_MONTH, DAY_OF_MONTH, DAY_OF_WEEK, DAY_ORDER, DAY_WEEK, DAY_WEEK_MONTH, DAY_YEAR
	 ,PERIOD_FROM, PERIOD_TO
	 ,DUREE_TRANCHE
	 ,ENABLED 	  
	 FROM pl_day 
	 WHERE 
	 /*PL_DAY.ENABLED=1 
	 and */pl_agend_id in (".$PL_AGEND_ID.") ";
	
	if(strlen($date)>0) $tsql.="
	 and (PERIOD_FROM <= '".$date."' and PERIOD_TO >= '".$date."')";
	
	$tsql.=" ORDER BY day_order";


	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_DAY_ID</td><td>Name</td>";
		print "<td>START_TIME</td><td>END_TIME</td>";
		print "<td>DAY_EVEN</td><td>DAY_MONTH</td><td>DAY_OF_MONTH</td><td>DAY_OF_WEEK</td>";
		print "<td>DAY_WEEK</td><td>DAY_WEEK_MONTH</td><td>DAY_YEAR</td>";
		print "<td>PERIOD_FROM</td><td>PERIOD_TO</td>";
		print "<td>DUREE_TRANCHE</td>";
		echo "<td>ENABLED</td>";		
		print "<td>message</td>";
		print "</tr> \n";
		

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_DAY_ID'];
			print "</td><td>";
			print "<a href=\"planning.php?action=plday&id=".$row['PL_DAY_ID']."\">";
			print $row['NAME'];
			print "</a></td>";
			print "<td>".$row['START_TIME']."</td><td>".$row['END_TIME']."</td>";
			print "<td>".$row['DAY_EVEN']."</td><td>".$row['DAY_MONTH']."</td><td>".$row['DAY_OF_MONTH']."</td><td>".$row['DAY_OF_WEEK']."</td>";
			print "<td>".$row['DAY_WEEK']."</td><td>".$row['DAY_WEEK_MONTH']."</td><td>".$row['DAY_YEAR']."</td>";
			print "<td>".$row['PERIOD_FROM']."</td><td>".$row['PERIOD_TO']."</td>";
			print "<td>".$row['DUREE_TRANCHE']."</td>";
			print "<td>".$row['ENABLED']."</td>";				
			$msg=pl_day_get_message($row);
			print "<td>".$msg."</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}else
	{
		print "<tr><td><em>Нет доступа к сеткам расписаниям</em></td></tr>";
	}
}

function pl_agend_show($PL_AGEND_ID, $date="")
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_AGEND_ID);
	input_validate_input_date($date);	
	/* ==================================================== */

	$arr = pl_agend_get_array($PL_AGEND_ID,$date);

	if (sizeof($arr) > 0)
	{
		pl_agend_show_array($arr);
	}
	else
	{
		print "<br>Нет доступа к сеткам расписаниям<br>";
	}
}


/**
 * return PL_AGEND_ID for selected PL_SUBJ_ID and date 
 * @param int $PL_SUBJ_ID
 * @param string $date
 * @return int PL_AGEND_ID
 */
function pl_agend_get_id_by_pl_subj_id($PL_SUBJ_ID, $date)
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	
	if(!$PL_SUBJ_ID>0) return 0;

	$tsql="select dbo.pl_GetSubjAgenda ('".$PL_SUBJ_ID."', '".$date."' ) PL_AGEND_ID";
	$arr = db_fetch_row($tsql);

	$PL_AGEND_ID=0;
	if (sizeof($arr) > 0)
	{
		$PL_AGEND_ID=$arr['PL_AGEND_ID'];
	}

	return $PL_AGEND_ID;

}

function pl_day_get_id_by_pl_agend_id($PL_AGEND_ID, $date)
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_AGEND_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	
	if(!$PL_AGEND_ID>0) return 0;

	$arr = pl_agend_get_array($PL_AGEND_ID, $date);

	$PL_DAY_ID=0;
	if (sizeof($arr) > 0)
	{
		$PL_DAY_ID=$arr['PL_DAY_ID'];
	}

	return $PL_DAY_ID;

}

function pl_agend_get_array($PL_AGEND_ID, $date="")
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_AGEND_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	if(!$PL_AGEND_ID>0) return null;

	$tsql="SET DATEFIRST 1
	 declare @date datetime
	 set @date='".$date."'

	 SELECT top 1
	 PL_DAY.PL_DAY_ID, PL_DAY.NAME
	 ,PL_DAY.START_TIME, PL_DAY.END_TIME 
	 ,DAY_EVEN, DAY_MONTH, DAY_OF_MONTH, DAY_OF_WEEK, DAY_ORDER, DAY_WEEK, DAY_WEEK_MONTH, DAY_YEAR
	 ,PERIOD_FROM, PERIOD_TO
	 ,DUREE_TRANCHE
	 ,ENABLED 
	 FROM pl_day 
	 WHERE 
	 /*PL_DAY.ENABLED=1 
	 and */
	 pl_agend_id in (".$PL_AGEND_ID.") 
	 and (PERIOD_FROM is null or PERIOD_FROM <= @date)						/* День активен с */
	 and (PERIOD_TO is null or PERIOD_TO >= @date)							/* День активен по */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))							/* Месяц */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))
	 and (isnull(DAY_OF_MONTH,0)=0 or DAY_OF_MONTH = day(@date))					/* День месяца */
	 and (isnull(DAY_YEAR,0)=0 or DAY_YEAR = (year(@date)-1995)/* magic year*/)		/* Год */
	 and (isnull(DAY_EVEN,0)=0 or DAY_EVEN=(day(@date)& 1 + 1))						/* Признак четности, нечетный 2, четный 1 */
	 and (isnull(DAY_WEEK,0)=0 or DAY_WEEK=(
		 DATEPART(week,@date)-DATEPART(week,DATEADD(day,1-day(@date),@date))+1))		/* Номер недели в месяце */
	 and (isnull(DAY_OF_WEEK,0)=0 
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and isnull(DAY_WEEK_MONTH,0)=0)	/* Номер дня в неделе */
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and DAY_WEEK_MONTH=DATEDIFF(day,DATEADD(day,1-day(@date),@date),@date)/7+1))
					/* i-тый день недели в месяце */
	 and (case when((INTERVAL_WORK is null or INTERVAL_WORK!>0) and (INTERVAL_OFF is null or INTERVAL_OFF!>0)) then 1 else (
			case when (DATEDIFF(day,INTERVAL_STARTFROM,@date))%(INTERVAL_WORK+INTERVAL_OFF)<=(INTERVAL_WORK-1)then 1 else 0 end
		 ) end)=1
	 ORDER BY day_order
	";

	$arr = db_fetch_row($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}	
}

function pl_agend_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		echo "<tr> \n";
		echo "<td>PL_DAY_ID</td><td>Name</td>";
		echo "<td>START_TIME</td><td>END_TIME</td>";
		echo "<td>DAY_EVEN</td><td>DAY_MONTH</td><td>DAY_OF_MONTH</td><td>DAY_OF_WEEK</td>";
		echo "<td>DAY_WEEK</td><td>DAY_WEEK_MONTH</td><td>DAY_YEAR</td>";
		echo "<td>PERIOD_FROM</td><td>PERIOD_TO</td>";
		echo "<td>DUREE_TRANCHE</td>";
		echo "<td>ENABLED</td>";
		echo "<td>message</td>";
		echo "</tr> \n";
		

		//foreach ($arr as $row)
		//{
		$row=$arr;
			$PL_DAY_ID=$row['PL_DAY_ID'];
			print "<tr> \n";
			print "<td>";
			print $row['PL_DAY_ID'];
			print "</td><td>";
			print "<a href=\"planning.php?action=plday&id=".$row['PL_DAY_ID']."\">";
			print $row['NAME'];
			print "</a></td>";
			print "<td>".$row['START_TIME']."</td><td>".$row['END_TIME']."</td>";
			print "<td>".$row['DAY_EVEN']."</td><td>".$row['DAY_MONTH']."</td><td>".$row['DAY_OF_MONTH']."</td><td>".$row['DAY_OF_WEEK']."</td>";
			print "<td>".$row['DAY_WEEK']."</td><td>".$row['DAY_WEEK_MONTH']."</td><td>".$row['DAY_YEAR']."</td>";
			print "<td>".$row['PERIOD_FROM']."</td><td>".$row['PERIOD_TO']."</td>";
			print "<td>".$row['DUREE_TRANCHE']."</td>";
			print "<td>".$row['ENABLED']."</td>";			
			$msg=pl_day_get_message($row);
			print "<td>".$msg."</td>";
			print "</tr> \n";
		//}

		print "	</tbody>
	</table>";

	}	
}


function pl_excl_show($PL_SUBJ_ID, $date="")
{

	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	if(strlen($date)>0) input_validate_input_date($date);
	/* ==================================================== */

	$arr = pl_excl_get_array($PL_SUBJ_ID, $date);

	if (sizeof($arr) > 0)
	{
		pl_excl_show_array($arr);
		
	}else
	{
		print "<tr><td><em>Нет доступа к исключительным событиям расписания</em></td></tr>";
	}
}

function pl_excl_get_array($PL_SUBJ_ID, $date="")
{

	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	if(strlen($date)>0) input_validate_input_date($date);
	/* ==================================================== */
	
	if(!$PL_SUBJ_ID>0) return null;

	$tsql="select 
	 PL_EXCL.PL_EXCL_ID
	 ,PL_LEG.NAME
	 ,PL_LEG.COLOR
	 ,FROM_DATE
	 ,FROM_TIME
	 ,TO_DATE
	 ,TO_TIME
	 from PL_EXCL
	 inner join PL_LEG on PL_LEG.PL_LEG_ID=PL_EXCL.PL_LEG_ID
	 where  PL_SUBJ_ID in (".$PL_SUBJ_ID.")";
	
	if(strlen($date)>0) $tsql.=" 
	 and (FROM_DATE <= '".$date."' and TO_DATE >= '".$date."')";
	
	$tsql.=" order by TO_DATE,FROM_DATE";

	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function pl_excl_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_EXCL_ID</td><td>Name</td>";
		print "<td>FROM_TIME</td><td>TO_TIME</td>";
		print "<td>FROM_DATE</td><td>TO_DATE</td>";
		print "<td>COLOR</td>";
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_EXCL_ID'];
			print "</td><td>";
			print $row['NAME'];
			print "</td>";
			print "<td>".parse_time($row['FROM_TIME'])."</td><td>".parse_time($row['TO_TIME'])."</td>";
			print "<td>".$row['FROM_DATE']."</td><td>".$row['TO_DATE']."</td>";
			print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}
}

function pl_day_show()
{
	print '<p>Список событий дня сетки расписания:</p>';

	if (empty($_GET["id"]))
	{
		print '<p>Не выбран день сетки расписания:</p>';
		return;
	}
	
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$PL_DAY_ID=$_GET["id"];
	//-----------
	//show day name
	$tsql="select PL_DAY_ID, PL_DAY.NAME DAY_NAME
	 ,PL_SUBJ.PL_SUBJ_ID, PL_SUBJ.NAME SUBJ_NAME
	 , PL_PARAM.PL_PARAM_ID DEP_ID, PL_PARAM.NOM DEP_NAME
	 , FM_ORG.LABEL ORG_NAME
	 from PL_DAY
	  inner join PL_SUBJ on PL_SUBJ.pl_agend_id1=PL_DAY.pl_agend_id
	  inner join PL_SUBJ_PARAM on PL_SUBJ_PARAM.PL_SUBJ_ID=PL_SUBJ.PL_SUBJ_ID
	  inner join PL_PARAM PL_PARAM ON PL_PARAM.PL_PARAM_ID=PL_SUBJ_PARAM.PL_PARAM_ID 
	  inner JOIN FM_ORG FM_ORG ON FM_ORG.FM_ORG_ID = PL_PARAM.FM_INTORG_ID
	 where PL_DAY.PL_DAY_ID in (".$PL_DAY_ID.")";

	$arr=db_fetch_row($tsql);
	if (sizeof($arr) > 0)
	{
		print "<p><a href=\"planning.php\">".$arr['ORG_NAME']."</a>";
		print " >> <a href=\"planning.php?action=dep&id=".$arr['DEP_ID']."\">".$arr['DEP_NAME']."</a>";
		print " >> <a href=\"planning.php?action=plsubj&id=".$arr['PL_SUBJ_ID']."\">".$arr['SUBJ_NAME']."</a>";
		print " >> ".$arr['DAY_NAME']."</p>";
	}

	//-----------	

	$arr = pl_day_get_array($PL_DAY_ID);

	if (sizeof($arr) > 0)
	{
		pl_day_show_array($arr);
	}
	else
	{
		print "<tr><td><em>Нет доступа к событиям дня сетки расписания</em></td></tr>";
	}
}

function pl_day_get_array($PL_DAY_ID)
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_DAY_ID);
	/* ==================================================== */
	if(!$PL_DAY_ID>0) return null;

	$tsql="SELECT 
	 PL_INT.PL_INT_ID, PL_INT.INT_FROM,PL_INT.INT_TO,PL_LEG.COLOR,PL_LEG.FONT
	 ,PL_LEG.NAME 
	 FROM PL_INT PL_INT 
	 JOIN PL_LEG PL_LEG ON PL_LEG.PL_LEG_ID = PL_INT.PL_LEG_ID 
	 WHERE 
	 PL_INT.PL_DAY_ID in (".$PL_DAY_ID.") 
	 order by PL_INT.INT_FROM
	";

	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function pl_day_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_INT_ID</td><td>Name</td>";
		print "<td>INT_FROM</td><td>END_TIME</td>";
		print "<td>COLOR</td>";
		//print "<td>COLOR2</td>";
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_INT_ID'];
			print "</td><td>";
			//print "<a href=\"planning.php?action=plint&id=".$row['PL_INT_ID']."\">";
			print $row['NAME'];
			//print "</a>";
			print "</td>";
			print "<td>".$row['INT_FROM']."</td><td>".$row['INT_TO']."</td>";
			//print "<td>".$row['COLOR']."</td>";
			print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";
	}
}

/**
 * список записей для определенного расписания на дату, если нет даты, то на сегодня
 * @param PL_SUBJ_ID int
 * @param date
 */
function planning_show($PL_SUBJ_ID, $date="")
{
	//If no date is specified, the current date is assumed
	if(!strlen($date)>0) $date=date("Ymd");
	
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	
	$arr = planning_get_array($PL_SUBJ_ID,$date);

	if (sizeof($arr) > 0)
	{
		planning_show_array($arr);
	}
	else
	{
		print "<tr><td><em>Нет доступа к записям расписания</em></td></tr>";
	}
}

function planning_get_array($PL_SUBJ_ID, $date="")
{
	//If no date is specified, the current date is assumed
	if(!strlen($date)>0) $date=date("Ymd");
	
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	
	$tsql="SELECT
	 PLANNING.PLANNING_ID,PLANNING.PL_SUBJ_ID,PLANNING.DATE_CONS,PLANNING.HEURE,PLANNING.DUREE,PLANNING.PATIENTS_ID
	 ,PLANNING.COLOR,PLANNING.FONT,PLANNING.DUREE_TEXT,PLANNING.PATIENT_ARRIVEE,PLANNING.PL_GENER_ID
	 ,PLANNING.PL_EXAM_ID,PL_EXAM.NAME NAME
	 ,PLANNING.NOM,PLANNING.PRENOM,PLANNING.PATRONYME,PLANNING.MOTIF,PLANNING.COMMENTAIRE
	 FROM
	 PLANNING PLANNING
	 LEFT OUTER JOIN PL_EXAM PL_EXAM ON PL_EXAM.PL_EXAM_ID = PLANNING.PL_EXAM_ID
	 WHERE
	 PLANNING.PL_SUBJ_ID in(".$PL_SUBJ_ID.")
	 /*AND (PLANNING.DATE_CONS>=DATEADD(day, DATEDIFF(day, 0, getdate()), 0) AND PLANNING.DATE_CONS<=getdate())*/
	 AND (PLANNING.DATE_CONS>='".$date."' AND PLANNING.DATE_CONS<='".$date."')
	 AND ((PLANNING.CANCELLED IS NULL) OR (PLANNING.CANCELLED=0))
	 AND ((PLANNING.STATUS IS NULL) OR (PLANNING.STATUS = 0))
	 ORDER BY PLANNING.HEURE";


	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function planning_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PLANNING_ID</td><td>Name</td>";
		print "<td>PL_SUBJ_ID</td><td>DATE_CONS</td>";
		print "<td>HEURE</td><td>DUREE</td><td>PATIENTS_ID</td>";
		//print "<td>FONT</td><td>DAY_WEEK_MONTH</td><td>DAY_YEAR</td>";
		//print "<td>PERIOD_FROM</td><td>PERIOD_TO</td>";
		print "<td>message</td>";
		print "<td>COLOR</td>";		
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PLANNING_ID'];
			print "</td><td>";
			//print "<a href=\"planning.php?action=plday&id=".$row['PL_DAY_ID']."\">";
			print $row['NAME'];
			//print "</a></td>";
			print "<td>".$row['PL_SUBJ_ID']."</td>";
			echo "<td>".date("Y-m-d", strtotime($row['DATE_CONS']))."</td>";
			//echo "<td>".$row['DATE_CONS']."</td>";
			echo "<td>".parse_time($row['HEURE'])." [".parse_time2($row['HEURE'])."</td>";
			echo "<td>".$row['DUREE']." [".add_time($row['HEURE'],$row['DUREE'])."</td>";
			print "<td>".$row['PATIENTS_ID']."</td>";
			print "<td>".$row['NOM']." ".$row['PRENOM']." ".$row['PATRONYME']." ".$row['COMMENTAIRE']."</td>";
			//print "<td>".$row['DAY_WEEK']."</td><td>".$row['DAY_WEEK_MONTH']."</td><td>".$row['DAY_YEAR']."</td>";
			//print "<td>".$row['PERIOD_FROM']."</td><td>".$row['PERIOD_TO']."</td>";
			print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}
}

function pl_exam_show($PL_SUBJ_ID)
{

	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	/* ==================================================== */

	$arr = pl_exam_get_array($PL_SUBJ_ID);

	if (sizeof($arr) > 0)
	{
		print "<tr><td><em>Виды приема расписания</em></td></tr>";
		pl_exam_show_array($arr);
		
	}else
	{
		print "<tr><td><em>Нет доступа к видам приема расписания</em></td></tr>";
	}
}

function pl_exam_get_array($PL_SUBJ_ID)
{
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	/* ==================================================== */
	
	if(!$PL_SUBJ_ID>0) return null;

	$tsql="SELECT
	 PL_EXAM.PL_EXAM_ID
	 ,PL_EXAM.NAME
	 ,PL_EXAM.DUREE
	 ,PL_EXAM.COLOR
	 FROM
	  PL_EXAM
	  LEFT OUTER JOIN PL_SUBJ_EXAM ON PL_SUBJ_EXAM.PL_EXAM_ID = PL_EXAM.PL_EXAM_ID
	 WHERE
	  PL_SUBJ_ID in (".$PL_SUBJ_ID.")
	 order by PL_SUBJ_EXAM.EXAM_ORDER";

	$arr = db_fetch_assoc($tsql);

	if (sizeof($arr) > 0)
	{
		return $arr;
	}
	else
	{
		return null;
	}
}

function pl_exam_show_array($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>PL_EXAM_ID</td><td>NAME</td>";
		print "<td>DUREE</td>";
		print "<td>COLOR</td>";
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>";
			print $row['PL_EXAM_ID'];
			print "</td><td>";
			print $row['NAME'];
			print "</td>";
			print "<td>".$row['DUREE']."</td>";
			print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}
}

function web_day_generate($PL_SUBJ_ID, $date)
{
	//If no date is specified, the current date is assumed
	if(!strlen($date)>0) $date=date("Ymd");
	
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	if(!$PL_SUBJ_ID>0) return null;
	
	//массив, который будет содержать все события
	//начало и конец дня
	//плановые события
	//исключительные события
	//записи в расписание
	$full_day=array();
	//массив для одного события
	$day_row=array('START_TIME'=>'', 
					'END_TIME'=>'', 
					'DUREE'=>'', 
					'TYPE'=>'', 
					'NAME'=>'', 
					'COLOR'=>'',
					'WEB_ACCESS'=>'');
	
	$PL_AGEND_ID=0;
	$PL_DAY_ID=0;
	
	$day_row['START_TIME']=''; 
	$day_row['END_TIME']=''; 
	$day_row['DUREE']='';
	$day_row['TYPE']=''; 
	$day_row['NAME']='';
	$day_row['COLOR']='';
	$day_row['WEB_ACCESS']='';
	
	$PL_AGEND_ID=pl_agend_get_id_by_pl_subj_id($PL_SUBJ_ID, $date);
	
	//получаем день PL_DAY
	$row = pl_agend_get_array($PL_AGEND_ID,$date);

	if (sizeof($row) > 0)
	{
		//debug
		//pl_agend_show_array($row);
		if($row['ENABLED']==0)
		{
			$full_day[]['NAME']="Нет приема";
			return $full_day;
		}
		
		$PL_DAY_ID=$row['PL_DAY_ID'];
		
		$key =parse_time($row['START_TIME']);
		$full_day[$key]['START_TIME']=parse_time($row['START_TIME']);
		$full_day[$key]['END_TIME']=parse_time($row['START_TIME']);
		$full_day[$key]['DUREE']='';
		$full_day[$key]['TYPE']=''; 		
		$full_day[$key]['NAME']=pl_day_get_message($row);
		$full_day[$key]['COLOR']='';
		$full_day[$key]['WEB_ACCESS']='';
		
		$key =parse_time($row['END_TIME']);
		$full_day[$key]['START_TIME']=parse_time($row['END_TIME']);
		$full_day[$key]['END_TIME']=parse_time($row['END_TIME']);
		$full_day[$key]['DUREE']='';
		$full_day[$key]['TYPE']='';
		$full_day[$key]['NAME']="Конец рабочего дня";
		$full_day[$key]['COLOR']='';
		$full_day[$key]['WEB_ACCESS']='';		
	}
	else
	{
		$full_day[]['NAME']="Нет приема";
		//print "<br>Нет доступа к сетке расписаниям<br>";
		//выходим сразу или получаем остальные события дня?
		return $full_day;
	}
	
	//------------------
	//получаем плановые события дня
	$arr=null;
	if($PL_DAY_ID>0)
		$arr = pl_day_get_array($PL_DAY_ID);

	if (sizeof($arr) > 0)
	{
		//debug
		//pl_day_show_array($arr);
		
		foreach ($arr as $row)
		{
			//заполняем
			$key =parse_time($row['INT_FROM']);		
			$full_day[$key]['START_TIME']=parse_time($row['INT_FROM']);
			$full_day[$key]['END_TIME']=parse_time($row['INT_TO']);
			$full_day[$key]['DUREE']='';
			$full_day[$key]['TYPE']=''; 
			$full_day[$key]['NAME']=$row['NAME'];
			$full_day[$key]['COLOR']=delphi_color_to_html($row['COLOR']);
			$full_day[$key]['WEB_ACCESS']='';
		}
	}

	//------------------
	//получаем исключительные события дня
	$arr=null;
	$arr = pl_excl_get_array($PL_SUBJ_ID, $date);

	if (sizeof($arr) > 0)
	{
		//debug
		//pl_excl_show_array($arr);
		
		foreach ($arr as $row)
		{
			//заполняем
			$key =parse_time($row['FROM_TIME']);
			$full_day[$key]['START_TIME']=parse_time($row['FROM_TIME']); 
			$full_day[$key]['END_TIME']=parse_time($row['TO_TIME']); 
			$full_day[$key]['DUREE']='';
			$full_day[$key]['TYPE']=''; 
			$full_day[$key]['NAME']=$row['NAME'];
			$full_day[$key]['COLOR']=delphi_color_to_html($row['COLOR']);
			$full_day[$key]['WEB_ACCESS']='';
		}
	}
	//------------------
	//получаем временное деление сетки DUREE_TRANCHE
	//TODO get DUREE_TRANCHE for day
	
	//------------------
	//получаем записи в расписание за день
	$arr=null;
	$arr = planning_get_array($PL_SUBJ_ID,$date);

	if (sizeof($arr) > 0)
	{
		//debug
		//planning_show_array($arr);
		
		foreach ($arr as $row)
		{
			$key =parse_time($row['HEURE']);
			$full_day[$key]['START_TIME']=parse_time($row['HEURE']); 
			$full_day[$key]['END_TIME']=add_time($row['HEURE'],$row['DUREE']); 
			$full_day[$key]['DUREE']=$row['DUREE'];
			$full_day[$key]['TYPE']=''; 
			$full_day[$key]['NAME']=$row['NAME']." ".$row['NOM']." ".$row['PRENOM']." ".$row['PATRONYME']." ".$row['COMMENTAIRE'];
			$full_day[$key]['COLOR']=delphi_color_to_html($row['COLOR']);
			$full_day[$key]['WEB_ACCESS']='';
		}
			
	}	
	ksort($full_day);
	return $full_day;
}

function web_day_show($arr)
{
	if (sizeof($arr) > 0)
	{

		print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
			
		print "<tr> \n";
		print "<td>START_TIME</td>"; 
		print "<td>END_TIME</td>";
		print "<td>DUREE</td>"; 
		print "<td>TYPE</td>";
		print "<td>NAME</td>"; 
		print "<td>COLOR</td>";
		print "<td>WEB_ACCESS</td>";
		print "</tr> \n";

		foreach ($arr as $row)
		{
			print "<tr> \n";
			print "<td>".(empty($row['START_TIME'])?"&nbsp":$row['START_TIME'])."</td>";
			print "<td>".(empty($row['END_TIME'])?"&nbsp":$row['END_TIME'])."</td>";
			print "<td>".(empty($row['DUREE'])?"&nbsp":$row['DUREE'])."</td>";
			print "<td>".(empty($row['TYPE'])?"&nbsp":$row['TYPE'])."</td>";
			print "<td>".(empty($row['NAME'])?"&nbsp":$row['NAME'])."</td>";
			if(empty($row['COLOR']))
				print "<td>&nbsp</td>";
			else
				print "<td bgcolor=\"#".$row['COLOR']."\">&nbsp</td>";
			print "<td>".(empty($row['WEB_ACCESS'])?"&nbsp":$row['WEB_ACCESS'])."</td>";
			//print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}
		print "	</tbody>
	</table>";
	}	
}

function print_calendar($pagename)
{
	//$month_names = array(1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december');
	$month_names = array(1=>"Январь", 2=>"Февраль", 3=>"Март", 4=>"Апрель", 5=>"Май", 6=>"Июнь", 7=>"Июль", 8=>"Август", 9=>"Сентябрь", 10=>"Октябрь", 11=>"Ноябрь", 12=>"Декабрь");
	$day_names = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС');
	$allow_past = true;
	$date_format = 'Ymd';
	
	$selected=get_request_var('date');
	
	if(!is_date($selected))
		$selected=date($date_format);
		
	$month= date('n',strtotime($selected));
	$year = date('Y',strtotime($selected));
	
	$pd = mktime (0,0,0, $month,1, $year);						// timestamp of the first day
	$zd = -(date('w', $pd)? (date('w', $pd)-1) : 6)+1;			// monday before
	$kd = date('t', $pd);										// last day of month
	$curr_last_day=mktime(0,0,0,$month,$kd,$year);				// timestamp of the last day of current month
	$sd = 7-date('w', $curr_last_day);							// sunday after
	
	$prev_month_day=strtotime($selected." -1 month");			// timestamp of the day of previous month
	$next_month_day=strtotime($selected." +1 month");			// timestamp of the day of next month
	
	//echo "\n monday before=$zd, last day of month=$kd, sunday after=$sd \n";
	
	echo '
    <div class="month_title">
      <a href="'.$pagename.'&date='.date($date_format,$prev_month_day).'" class="month_move">&laquo;</a>
      <div class="month_name">'.$month_names[date('n', mktime(0,0,0,$month,1,$year))].' '.date('Y', mktime(0,0,0,$month,1,$year)).'</div>
      <a href="'.$pagename.'&date='.date($date_format,$next_month_day).'" class="month_move">&raquo;</a>
      <div class="r"></div>
    </div>';
	
	for ($d=0;$d<7;$d++) {
		echo "\n    <div class=\"week_day\">".$day_names[$d]."</div>";
	}
	
	echo "\n    <div class=\"r\"></div>";
	for ($d=$zd;$d<=($kd+$sd);$d++) {
		$i = mktime (0,0,0,$month,$d,$year);
		
		if (($i >= $pd)&&($i <= $curr_last_day)) 
		{
			$day_style = '';
			if(date('Ymd') == date('Ymd', $i))
				$day_style = '_today';
			else if(strtotime($selected) == $i)
			 	$day_style = '_selected';
		} else 
		{
			//echo "\n	<div class=\"no_day\">&nbsp;</div>";
			$day_style = '_other';
		}
		$minulost = (date('Ymd') >= date('Ymd', $i+86400)) && !$allow_past;	//wtf in last period?
		$day_text='';
		if($minulost)
			$day_text=date('j', $i);
		else
			$day_text='<a title="'.date('Ymd', $i).'" href="'.$pagename."&date=".date($date_format, $i).'">'.date('j', $i).'</a>';
			
		echo "\n	<div class=\"day".$day_style.'">'.$day_text.'</div>';

		if (date('w', $i) == 0 && $i >= $pd) 
		{
			echo "\n	<div class=\"r\"></div>";
		}
	}
}


/**
 * Print array in XML format
 * array must be 2-dimensional
 * @param array $arr
 */
function export_xml($arr, $startElement = 'elements',$ElementName = 'element', $xml_version = '1.0', $xml_encoding = 'UTF-8')
{
    if(!is_array($arr)){
        $err = 'Invalid variable type supplied, expected array not found on line '.__LINE__." in Class: ".__CLASS__." Method: ".__METHOD__;
        trigger_error($err);
        if($this->_debug) echo $err;
        return false; //return false error occurred
    }
	$xml = new XMLWriter();
	//$xml->openURI("php://output");
    $xml->openMemory();
	$xml->startDocument($xml_version, $xml_encoding);
	$xml->setIndent(true);

	$xml->startElement($startElement);

	foreach ($arr as $row)
	{
		$xml->startElement($ElementName);
		foreach($row as $key => $value)
		{
			//echo "<$key>$value</$key>";
			$xml->writeAttribute($key, $value."");
			//$xml->writeElement($key, $value."");
		}
		$xml->writeRaw("");
		$xml->endElement();
	}

    $xml->endElement();//write end element
    
    //header('Content-type: text/xml');
	//$xml->flush();
    
	//returns the XML results
    return $xml->outputMemory(true);
}

/**
 * Build A XML Data Set
 *
 * @param array $data Associative Array containing values to be parsed into an XML Data Set(s)
 * @param string $startElement Root Opening Tag, default fx_request
 * @param string $xml_version XML Version, default 1.0
 * @param string $xml_encoding XML Encoding, default UTF-8
 * @return string XML String containig values
 * @return mixed Boolean false on failure, string XML result on success
 */
function arrayToXML($data, $startElement = 'fx_request', $xml_version = '1.0', $xml_encoding = 'UTF-8'){
    if(!is_array($data)){
        $err = 'Invalid variable type supplied, expected array not found on line '.__LINE__." in Class: ".__CLASS__." Method: ".__METHOD__;
        trigger_error($err);
        if($this->_debug) echo $err;
        return false; //return false error occurred
    }
    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument($xml_version, $xml_encoding);
    $xml->startElement($startElement);

    /**
     * Write XML as per Associative Array
     * @param object $xml XMLWriter Object
     * @param array $data Associative Data Array
     */
    function write(XMLWriter $xml, $data){
        foreach($data as $key => $value){
            if (is_array($value) && isset($value[0])){
                foreach($value as $itemValue){
                    //$xml->writeElement($key, $itemValue);

                    if(is_array($itemValue)){
                        $xml->startElement($key);
                        write($xml, $itemValue);
                        $xml->endElement();
                        continue;
                    } 

                    if (!is_array($itemValue)){
                        $xml->writeElement($key, $itemValue."");
                    }
                }
            }else if(is_array($value)){
            	//echo "key=$key";
                $xml->startElement($key);
                write($xml, $value);
                $xml->endElement();
                continue;
            } 

            if (!is_array($value)){
                $xml->writeElement($key, $value."");
            }
        }
    }
    write($xml, $data);

    $xml->endElement();//write end element
    //returns the XML results
    return $xml->outputMemory(true);
}




?>
