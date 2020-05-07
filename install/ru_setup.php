<?php

session_start();

$steps=array("Мастер установки - «GOLDSVET» Casino Management System", "Лицензионное соглашение", "Проверка установленных компонентов PHP", "Проверка на запись у важных файлов системы", "Данные для доступа к MySQL серверу", "Данные для доступа к панели управления", "Установка завершена");

$writable_files=array('../engine/dbcfg.php');

$curr_step=isset($_POST['step'])? $_POST['step']: 0;

$po="«GOLDSVET» Casino Management System";

//действия

if ($curr_step==5)
  {
  //Заполняем БД
    $configfile = '../engine/dbcfg.php';

	$dbhost = 'localhost';
	$dbname = 'db host';
	$dbuser = 'db user';
	$dbpassword = 'db password';
		
	if(isset($_POST['dbhost']))
		$dbhost = addslashes($_POST['dbhost']);
	if(isset($_POST['dbname']))
		$dbname = addslashes($_POST['dbname']);
	if(isset($_POST['dbuser']))
		$dbuser = addslashes($_POST['dbuser']);
	if(isset($_POST['dbpassword']))
		$dbpassword = addslashes($_POST['dbpassword']);

	if(!empty($dbname) && !empty($dbuser)&& !empty($dbhost))
	{
		if(!@mysql_connect($dbhost, $dbuser, $dbpassword))
			$error = 'Не могу соединиться с базой. Проверьте логин и пароль';
		if(!@mysql_select_db($dbname))
			$error = "Базы данных $dbname не существует.";
		if(!@mysql_query('SET NAMES utf8'))
			$error = 'Не могу соединиться с базой. Проверьте логин и пароль';
		
		if(!is_readable('ru_setup.sql'))
			$error = 'Файл ru_setup.sql не найден';
			
		if(!isset($error))
		{
			mysqlrestore('ru_setup.sql');
      
      $domen=$_SERVER['HTTP_HOST'];
      mysql_query("update settings set val='$domen' where key_name='url'");
      
      $btc_callback=mysql_result(mysql_query("select val from settings where key_name='btc_callback'"),0);
      $btc_callback=str_replace('your_domain.com',$domen,$btc_callback);
      mysql_query("update settings set val='$btc_callback' where key_name='btc_callback'");
			
			# Запишем конфиги с базой
			$config = file_get_contents($configfile);
			$config = preg_replace("/database.*;/i", 'database = "'.$dbname.'";', $config);
			$config = preg_replace("/hostname.*;/i", 'hostname = "'.$dbhost.'";', $config);
			$config = preg_replace("/mysql_login.*;/i", 'mysql_login = "'.$dbuser.'";', $config);
			$config = preg_replace("/mysql_password.*;/i", 'mysql_password = "'.$dbpassword.'";', $config);
			if (@!file_put_contents($configfile, $config))
      	$error="Произошла ошибка при записи конфигурационных данных в $configfile";
		}
	}
  else
    $error="Невозможно соединиться с MySQL сервером по указанным доступам. Введите корректные данные доступа для соединения с БД MySQL";  
  }
elseif ($curr_step==6)
  {
  include_once ('../engine/cfg.php');
  include_once ('../engine/inc/functions.php');
  
  $_SESSION['base_dir']=realpath(dirname(__FILE__)."/..");	
  
  $domen=get_domen();
  $key_ = gen_key($domen);
    mysql_query("update settings set val='$key_' where key_name='lic_key'");
  	
  if(isset($_POST['username']) && isset($_POST['password']))
	{
	  if($_POST['password']==$_POST['confirm_password'])
	  {
    include_once ('../engine/cfg.php');
    include_once ('../engine/inc/functions.php');
    mysql_query ("delete from users");
    $sql = 'insert into users (id,login,pass,email,balance,status,creator) values (1,"'.$_POST['username'].'", "'.as_md5( $key_, $_POST['password'] ).'","'.$_POST['email'].'",0,1,0)';
		mysql_query($sql) or $error = 'Ошибка при записи в БД '.mysql_error();
		$_SESSION['login']= $_POST['username'];
		}
		else
		$error="Подтверждение пароля не совпадает";
	}
  }
  
if (isset($error))
  $curr_step--;

switch($curr_step)
		{
			
      case 0:
      $content="
		Добро пожаловать в мастер установки <b>«GOLDSVET» Casino Management System</b>. Данный мастер поможет вам установить систему всего за несколько минут.<br><br>
		Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов.<br><br>
		Обращаем Ваше внимание на то, что <b>«GOLDSVET» Casino Management System</b> поддерживает работу с ЧПУ, а для этого необходимо, чтобы был установлен модуль <b>modrewrite</b> и его использование было разрешено.<br><br>
		<font color=\"red\">Внимание: при установке системы создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки, поэтому после успешной установки удалите папку <b>/install/</b> во избежание повторной установки системы!</font><br><br>
		Приятной Вам работы с програмным обеспечением <b>".$po."</b>";
		  
      break;
      case '2':
      $content="
				
<table class=\"table table-normal table-bordered\">
<tr>
<td width=\"250\">Минимальные требования системы</td>
<td colspan=\"2\">Текущее значение</td>
</tr>";

	if( version_compare(phpversion(), '5.6', '<') ) {
		 $status = '<font color=red><b>Нет</b></font>';
	} else {
		$status = '<font color=green><b>Да</b></font>';
    }
	
$content.= "<tr>
         <td>Версия PHP 5.6</td>
         <td colspan=2>$status</td>
         </tr>";
	
    $status = function_exists('mysqli_connect') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';

$content.= "<tr>
         <td>Поддержка MySQLi</td>
         <td colspan=2>$status</td>
         </tr>";

    $status = extension_loaded('xml') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';

$content.= "<tr>
         <td>Поддержка XML</td>
         <td colspan=2>$status</td>
         </tr>";

    $status = function_exists('mb_convert_encoding') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

$content.= "<tr>
         <td>Поддержка многобайтных строк</td>
         <td colspan=2>$status</td>
         </tr>";
		 
    $status = extension_loaded('gd') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

$content.= "<tr>
         <td>Поддержка GD</td>
         <td colspan=2>$status</td>
         </tr>";
		 
    $status = extension_loaded('ionCube Loader') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

$content.= "<tr>
         <td>Поддержка IonCube Loader</td>
         <td colspan=2>$status</td>
         </tr>";
		 
    $status = extension_loaded('sockets') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

$content.= "<tr>
         <td>Поддержка Web Sockets</td>
         <td colspan=2>$status</td>
         </tr>";

$content.= "<tr>
         <td colspan=3><br />Если любой из этих пунктов выделен красным, то пожалуйста выполните действия для исправления положения. В случае несоблюдения минимальных требований скрипта возможна его некорректная работа в системе.<br /><br /></td>
         </tr>";		 

$content.= "</table>";
                        
      break;
      case '1':
      $content="
		Пожалуйста, внимательно прочитайте и примите пользовательское соглашение по использованию <b>«GOLDSVET» Casino Management System</b>.<br /><br />
		<div style=\"height: 300px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;\">
		<b>1. Предмет лицензионного соглашения</b><br /><br />
		Предметом настоящего лицензионного соглашения (далее Соглашение) является право использования одной лицензионной копии программного продукта <b>«GOLDSVET» Casino Management System</b>, (далее Программный продукт), в порядке и на условиях, установленных настоящим Соглашением. Если вы не согласны с условиями данного Соглашения, вы не можете использовать данный Программный продукт. Установка и использование Программного продукта означает ваше полное согласие со всеми пунктами настоящего Соглашения.<br /><br />
		<b>2. Авторские права</b><br /><br />
		<b>2.1.</b> Программный продукт <b>«GOLDSVET» Casino Management System</b>, является интеллектуальной собственностью Дянина Станислава Владимировича (далее Правообладатель).<br /><br />
		<b>2.2.</b> Программный продукт защищён законом России «Об авторском праве и смежных правах», а также международными договорами.<br /><br />
		<b>2.3.</b> К правам собственности и авторским правам относятся исходные коды, тексты, изображениям и прочие объекты авторского права.<br /><br />
		<b>2.4.</b> В случае нарушения авторских прав предусматривается ответственность в соответствии с действующим законодательством.<br /><br />
		<b>3. Объём прав, предоставляемых настоящим Соглашением</b><br /><br />
		<b>3.1.</b> Настоящее Соглашение предоставляет вам следующие права:<br /><br />
		<b>3.1.1.</b> Право установить и использовать одну копию Программного продукта на одном доменном имени;<br /><br />
		<b>3.1.2.</b> Право изготовить копию Программного продукта при условии, что эта копия предназначена только для архивных целей и для замены правомерно приобретенного экземпляра в случаях, когда оригинал утерян, уничтожен или стал непригоден для использования. Данная копия не может быть использована для иных целей и должна быть уничтожена в случае, если владение экземпляром Программного продукта перестанет быть правомерным;<br /><br />
		<b>3.1.3.</b> Право на получение обновлений Программного продукта через интернет согласно договору;<br /><br />
		<b>3.1.4.</b> Право изменять дизайн Программного продукта, в соответствии с нуждами вашего сайта, но не нарушая других условий данного соглашения;<br /><br />
		<b>3.1.5.</b> Право создавать приложения, интерфейс которых будет успешно работать с Программным продуктом и которые не затрагивают исходный код системы, указав, что это ваш оригинальный продукт;<br /><br />
		<b>3.2.</b> В соответствии с условиями настоящего Соглашения вы не имеете права:<br /><br />
		<b>3.2.1.</b> Производить модификацию Программного продукта, использовать его компоненты в каких-либо других продуктах;<br /><br />
		<b>3.2.2.</b> Передавать другим лицам права, предоставляемые вам настоящим Соглашением (путем проката, аренды или передачи во временное пользование);<br /><br />
		<b>3.2.3.</b> Удалять или исправлять в Программном продукте любые знаки о праве собственности и авторских правах на нее;<br /><br />
		<b>3.2.4.</b> Распространять Программный продукт или индивидуальные копии файлов, библиотек и другого исходного кода продукта;<br /><br />
		<b>3.2.5.</b> Модифицировать Программный продукт для работы одного комплекта исходных файлов сразу на нескольких Адресах (URL, доменах, поддоменах, и т.д.). Для каждого сайта требуется отдельная лицензия;<br /><br />
		<b>3.2.6.</b> Передавать и/или продавать лицензию третьему лицу без предварительного уведомления правообладателя;<br /><br />
		<b>3.2.6.1.</b> Переоформление лицензии на третье лицо, стоит 50% от стоимости Программного продукта;<br /><br />
		<b>3.3.</b> Настоящее Соглашение не предоставляет вам никаких прав в отношении товарных знаков или названий, принадлежащих Правообладателю.<br /><br />
		<b>3.4.</b> Настоящее Соглашение не предоставляет право на получение новой версии Программного продукта на безвозмездной основе.<br /><br />
		<b>3.5.</b> При неуплате рассрочки в установленные сроки (три календарных месяца), начисляется пеня в размере 10% с блокировкой лицензионной копии.<br /><br />
		<b>4. Срок действия Соглашения</b><br /><br />
		<b>4.1.</b> Настоящее Соглашение и права, предоставляемые им, вступают в силу в момент установки Программного продукта и действуют в течении срока действия лицензии.<br /><br />
		<b>4.2.</b> По истечении срока действия Вашей лицензии на Программный продукт и в случае нежелания ее продлевать Вы имеете право использовать продукт согласно данному Соглашению, но без услуг технической поддержки.<br /><br />
		<b>4.3.</b> Без ущерба каких-либо своих прав Правообладатель вправе прекратить действие настоящего Соглашения при несоблюдении вами его условий и ограничений.<br /><br />
		<b>4.4.</b> При прекращении действия настоящего Соглашения вы обязаны уничтожить все имеющиеся у вас копии и компоненты Программного продукта.<br /><br />
		<b>5. Ответственность сторон</b><br /><br />
		<b>5.1.</b> Правообладатель гарантирует, что работа Программного продукта будет соответствовать описанию, данному в документации.<br /><br />
		<b>5.2.</b> Правообладатель не несет никакой ответственности в случае несовместимости Программного продукта с Вашим сервером.<br /><br />
		<b>5.3.</b> Правообладатель не несет ответственность за какой-либо ущерб, связанный с использованием или невозможностью использования Программного продукта.<br /><br />
		<b>5.4.</b> За нарушение авторских прав на Программный продукт нарушитель несет гражданскую, административную или уголовную ответственность в соответствии с законодательством.<br /><br />
		<b>5.5.</b> В случае, если пользователь меняет ядро продукта или структуру базы данных Программного продукта, Правообладатель не гарантирует бесперебойную работу программы, а также безопасность ядра.<br /><br />
		<b>5.6.</b> В связи с частично открытым кодом Программного Продукта, возврат средств не предусматривается настоящим Соглашением.<br /><br />
		<b>6. Содержание</b><br /><br />
		<b>6.1.</b> Правообладатель <b>«GOLDSVET» Casino Management System</b>, оставляет за собой право публиковать списки избранных клиентов своих Программных продуктов.<br /><br />
		<b>6.1.1.</b> Для того, чтобы вас внесли в список клиентов, необходимо, чтобы ваш проект соответствовал следующим требованиям:<br /><br />
		- копия Программного продукта, должна быть законно приобретена у Правообладателя продукта.<br /><br />
		- на вашем проекте должен быть размещён логотип <b>«GOLDSVET» Casino Management System</b>, с описанием, что это лицензионная копия и ссылкой на официальный сайт Программного продукта, или страницу на официальном сайте с описанием вашей системы.<br /><br />
		- дизайн сайта должен быть утверждён сотрудниками <b>«GOLDSVET» Casino Management System</b>, или заказан у дизайнеров.<br /><br />
		<b>6.1.2.</b> Ваш сайт может быть опубликован на усмотрение сотрудников <b>«GOLDSVET» Casino Management System</b>, без соблюдений пункта 6.1.1., если ваш проект изготавливался «под ключ».<br /><br />
		<b>6.2.</b> Правообладатель <b>«GOLDSVET» Casino Management System</b>, оставляет за собой право в любое время изменять условия данного лицензионного соглашения, но данные действия не имеют и не будут иметь обратной силы.<br /><br />
        </div>";

      break;
      case 3:
      $content="
<table class=\"table table-normal table-bordered\">

<thead><tr>
<td>Папка/Файл</td>
<td width=\"100\">CHMOD</td>
<td width=\"100\">Статус</td></tr></thead><tbody>";

$important_files = array(
'../engine/logs',
'../engine/templates_c',
'../engine/cfg.php',
'../engine/dbcfg.php',
'../engine/ini.php',
'../engine/view.php',
);


$chmod_errors = 0;
$not_found_errors = 0;
    foreach($important_files as $file){

        if(!file_exists($file)){
            $file_status = "<font color=red>не найден!</font>";
            $not_found_errors ++;
        }
        elseif(is_writable($file)){
            $file_status = "<font color=green>разрешено</font>";
        }
        else{
            @chmod($file, 0777);
            if(is_writable($file)){
                $file_status = "<font color=green>разрешено</font>";
            }else{
                @chmod("$file", 0755);
                if(is_writable($file)){
                    $file_status = "<font color=green>разрешено</font>";
                }else{
                    $file_status = "<font color=red>запрещено</font>";
                    $chmod_errors ++;
                }
            }
        }
        $chmod_value = @decoct(@fileperms($file)) % 1000;

$content.= "<tr>
         <td>$file</td>
         <td>$chmod_value</td>
         <td>$file_status</td>
         </tr>";
    }
    
if($chmod_errors == 0 and $not_found_errors == 0){

    $status_report = 'Проверка успешно завершена! Можете продолжить установку!';

} else {
    
    if($chmod_errors > 0){
        $status_report = "<font color=red>Внимание!!!</font><br /><br />Во время проверки обнаружены ошибки: <b>$chmod_errors</b>. Запрещена запись в файл.<br />Вы должны выставить для папок CHMOD 777, для файлов CHMOD 666, используя FTP клиент.<br /><br /><font color=red><b>Настоятельно не рекомендуется</b></font> продолжать установку, пока не будут произведены изменения.<br />";
    }

    if($not_found_errors > 0){
        $status_report .= "<font color=red>Внимание!!!</font><br />Во время проверки обнаружены ошибки: <b>$not_found_errors</b>. Файлы не найдены!<br /><br /><font color=red><b>Не рекомендуется</b></font> продолжать установку, пока не будут произведены изменения.<br />";
    }
}

$content.= "<tr><td height=\"25\" colspan=3>&nbsp;&nbsp;Состояние проверки</td></tr><tr><td style=\"padding: 5px\" colspan=3>$status_report</td></tr>

</tbody></table>";
		  
      break;
      case 4:
      $dbhost =isset($_POST['dbhost'])? $_POST['dbhost']:'localhost';
	  $dbname =isset($_POST['dbname'])? $_POST['dbname']:'';
	  $dbuser =isset($_POST['dbuser'])? $_POST['dbuser']:'';
      $dbpassword =isset($_POST['dbpassword'])? $_POST['dbpassword']:'';
      $content="
<table width=\"100%\">
<tr><td style=\"padding: 5px;\">Сервер MySQL:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"dbhost\" value=\"$dbhost\"></tr>
<tr><td style=\"padding: 5px;\">Имя базы данных:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"dbname\" value=\"$dbname\"></tr>
<tr><td style=\"padding: 5px;\">Имя пользователя:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"dbuser\" value=\"$dbuser\"></tr>
<tr><td style=\"padding: 5px;\">Пароль:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"dbpassword\" value=\"$dbpassword\"></tr>
</table>";

      break;      
      case 5:
      $username=isset($_POST['username'])? $_POST['username']: 'admin';
      $email=isset($_POST['email'])? $_POST['email']:'admin@admin.com';
      $content="
<table width=\"100%\">
<tr><td style=\"padding: 5px;\">Имя администратора:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"username\" value=\"$username\"></tr>
<tr><td style=\"padding: 5px;\">Пароль:<td style=\"padding: 5px;\"><input type=password size=\"28\" name=\"password\" value=\"admin\"> <b>не</b> забудьте пароль!</tr>
<tr><td style=\"padding: 5px;\">Повторите пароль:<td style=\"padding: 5px;\"><input type=password size=\"28\" name=\"confirm_password\" value=\"admin\"></tr>
<tr><td style=\"padding: 5px;\">E-mail:<td style=\"padding: 5px;\"><input type=text size=\"28\" name=\"email\" value=\"$email\"></tr>
</table>";

      break;
      case 6:      
      $content=" 
		Поздравляем Вас, <b>«GOLDSVET» Casino Management System</b> был успешно установлен на Ваш сервер. Вы можете зайти теперь на главную <a class=\"status-info\" href=\"/\">страницу вашего сайта</a>  и посмотреть возможности системы. Либо Вы можете <a class=\"status-info\" href=\"/adm/\">зайти</a> в панель управления <b>«GOLDSVET» Casino Management System</b> и изменить другие настройки системы.<br><br>
		<font color=\"red\">Внимание: при установке системы создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки, поэтому после успешной установки удалите папку <b>/install/</b> во избежание повторной установки системы!</font><br><br>
		Приятной Вам работы с програмным обеспечением <b>".$po."</b>";
  
    }

?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <title><?=$steps[$curr_step]?></title>
  <link href="css/application.css" media="screen" rel="stylesheet" type="text/css" />
<style type="text/css">
body {
	background: url("images/bg.png");

}
</style>
</head>
<body>
<form action="" method=post>
<div class="container">
<div class="col-md-8 col-md-offset-2">
    <div class="padded">
	<div style="margin-top: 10px;">
<input type="hidden" name="action" value="eula">
<div class="box">
  <div class="box-header">
    <div class="title"><?=$steps[$curr_step]?></div>
  </div>
  <div class="box-content">
	<div class="row box-section">
    		 	    <?php
    		 	    if (isset($error))
    		 	      echo "<div class='message error'>Ошибка: $error</div><br />";
              if (isset($msg))
                echo "<div class='message'>$msg</div><br />"; 
              print $content?> 
	</div>

           <?php
           if($curr_step+1<count($steps)) {?>
		   <div class='row box-section'>
           <input class='btn btn-green' type="hidden" name="step" value="<?=$curr_step+1?>"/>	 	                    
           <input class='btn btn-green' type='submit' class='button' value='Далее' />
		   </div>
           <?php } ?>
		   
  </div>
</div>
    </div>
    </div>
</div>
</div>
</form>
</body>
</html>


<?php

##################################################################
##################################################################


function mysqlrestore($filename)
{ global $error;
  $templine = '';
  $fp = fopen($filename, 'r');

  // Loop through each line
  if($fp)
  while(!feof($fp)) {
    $line = fgets($fp);
    // Only continue if it's not a comment
    if (substr($line, 0, 2) != '--' && $line != '') {
      // Add this line to the current segment
      $templine .= $line;
      // If it has a semicolon at the end, it's the end of the query
      if (substr(trim($line), -1, 1) == ';') {
        // Perform the query
        mysql_query($templine) or $error='Error performing query \'<b>' . $templine . '</b>\': ' . mysql_error() . '<br /><br />';
        // Reset temp variable to empty
        $templine = '';
      }
    }
  }

  fclose($fp);
}

?>