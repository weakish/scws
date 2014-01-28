<?php
// hightman, SCWS v4 (built as php_extension)
// 2007/06/02
//
// view the source code
if (isset($_SERVER['QUERY_STRING']) 
	&& !strncasecmp($_SERVER['QUERY_STRING'], 'source', 6))
{
	highlight_file(__FILE__);
	exit(0);
}

// try to count the time
function get_microtime()
{
	list($usec, $sec) = explode(' ', microtime()); 
	return ((float)$usec + (float)$sec); 
}
$time_start = get_microtime();

// demo data
if (!isset($_REQUEST['mydata']) || empty($_REQUEST['mydata']))
	$mydata = file_get_contents('sample.txt');	
else
{
	$mydata = & $_REQUEST['mydata'];
	if (get_magic_quotes_gpc())
		$mydata = stripslashes($mydata);
}

// other options
$ignore = $showa = $stats = $duality = false;
$checked_ignore = $checked_showa = $checked_stats = $checked_duality = '';

// �Ƿ�������������
if (isset($_REQUEST['ignore']) && !strcmp($_REQUEST['ignore'], 'yes'))
{
	$ignore = true;
	$checked_ignore = ' checked';
}

// �Ƿ�ɢ���Զ���Ԫ
if (isset($_REQUEST['duality']) && !strcmp($_REQUEST['duality'], 'yes'))
{
	$duality = true;
	$checked_duality = ' checked';
}

// �Ƿ���ע����
if (isset($_REQUEST['showa']) && !strcmp($_REQUEST['showa'], 'yes'))
{
	$showa = true;
	$checked_showa = ' checked';
}

// �Ƿ���ͳ�Ʊ�
if (isset($_REQUEST['stats']) && !strcmp($_REQUEST['stats'], 'yes'))
{
	$stats = true;
	$checked_stats = ' checked';
}

// �Ƿ񸴺Ϸִ�?
$multi = 0;
if (isset($_REQUEST['multi']) && is_array($_REQUEST['multi'])){
	foreach ($_REQUEST['multi'] as $mval) $multi |= intval($mval);
}
$mtags = array('���̴�' => 1, '��Ԫ' => 2, '��Ҫ����' => 4, 'ȫ������' => 8);

$xattr = &$_REQUEST['xattr'];
if (!isset($xattr)) $xattr = '~v';
$limit = &$_REQUEST['limit'];
if (!isset($limit)) $limit = 10;

// do the segment
$cws = scws_new();
$cws->set_charset('gbk');
$cws->set_rule(ini_get('scws.default.fpath') . '/rules.ini');
$cws->set_dict(ini_get('scws.default.fpath') . '/dict.xdb');

//
// use default dictionary & rules
//
$cws->set_duality($duality);
$cws->set_ignore($ignore);
$cws->set_multi($multi);
$cws->send_text($mydata);
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=gbk">
<title>PHP�������ķִ�(SCWS) ��4��������ʾ (by hightman)</title>
<style type="text/css">
<!--
td, body	{ background-color: #efefef; font-family: tahoma; font-size: 14px; word-break: break-all; }
.demotx		{ font-size: 12px; width: 100%; height: 140px; }
small		{ font-size: 12px; }
//-->
</style>
</head>
<body>
<h3>
  <font color=red>PHP�������ķִ�(SCWS)</font>
  <font color=blue>��4��(GBK)</font> - ������ʾ (by hightman)
</h3>  
��������: ���ݴ�Ƶ�ʵ���Ϊ���ܵ����ķִʣ�֧�ֹ���ʶ�������������ȡ���<a href="v48.php">UTF-8������</a> <a href="v48.cht.php">������</a>��<br />
<a href="http://www.xunsearch.com" target="_blank">�Ƽ����������� scws + xapian �����Ŀ�Դȫ���������� xunsearch ����</a> <hr />

<table width=100% border=0>
  <tr>
    <form method=post>
	<td width=100%>
	  <strong>���������ֵ����ύ���Էִ�: </strong> <br />
	  <textarea name=mydata cols=60 rows=14 class=demotx><?php echo $mydata; ?></textarea>
	  <small>
	    <span style="color:#666666;">		
	    <strong>[���Ϸִ�ѡ��]</strong>
<?php foreach ($mtags as $mtag => $mval) { ?>
		<input type=checkbox name="multi[]" value=<?php echo $mval . " " . (($multi & $mval) ? " checked" : "");?>><?php echo $mtag;?>&nbsp;
<?php } ?>
		</span>
	    <br />
		<input type=checkbox name=ignore value="yes"<?php echo $checked_ignore;?>> ������������
		&nbsp;
		<input type=checkbox name=duality value="yes"<?php echo $checked_duality;?>> ɢ�ֶ�Ԫ
		&nbsp;
		<input type=checkbox name=showa value="yes"<?php echo $checked_showa;?>> <font color=green>��ע����</font>
		&nbsp;
		<input type=checkbox name=stats value="yes"<?php echo $checked_stats;?>> <font color=red>ֻ��ͳ��</font>
		<input type=text name=limit size=2 value="<?php echo intval($limit);?>">��
		&nbsp;
		ͳ�ƴ���: 
		<input type=text name=xattr size=8 value="<?php echo htmlspecialchars($xattr);?>">(������,�ֿ� ��~��ͷ��ʾ������)
	  </small>
	  <input type=submit>
	  </td>
	  </form>
	</tr>
	<tr>
	  <td><hr /></td>
	</tr>
	<tr>
	  <td width=100%>
	    <strong>�ִʽ���(ԭ���ܳ��� <?php echo strlen($mydata); ?> �ַ�) </strong>
		(<a href="http://bbs.xunsearch.com/forumdisplay.php?fid=8" target="_blank">���ηִʽ������ԣ������㱨</a>)
		<br />
		<textarea cols=60 rows=14 class=demotx readonly style="color:#888;">
<?php
if ($stats == true)
{
	// stats
	printf("No. WordString               Attr  Weight(times)\n");
	printf("-------------------------------------------------\n");
	$list = $cws->get_tops($limit, $xattr);
	$cnt = 1;
	settype($list, 'array');
	foreach ($list as $tmp)
	{
		printf("%02d. %-24.24s %-4.2s  %.2f(%d)\n",
			$cnt, $tmp['word'], $tmp['attr'], $tmp['weight'], $tmp['times']);
		$cnt++;
	}
}
else
{
	// segment
	while ($res = $cws->get_result())
	{
		foreach ($res as $tmp)
		{
			if ($tmp['len'] == 1 && $tmp['word'] == "\r")
				continue;
			if ($tmp['len'] == 1 && $tmp['word'] == "\n")
				echo $tmp['word'];
			else if ($showa)
				printf("%s/%s ", $tmp['word'], $tmp['attr']);
			else
				printf("%s ", $tmp['word']);
		}
		flush();
	}
}

$cws->close();
$time_end = get_microtime();
$time = $time_end - $time_start;
?>
		</textarea>
		<small>
		  �ִʺ�ʱ: <?php echo $time; ?>��
		  <a href="../">����scws��ҳ</a>��ֱ��<a href="?source" target="_blank">�鿴Դ��</a> Powered by <?php echo scws_version();?>
		</small>
	</td>
  </tr>
</table>
</body>
</html>
