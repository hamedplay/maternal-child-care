<?php
require_once __DIR__.'/../config/config.php';use App\Repositories\UserRepository;
if(!isset($_SESSION['user_id'])||$_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
$data=['full_name'=>trim((string)($_POST['full_name']??'')),'email'=>trim((string)($_POST['email']??'')),'birth_date'=>trim((string)($_POST['birth_date']??'')),'city'=>trim((string)($_POST['city']??'')),'pregnancy_week'=>(int)($_POST['pregnancy_week']??0)];
if($data['email']!==''&&!filter_var($data['email'],FILTER_VALIDATE_EMAIL))$data['email']='';if($data['pregnancy_week']<1||$data['pregnancy_week']>40)$data['pregnancy_week']=null;
(new UserRepository())->updateProfile((int)$_SESSION['user_id'],$data);$_SESSION['user_full_name']=$data['full_name']?:null;header('Location: profile.php?saved=1');exit;
