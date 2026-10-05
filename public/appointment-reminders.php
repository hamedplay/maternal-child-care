<?php
require_once __DIR__ . '/../config/config.php';
use App\Repositories\AppointmentRepository;
use App\Services\KavenegarService;
header('Content-Type: application/json; charset=utf-8');
$config=require BASE_PATH.'/config/services.php';$secret=(string)($config['reminder_cron_secret']??'');$provided=(string)($_SERVER['HTTP_X_CRON_SECRET']??($_GET['secret']??''));
if($secret===''||!hash_equals($secret,$provided)){http_response_code(403);echo json_encode(['error'=>'forbidden']);exit;}
$repo=new AppointmentRepository();$sms=new KavenegarService();$sent=0;
foreach($repo->dueReminders() as $item){$when=date('Y/m/d H:i',strtotime($item['scheduled_at']));$message="یادآوری نوبت پزشک: {$when} با {$item['doctor_name']}";if($sms->sendMessage($item['phone'],$message)){$repo->markReminderSent((int)$item['id']);$sent++;}}
echo json_encode(['success'=>true,'sent'=>$sent],JSON_UNESCAPED_UNICODE);
