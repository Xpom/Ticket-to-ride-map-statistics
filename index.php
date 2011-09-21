<style>
	*
	{
		font-family: Verdana;
		font-size: 12px;
	}

	td#desc
	{
		font-weight: bold;
	}

	td#sql
	{
		font-family: Courier;
	}

	td#nn
	{
		background: #ccc;
	}

	tr#hh
	{
		background: #aaa;
		font-weight: bold;
	}

	td
	{
		padding: 2px;
	}

	table#stat
	{
		border: solid 1px #555;
	}


</style>
<?

class CONFIG
{
	const server	= "u201087.mysql.masterhost.ru";
	const db		= "u201087";
	const login		= "u201087";
	const password	= "sallus5nedn";
	const filename	= "a.csv";
	const template	= "<b>%s</b><br>\n";

	const logging	= true;
	const logdiv	= "\r\n";
}

	$conn	= mysql_connect(CONFIG::server, CONFIG::login, CONFIG::password);
	if(!$conn)
		throw new Exception("cant connect MYSQL");
	mysql_select_db(CONFIG::db, $conn);

function gg($sql)
{
	$data		= array();
	$result 	= mysql_query($sql, $GLOBALS["conn"]);
	echo mysql_error();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
		$data[]	= $row;
	return $data;
}

//$r	= gg("select * from f_usa");

//print_r($r);

function ggg($sql, $title)
{
//echo isset($title)?("<br>".$title):"";
//echo "<br>".$sql;
?><table cellspacing="0" id="stat"><?
$r	= gg($sql);
for($i=0,$m=count($r); $i<$m; $i++)
{
	$row = $r[$i];
	if($i==0)
	{
	?><tr id="hh"><td>n<?
	foreach($row as $k => $v)
	{
	?><td><?=$k?><?
	}
	?></tr><?
	}

	?><tr><td id="nn"><?=$i+1?>.<?
	foreach($row as $k => $v)
	{
	?><td><?=$v?><?
	}
	?></tr><?
}
?></table><?
}

$r_editions   = array("f_usa", "f_europe", "f_nc", "f_russia");
$r_stat   = array(
	array(
		"sql"   => "select count(1) cnt, sum(len) sm, color from %s group by color",
		"desc"  => "cell count per color"
	),
	array(
		"sql"   => "select len, count(1) cnt from %s group by len order by len",
		"desc"  => "route count per route length"
	),
	array(
		"sql"   => "select color, (select count(1) from %s b where b.color=a.color and len=1) len1, (select count(1) from %s b where b.color=a.color and len=2) len2, (select count(1) from %s b where b.color=a.color and len=3) len3, (select count(1) from %s b where b.color=a.color and len=4) len4, (select count(1) from %s b where b.color=a.color and len=5) len5, (select count(1) from %s b where b.color=a.color and len=6) len6 from %s a group by color",
		"desc"  => "route count per route length per color"
	),
	array(
		"sql"   => "select (select count(1) from %s b where a.city=b.city1 or a.city=b.city2) workload, count(1) cnt from (select city1 city from %s union select city2 city from %s) a group by workload order by workload",
		"desc"  => "city count per workload"
	),
	array(
		"sql"   => "select distinct city".
				   ", (select count(1) from %s b where a.city=b.city1 or a.city=b.city2) workload".
				   ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=1) w1".
				   ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=2) w2".
				   ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=3) w3".
				   ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=4) w4".
				   ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=5) w5".
		           ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=6) w6".
		           ", (select count(1) from %s b where (a.city=b.city1 or a.city=b.city2) and len=9) w9".
				   " from (select city1 city from %s union select city2 city from %s) a order by workload",
		"desc"  => "workload stats per city"
	),
	array(
		"sql"   => "select * from %s order by city1, city2",
		"desc"  => "pure data"
	),
);

$t_editions   = array("f_usa_tickets");
$t_stat   = array(
	array(
		"sql"   => "select c1, count(1) cnt, sum(c) summa, round(sum(c)/count(1),2) average from(SELECT city1 c1, city2 c2, cost c FROM %s a union all SELECT city2 c1, city1 c2, cost c FROM %s b) cc group by c1 order by cnt",
		"desc"  => "ticket count, sum of ticket costs and average cost of ticket - per city"
	),
	array(
		"sql"   => "select c1, count(1) cnt, sum(c) summa, round(sum(c)/count(1),2) average from(SELECT city1 c1, city2 c2, cost c FROM %s a where a.edition='original' union all SELECT city2 c1, city1 c2, cost c FROM %s b where b.edition='original') cc group by c1 order by cnt",
		"desc"  => "[original] ticket count, sum of ticket costs and average cost of ticket - per city"
	),
	array(
		"sql"   => "select * from (SELECT city1 c1, city2 c2, cost c FROM %s a union all SELECT city2 c1, city1 c2, cost c FROM %s b) cc order by c1",
		"desc"  => "all tickets per city"
	),
	array(
		"sql"   => "select * from (SELECT city1 c1, city2 c2, cost c FROM %s a where a.edition='original' union all SELECT city2 c1, city1 c2, cost c FROM %s b where b.edition='original') cc order by c1",
		"desc"  => "[original] all tickets per city"
	),
	array(
		"sql"   => "select * from %s order by city1, city2",
		"desc"  => "pure data"
	),
);

function stat_out($stat, $editions)
{

foreach($stat as $s)
{
?>
<table cellspacing="0">
	<tr>
		<td colspan="<?=count($editions)+1?>" id="desc">
			<?=$s["desc"]?>
			<tr>
		<td colspan="<?=count($editions)+1?>" id="sql">
			<?=$s["sql"]?>
	<tr>
<?
	foreach($editions as $e)
	{
		?><td width="400"><?=$e?><?
	}
?><td>&nbsp;
	<tr>
<?
	foreach($editions as $e)
	{
		?><td valign="top"><? ggg(str_replace("%s", $e, $s["sql"]), " ") ?><?
	}
?><td>&nbsp;
</table><br>
<?
}
}

stat_out($r_stat, $r_editions);
stat_out($t_stat, $t_editions);

?>

<br><img src="maps/europe_map.jpg">
<br><img src="maps/nordic_map.jpg">
<br><img src="maps/switzerland_map.jpg">
<br><img src="maps/usa_map.jpg">
