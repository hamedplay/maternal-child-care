<?php
require_once __DIR__ . '/../config/config.php';
use App\Repositories\DoctorRepository;
$activePage = 'doctors';
$pageTitle = 'پزشکان - مراقبت مادر و کودک';
$doctors = (new DoctorRepository())->allActive();
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= htmlspecialchars($pageTitle) ?></title><link rel="stylesheet" href="assets/css/base.css"><link rel="stylesheet" href="assets/css/components/header.css"><link rel="stylesheet" href="assets/css/components/footer.css"><link rel="stylesheet" href="assets/css/pages/portal.css"></head><body>
<?php include TEMPLATES_PATH . '/partials/header.php'; ?>
<main class="portal-page"><div class="portal-head"><h1>ارتباط با پزشک</h1><a class="portal-link-btn" href="appointments.php">نوبت‌های من</a></div>
<div class="portal-grid">
<?php if (!$doctors): ?><div class="portal-card"><p>هنوز پزشکی در سامانه ثبت نشده است.</p></div><?php endif; ?>
<?php foreach ($doctors as $doctor): ?><article class="portal-card"><h2><?= htmlspecialchars($doctor['full_name']) ?></h2><p><strong><?= htmlspecialchars($doctor['specialty']) ?></strong></p><?php if (!empty($doctor['bio'])): ?><p class="portal-muted"><?= nl2br(htmlspecialchars($doctor['bio'])) ?></p><?php endif; ?>
<?php if (isset($_SESSION['user_id'])): ?><form class="portal-form" method="post" action="doctor-appointment-create.php"><input type="hidden" name="doctor_id" value="<?= (int)$doctor['id'] ?>"><label>زمان نوبت<input type="datetime-local" name="scheduled_at" required min="<?= date('Y-m-d\TH:i') ?>"></label><label>توضیح کوتاه<textarea name="reason" rows="2" maxlength="500"></textarea></label><button type="submit">ثبت نوبت</button></form><?php else: ?><p class="portal-muted">برای دریافت نوبت ابتدا وارد حساب کاربری شوید.</p><?php endif; ?></article><?php endforeach; ?>
</div></main><?php include TEMPLATES_PATH . '/partials/footer.php'; ?><script src="assets/js/components/header.js"></script></body></html>
