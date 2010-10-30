<?php
/*
 * Function for snmp details
 * 
 */
$snmp_tables_type=array('SNMP_BLADES','SNMP_FIREWALLS','SNMP_LOADBALANCERS',
					    'SNMP_PRINTERS','SNMP_SWITCHS'); 

$snmp_tables=array('SNMP_CARDS','SNMP_CARTRIDGES','SNMP_DRIVES',
				'SNMP_FANS','SNMP_NETWORKS','SNMP_POWERSUPPLIES',
				'SNMP_STORAGES','SNMP_TRAYS');


//is ID exist?
function info_snmp($snmp_id){
	global $l,$snmp_tables_type;
	if ($snmp_id == "" or !is_numeric($snmp_id)){
		return $l->g(837);	
	}
	
	//$arg=array();
	$sql="select * from snmp where id=%s";
	$arg=$snmp_id;
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$array['snmp'] = mysql_fetch_object($result);
	if ( $array['snmp']->ID == ""){
		return $l->g(837);	
	}else{
		foreach($snmp_tables_type as $id=>$table){
			$sql="select * from %s where snmp_id=%s";
			$arg=array(strtolower($table),$snmp_id);
			$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			$array[$table] = mysql_fetch_object($result);
		}
		return $array;		
	}

	/*foreach($snmp_tables_type as $id=>$table){
		$table_alias[]=$var.'.*';
		$sql.= " left join %s %s on sn.id=%s.snmp_id ";
		array_push($arg,strtolower($table));
		array_push($arg,$var);
		array_push($arg,$var);
		$var++;
	}
	
	$sql= "select ".implode(',',$table_alias).$sql." where sn.id = %s";
	array_push($arg,$snmp_id);*/
	
	
	
}


function subnet_name($systemid){
	if (!is_numeric($systemid))
	return false;	
	$reqSub = "select NAME,NETID from subnet left join networks on networks.ipsubnet = subnet.netid 
				where  networks.status='Up' and hardware_id=".$systemid;
	$resSub = mysql_query($reqSub, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while($valSub = mysql_fetch_object( $resSub )){
		
		$returnVal[]=$valSub->NAME."  (".$valSub->NETID.")";
	}	
	return 	$returnVal;
}

function print_item_header($text)
{
	echo "<br><br><table align=\"center\"  width='100%'  cellpadding='4'>";
	echo "<tr>";
	echo "<td align='center' width='100%'><b><font color='blue'>".strtoupper($text)."</font></b></td>";
	echo "</tr>";
	echo "</table><br>";	
}

function bandeau($data,$lbl_affich){
	$nb_col=2;
	echo "<table ALIGN = 'Center' class='mlt_bordure' ><tr><td align =center>";
	echo "		<table align=center border='0' width='100%'  ><tr>";
	$i=0;
	foreach ($data as $table=>$values){
		if (is_object($values)){
			foreach ($values as $field=>$field_value){
				if (trim($field_value) != ''){
					if ($i == $nb_col){
						echo "</tr><tr>";
						$i=0;			
					}
					echo "<td >&nbsp;<b>";
					if (isset($lbl_affich[$field]))
						echo $lbl_affich[$field];
					else
						echo $field;
					echo " :</b></td><td >".$field_value."</td>";
					$i++;
				}
			}
		}
		
		
	}
		
	echo "</tr></table></td></tr></table>";	
}

function deleteDid_snmp($id){
	global $snmp_tables;
	if (is_array($id))
		$id_snmp=explode(',',$id);
	else
		$id_snmp=$id;
	
	foreach ($snmp_tables as $key=>$values){
		$sql='delete from %s where snmp_id in ';
		$arg=$values;
		$del_sql=mysql2_prepare($sql,$arg,$id_snmp,$nocot=true);
		mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);		
	}	

	$sql='delete from SNMP where id in ';
	$del_sql=mysql2_prepare($sql,array(),$id_snmp,$nocot=true);
	mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);			
}


/*
 * 
 *Find all accountinfo for  
 * snmp data
 * 
 */
function admininfo_snmp($id = ""){
	global $l;
	if (!is_numeric($id) and $id != "")
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="SELECT * FROM snmp_accountinfo ";
	if (is_numeric($id)){
		$sql_account_data.= " WHERE snmp_id=%s";
		$arg_account_data=array($id);
	}else
		$sql_account_data.= " LIMIT 1 ";
	
	$res_account_data=mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	$val_account_data = mysql_fetch_array( $res_account_data );
	return $val_account_data;	
}

function updateinfo_snmp($id,$values,$list=''){
	global $l;
	if (!is_numeric($id) and $list == '')
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="UPDATE snmp_accountinfo SET ";
	foreach ($values as $field=>$val){
		$sql_account_data .= " %s='%s', ";
		array_push($arg_account_data,$field);
		array_push($arg_account_data,$val);		
	}
	$sql_account_data = substr($sql_account_data,0,-2);
	if (is_numeric($id) and $list == '')
	$sql_account_data.=" WHERE snmp_id=%s";
	if ($list != '')
	$sql_account_data.=" WHERE snmp_id in (%s)";
	
	array_push($arg_account_data,$id);	
	mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	return $l->g(1121);	
}


?>