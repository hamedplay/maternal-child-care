<?php
require_once __DIR__ . '/../config/config.php';
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
if (!isset($_SESSION['user_id'])) { header('Location: doctors.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: doctors.php'); exit; }
$doctorId=(int)($_POST['doctor_id']??0);$raw=trim((string)($_POST['scheduled_at']??''));$reason=trim((string)($_POST['reason']??''));
$time=strtotime($raw);if(!$doctorId||!$time||$time<=time()||!(new DoctorRepository())->findById($doctorId)){header('Location: doctors.php?appointment_error=1');exit;}
$ok=(new AppointmentRepository())->create((int)$_SESSION['user_id'],$doctorId,date('Y-m-d H:i:s',$time),$reason!==''?$reason:null);
header('Location: appointments.php?created='.($ok?'1':'0'));exit;
