<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();

function renderFavicon()
{
?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= assetUrl('/favicon/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= assetUrl('/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= assetUrl('/favicon/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= assetUrl('/favicon/site.webmanifest') ?>">
<?php
}
