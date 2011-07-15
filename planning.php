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

switch ($_REQUEST["action"]) {
	case 'save':

		break;
	case 'actions':

		break;
	case 'dep':
		include_once("./include/header.php");

		show_plan();

		include_once("./include/footer.php");
		break;
	case 'plsubj':
		include_once("./include/header.php");

		show_plsubj();

		include_once("./include/footer.php");
		break;
	case 'plday':
		include_once("./include/header.php");

		pl_day_show();

		include_once("./include/footer.php");
		break;
	default:
		include_once("./include/header.php");

		show_deps();

		include_once("./include/footer.php");
		break;
}


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

	print '<p>������ ���������:</p>';

	$arr = db_fetch_assoc($tsql);

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


	}else
	{
		print "<tr><td><em>��� ������� � ����������</em></td></tr>";
	}


}


function show_plan()
{
	global $WEB_MEDECINS_ID;
	print '<p>������ ����������:</p>';
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (empty($_GET["id"]))
	{
		print '<p>�� ������� ���������:</p>';
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
		print "<tr><td><em>��� ������� � �����������</em></td></tr>";
		//exit
		return;		
	}

	//-----------
	//������ ������ ���������� ��� ���������� ���������

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

	}else
	{
		print "<tr><td><em>��� ������� � �����������</em></td></tr>";
	}
}

function show_plsubj()
{
	global $config;
	print '<p>������ ���� ��� ����� ����������:</p>';
	/* ================= input validation ================= */
	//input_validate_input_number(get_request_var("id"));
	/* ==================================================== */
	
	$PL_SUBJ_ID=0;

	if (!empty($_GET["id"]))
	{
			$PL_SUBJ_ID=$_GET["id"];
	}
	
	if (!$PL_SUBJ_ID>0)
	{
		print '<p>�� ������� ����� ����������:</p>';
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
	//print read_config_option("check_web_access")."<br>";
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
		print '<p>�� ������� ����</p>';
		print "<p>������� ".date("Y.m.d")."</p>";
		$date=date("Ymd");
	}

	print_calendar();
	$full_day=web_day_generate($PL_SUBJ_ID, $date);
	//print_r($full_day);
	
	web_day_show($full_day);
	
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

function pl_day_get_message($row)
{
	/*"DAY_EVEN, DAY_MONTH, DAY_OF_MONTH, DAY_OF_WEEK, DAY_ORDER, DAY_WEEK, DAY_WEEK_MONTH, DAY_YEAR, PERIOD_FROM, PERIOD_TO"
	 * 
CREATE_DATE_TIME	Label=���� �������� ������  System=T  CanModify=F  IsVisible=T  
DAY_EVEN			Label=������� ��������  
DAY_MONTH			Label=�����    
DAY_OF_MONTH		Label=���� ������    
DAY_OF_WEEK			Label=����� ��� � ������  
DAY_ORDER			Label=�������    
DAY_WEEK			Label=����� ������ � ������      
DAY_WEEK_MONTH		Label=i-��� ���� ������ � ������  
DAY_YEAR			Label=���    
DUREE_TRANCHE		Label=������������ ������  
ENABLED				Label=������� ���������� (��/���)  
END_TIME			Label=����� ��������� ���. ���  
INTERVAL_OFF		Label=���� ��������  
INTERVAL_STARTFROM	Label=������ � ����  
INTERVAL_WORK		Label=���� ��������  
KRN_CREATE_DATE		Label=���� ��������  
KRN_CREATE_USER_ID
KRN_MODIFY_DATE		Label=���� ���������  
KRN_MODIFY_USER_ID		
MEDECINS_CREATE_ID	Label=������������, ��������� ������  
MEDECINS_MODIFY_ID	Label=������������, ��������� ���������� ������  
MODIFY_DATE_TIME	Label=���� ���������� ��������� ������  
NAME				Label=������������  
PERIOD_FROM			Label=���� ������� �  
PERIOD_TO			Label=���� ������� ��  
PL_AGEND_ID			Label=������ ����������  
PL_DAY_ID				   
PL_SUBJ_ID			Label=����������  
START_TIME			Label=����� ������ ���. ���  
	*/
	
	$days=array("�����������", "�������", "�����", "�������", "�������", "�������", "�����������");
	$months=array("������", "�������", "����", "������", "���", "����", "����", "������", "��������", "�������", "������", "�������");
	$months_a=array("������", "�������", "�����", "������", "���", "����", "����", "�������", "��������", "�������", "������", "�������");

	$msg="";
	
	$DAY_MONTH=0;
	$DAY_OF_MONTH=0;
	$DAY_WEEK=0;
	$DAY_WEEK_MONTH=0;
	$DAY_OF_WEEK=0;
	$DAY_EVEN=0;
	$INTERVAL_OFF=0;		//���� ��������  
	$INTERVAL_STARTFROM=0;	//������ � ����  
	$INTERVAL_WORK=0;		//���� ��������
	
	//�����
	if (isset($row['DAY_MONTH']))	
		$DAY_MONTH=(int)$row['DAY_MONTH'];
	
	if(($DAY_MONTH>0)&&($DAY_MONTH<13))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="� ".$months_a[$DAY_MONTH-1];	//� ������� ��������� � 0
	}
		
	//���� ������
	if (isset($row['DAY_OF_MONTH']))	
		$DAY_OF_MONTH=(int)$row['DAY_OF_MONTH'];
	
	if(($DAY_OF_MONTH>0)&&($DAY_OF_MONTH<32))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_OF_MONTH." �����";
	}
	
	//����� ������ � ������
	if (isset($row['DAY_WEEK']))	
		$DAY_WEEK=(int)$row['DAY_WEEK'];

	if(($DAY_WEEK>0)&&($DAY_WEEK<=6))	 /* 6 weeks sometimes :) */
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_WEEK." ������";
	}
	
	//i-��� ���� ������ � ������
	if (isset($row['DAY_WEEK_MONTH']))	
		$DAY_WEEK_MONTH=(int)$row['DAY_WEEK_MONTH'];
		
	if(($DAY_WEEK_MONTH>0)&&($DAY_WEEK_MONTH<6))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$DAY_WEEK_MONTH."-��";
	}
		
	//����� ��� � ������
	if (isset($row['DAY_OF_WEEK']))	
		$DAY_OF_WEEK=(int)$row['DAY_OF_WEEK'];
	
	if(($DAY_OF_WEEK>0)&&($DAY_OF_WEEK<8))	 
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.=$days[$DAY_OF_WEEK-1];	//� ������� ��������� � 0
	}
		
	//������� ��������
	if (isset($row['DAY_EVEN']))	
		$DAY_EVEN=(int)$row['DAY_EVEN'];
		
	if($DAY_EVEN==1)		
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="������";
	}elseif ($DAY_EVEN==2)
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="��������";
	}
	
	//��������� ������
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
		$msg.=$days[$DAY_OF_WEEK-1];	//� ������� ��������� � 0
	}	

	
	//����� ������ ���. ���
	if(!is_null($row['START_TIME']))
	{
		if(strlen($msg)>0) $msg.=", ";
		$msg.="c ".parse_time($row['START_TIME']);
	}
	//����� ��������� ���. ���
	if(!is_null($row['END_TIME']))
	{
		if(strlen($msg)>0) $msg.=" ";
		$msg.="�� ".parse_time($row['END_TIME']);
	}
	

	return $msg;
}

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
	 FROM pl_day 
	 WHERE 
	 PL_DAY.ENABLED=1 
	 and pl_agend_id in (".$PL_AGEND_ID.") ";
	
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
			$msg=pl_day_get_message($row);
			print "<td>".$msg."</td>";
			print "</tr> \n";
		}

		print "	</tbody>
	</table>";

	}else
	{
		print "<tr><td><em>��� ������� � ������ �����������</em></td></tr>";
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
		print "<br>��� ������� � ������ �����������<br>";
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
	 and (PERIOD_FROM is null or PERIOD_FROM <= @date)						/* ���� ������� � */
	 and (PERIOD_TO is null or PERIOD_TO >= @date)							/* ���� ������� �� */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))							/* ����� */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))
	 and (isnull(DAY_OF_MONTH,0)=0 or DAY_OF_MONTH = day(@date))					/* ���� ������ */
	 and (isnull(DAY_YEAR,0)=0 or DAY_YEAR = (year(@date)-1995)/* magic year*/)		/* ��� */
	 and (isnull(DAY_EVEN,0)=0 or DAY_EVEN=(day(@date)& 1 + 1))						/* ������� ��������, �������� 2, ������ 1 */
	 and (isnull(DAY_WEEK,0)=0 or DAY_WEEK=(
		 DATEPART(week,@date)-DATEPART(week,DATEADD(day,1-day(@date),@date))+1))		/* ����� ������ � ������ */
	 and (isnull(DAY_OF_WEEK,0)=0 
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and isnull(DAY_WEEK_MONTH,0)=0)	/* ����� ��� � ������ */
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and DAY_WEEK_MONTH=DATEDIFF(day,DATEADD(day,1-day(@date),@date),@date)/7+1))
					/* i-��� ���� ������ � ������ */
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
			
		print "<tr> \n";
		print "<td>PL_DAY_ID</td><td>Name</td>";
		print "<td>START_TIME</td><td>END_TIME</td>";
		print "<td>DAY_EVEN</td><td>DAY_MONTH</td><td>DAY_OF_MONTH</td><td>DAY_OF_WEEK</td>";
		print "<td>DAY_WEEK</td><td>DAY_WEEK_MONTH</td><td>DAY_YEAR</td>";
		print "<td>PERIOD_FROM</td><td>PERIOD_TO</td>";
		print "<td>message</td>";
		print "</tr> \n";
		

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
		print "<tr><td><em>��� ������� � �������������� �������� ����������</em></td></tr>";
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
	print '<p>������ ������� ��� ����� ����������:</p>';

	if (empty($_GET["id"]))
	{
		print '<p>�� ������ ���� ����� ����������:</p>';
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
		print "<tr><td><em>��� ������� � �������� ��� ����� ����������</em></td></tr>";
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
 * ������ ������� ��� ������������� ���������� �� ����, ���� ��� ����, �� �� �������
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
		print "<tr><td><em>��� ������� � ������� ����������</em></td></tr>";
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
			print "<td>".$row['PL_SUBJ_ID']."</td><td>".$row['DATE_CONS']."</td>";
			print "<td>".parse_time($row['HEURE'])."</td><td>".$row['DUREE']."</td>";
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

function web_day_generate($PL_SUBJ_ID, $date)
{
	//If no date is specified, the current date is assumed
	if(!strlen($date)>0) $date=date("Ymd");
	
	/* ================= input validation ================= */
	input_validate_input_number($PL_SUBJ_ID);
	input_validate_input_date($date);	
	/* ==================================================== */
	if(!$PL_SUBJ_ID>0) return null;
	
	//������, ������� ����� ��������� ��� �������
	//������ � ����� ���
	//�������� �������
	//�������������� �������
	//������ � ����������
	$full_day=array();
	//������ ��� ������ �������
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
	
	//�������� ���� PL_DAY
	$row = pl_agend_get_array($PL_AGEND_ID,$date);

	if (sizeof($row) > 0)
	{
		//debug
		//pl_agend_show_array($row);
		if($row['ENABLED']==0)
		{
			$full_day[]['NAME']="��� ������";
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
		$full_day[$key]['NAME']="����� �������� ���";
		$full_day[$key]['COLOR']='';
		$full_day[$key]['WEB_ACCESS']='';		
	}
	else
	{
		print "<br>��� ������� � ����� �����������<br>";
		//������� ����� ��� �������� ��������� ������� ���?
		//return;
	}
	
	//------------------
	//�������� �������� ������� ���
	$arr=null;
	if($PL_DAY_ID>0)
		$arr = pl_day_get_array($PL_DAY_ID);

	if (sizeof($arr) > 0)
	{
		//debug
		//pl_day_show_array($arr);
		
		foreach ($arr as $row)
		{
			//���������
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
	//�������� �������������� ������� ���
	$arr=null;
	$arr = pl_excl_get_array($PL_SUBJ_ID, $date);

	if (sizeof($arr) > 0)
	{
		//debug
		//pl_excl_show_array($arr);
		
		foreach ($arr as $row)
		{
			//���������
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
	//�������� ������ � ���������� �� ����
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
			$full_day[$key]['END_TIME']=''; 
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
			print "<td bgcolor=\"#".(empty($row['COLOR'])?"000000":$row['COLOR'])."\">&nbsp</td>";
			print "<td>".(empty($row['WEB_ACCESS'])?"&nbsp":$row['WEB_ACCESS'])."</td>";
			//print "<td bgcolor=\"#".delphi_color_to_html($row['COLOR'])."\">&nbsp</td>";
			print "</tr> \n";
		}
		print "	</tbody>
	</table>";
	}	
}

function print_calendar()
{
	//$month_names = array(1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december');
	$month_names = array(1=>"������", 2=>"�������", 3=>"����", 4=>"������", 5=>"���", 6=>"����", 7=>"����", 8=>"������", 9=>"��������", 10=>"�������", 11=>"������", 12=>"�������");
	$day_names = array('��', '��', '��', '��', '��', '��', '��');
	$allow_past = true;
	$date_format = 'Ymd';
	$pagename=basename($_SERVER["REQUEST_URI"]);
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
    <div class="day'.$today.'">'.($minulost? date('j', $i) : '<a title="'.date('Ymd', $i).'" href="'.$pagename."&date=".date($date_format, $i).'">'.date('j', $i).'</a>').'</div>';
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

?>
