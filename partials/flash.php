<?php
// One-time flash alerts renderer. Include this file where you want alerts to appear.
$alerts = $_SESSION['alerts'] ?? [];
if (!empty($alerts)) {
    unset($_SESSION['alerts']); // consume alerts so they don't reappear on other pages
    ?>
    <div class="alert-box show">
        <?php foreach ($alerts as $alert): ?>
            <div class="alert <?= $alert['type']; ?>">
                <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-error-circle'; ?>'></i>
                <span><?= $alert['message']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>

