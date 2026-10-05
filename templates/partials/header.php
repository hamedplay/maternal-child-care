<?php
$activePage = $activePage ?? '';
$pregnancyPages = ['pregnancy-weeks', 'pregnancy-nutrition', 'pregnancy-supplements', 'pregnancy-warning-signs', 'pregnancy-exercises'];
$childPages = ['growth', 'vaccine', 'child-nutrition', 'milestones', 'conditions', 'children'];
$isPregnancyActive = in_array($activePage, $pregnancyPages, true);
$isChildActive = in_array($activePage, $childPages, true);
$isLoggedIn = isset($_SESSION['user_id']);
$userFullName = $_SESSION['user_full_name'] ?? null;
$userRole = $_SESSION['user_role'] ?? 'user';
$userPhoneDisplay = '';
if ($isLoggedIn && !empty($_SESSION['user_phone'])) {
    $userPhoneDisplay = strtr($_SESSION['user_phone'], ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}
?>
<header class="header-wrapper" id="headerWrapper">
    <div class="header" id="header">
        <a href="index.php" class="logo-area" aria-label="مراقبت مادر و کودک"><img src="assets/image/logo.png" alt="مراقبت مادر و کودک" class="logo-image" /></a>
        <ul class="nav-menu">
            <li><a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">خانه</a></li>
            <li class="has-dropdown">
                <button type="button" class="nav-parent-link <?= $isPregnancyActive ? 'active' : '' ?>" aria-expanded="false">بارداری <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg></button>
                <ul class="dropdown-menu">
                    <li><a href="pregnancy-weeks.php" class="<?= $activePage === 'pregnancy-weeks' ? 'active' : '' ?>">هفته به هفته بارداری</a></li>
                    <li><a href="pregnancy-nutrition.php" class="<?= $activePage === 'pregnancy-nutrition' ? 'active' : '' ?>">تغذیه دوران بارداری</a></li>
                    <li><a href="pregnancy-supplements.php" class="<?= $activePage === 'pregnancy-supplements' ? 'active' : '' ?>">مکمل‌های ضروری</a></li>
                    <li><a href="pregnancy-warning-signs.php" class="<?= $activePage === 'pregnancy-warning-signs' ? 'active' : '' ?>">علائم خطر</a></li>
                    <li><a href="pregnancy-exercises.php" class="<?= $activePage === 'pregnancy-exercises' ? 'active' : '' ?>">ورزش‌های مناسب</a></li>
                </ul>
            </li>
            <li class="has-dropdown">
                <button type="button" class="nav-parent-link <?= $isChildActive ? 'active' : '' ?>" aria-expanded="false">کودک <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg></button>
                <ul class="dropdown-menu">
                    <li><a href="children.php" class="<?= $activePage === 'children' ? 'active' : '' ?>">کودکان من</a></li>
                    <li><a href="growth.php" class="<?= $activePage === 'growth' ? 'active' : '' ?>">رشد و پیشرفت</a></li>
                    <li><a href="vaccine.php" class="<?= $activePage === 'vaccine' ? 'active' : '' ?>">زمان‌بندی واکسن</a></li>
                    <li><a href="child-nutrition.php" class="<?= $activePage === 'child-nutrition' ? 'active' : '' ?>">تغذیه کودک</a></li>
                    <li><a href="milestones.php" class="<?= $activePage === 'milestones' ? 'active' : '' ?>">مراحل تکامل</a></li>
                    <li><a href="conditions.php" class="<?= $activePage === 'conditions' ? 'active' : '' ?>">مشکلات شایع</a></li>
                </ul>
            </li>
            <li><a href="articles.php" class="<?= $activePage === 'articles' ? 'active' : '' ?>">مقالات</a></li>
            <li><a href="ai-qa.php" class="<?= $activePage === 'ai-qa' ? 'active' : '' ?>">پرسش از هوش مصنوعی</a></li>
            <li><a href="doctors.php" class="<?= $activePage === 'doctors' ? 'active' : '' ?>">پزشکان</a></li>
        </ul>

        <?php if ($isLoggedIn): ?>
            <div class="profile-menu" id="profileMenu">
                <button type="button" class="profile-icon" id="profileMenuTrigger" aria-label="حساب کاربری" aria-expanded="false"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg><span class="profile-online-dot"></span></button>
                <div class="profile-dropdown" id="profileDropdown" hidden>
                    <?php if ($userFullName): ?><div class="profile-dropdown-name"><span><?= htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                    <div class="profile-dropdown-phone"><bdi dir="ltr"><?= htmlspecialchars($userPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></bdi></div>
                    <a href="profile.php" class="profile-dropdown-logout">پروفایل من</a>
                    <?php if ($userRole === 'admin'): ?><a href="admin.php" class="profile-dropdown-logout">مدیریت سامانه</a><?php endif; ?>
                    <a href="auth-logout.php" class="profile-dropdown-logout">خروج از حساب</a>
                </div>
            </div>
        <?php else: ?>
            <a href="#" class="profile-icon" id="authModalTrigger" aria-label="ورود یا ثبت‌نام"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg></a>
        <?php endif; ?>
    </div>
</header>

<?php include TEMPLATES_PATH . '/components/auth-modal.php'; ?>
<link rel="stylesheet" href="assets/css/components/auth-modal.css">
<script src="assets/js/components/auth-modal.js" defer></script>
<?php include TEMPLATES_PATH . '/partials/doctor-widget.php'; ?>
<?php include TEMPLATES_PATH . '/partials/support-widget.php'; ?>
