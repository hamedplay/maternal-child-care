<?php $doctorOpen = isset($_GET['doctor']) && $_GET['doctor'] === '1'; ?>
<style>
.doctor-widget{position:fixed;left:22px;bottom:92px;z-index:9998;font-family:inherit}.doctor-fab{border:0;border-radius:999px;background:#0b6b63;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:12px 16px;cursor:pointer;display:flex;gap:8px;align-items:center}.doctor-panel{display:none;position:absolute;left:0;bottom:56px;width:260px;background:#fff;border:1px solid #e5e7eb;border-radius:18px;box-shadow:0 14px 35px rgba(0,0,0,.18);padding:16px}.doctor-widget.is-open .doctor-panel{display:block}.doctor-panel h3{margin:0 0 10px;font-size:16px}.doctor-panel a{display:block;text-decoration:none;color:#183153;padding:10px 8px;border-radius:10px}.doctor-panel a:hover{background:#f3f7f6}@media(max-width:640px){.doctor-widget{left:14px;bottom:84px}.doctor-fab span{display:none}}
</style>
<div class="doctor-widget <?= $doctorOpen ? 'is-open' : '' ?>" id="doctorWidget">
    <button type="button" class="doctor-fab" id="doctorFab" aria-expanded="<?= $doctorOpen ? 'true' : 'false' ?>">
        <span aria-hidden="true">👨‍⚕️</span><span>ارتباط با پزشک</span>
    </button>
    <div class="doctor-panel" id="doctorPanel">
        <h3>ارتباط با پزشک</h3>
        <a href="doctors.php">مشاهده پزشکان و دریافت نوبت</a>
        <a href="appointments.php">نوبت‌های من</a>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){var w=document.getElementById('doctorWidget'),b=document.getElementById('doctorFab');if(!w||!b)return;b.addEventListener('click',function(){w.classList.toggle('is-open');b.setAttribute('aria-expanded',w.classList.contains('is-open')?'true':'false')});document.addEventListener('click',function(e){if(!w.contains(e.target))w.classList.remove('is-open')})});
</script>
