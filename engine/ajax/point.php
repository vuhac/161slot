<?php
include ('../cfg.php');
include ('../ini.php');
                               

$action=isset($_REQUEST['action'])? $_REQUEST['action']: 'get';
$user=isset($_REQUEST['user'])? intval($_REQUEST['user']): false;

//изменения может делать админ  либо юзер сам себе


if ($status==1 && $user)
  {
  if ($action=='on')
    {
    //добавим юзера в розыгрыш
    $sql="update users set point_on=1 where id=$user";
    if(mysql_query("$sql"))
      echo "success";
    else
      echo "Error|Не удалось добавить пользователя в розыгрыш";  
    }
  elseif($action=='off')
    {
    //уберем юзера из розыгрыша
    $sql="update users set point_on=0 where id=$user";
    if(mysql_query("$sql"))
      echo "success";
    else
      echo "Error|Не удалось убрать пользователя из розыгрыша";  
    }
  }
?>