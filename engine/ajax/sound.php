<?php
include ('../cfg.php');
include ('../ini.php');
                               

$s_user_id=isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): $user_id;
$action=isset($_REQUEST['action'])? $_REQUEST['action']: 'get';

//изменения может делать админ  либо юзер сам себе


if ($status==1 || $user_id==$s_user_id )
  {
  if ($action=='togle')
    {
    //переключим звук
    $sql="update users set sound=if(sound>0,0,1) where id=$s_user_id";
    if(mysql_query("$sql"))
      echo "success";
    else
      echo "Error|Не удалось переключить звук";  
    }
  elseif($action=='get')
    {
    if($login)
      $sql="select sound from users where id=$s_user_id";
    else
      $sql="select sound from users_tmp where sid='".session_id()."'";  
    $res=@mysql_result(mysql_query($sql),0);
    if($res===false)
      {
      echo "error|Ошибка БД " .mysql_error();
      }
    elseif($res)
      echo "success|on";
    else
      echo "success|off";  
    
    }
  }
?>