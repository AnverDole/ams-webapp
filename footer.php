<?php

require_once __DIR__ . "/app/flow.php";
checkFlowAccess();

require_once __DIR__ . "/helpers/layout_hooks.php";

addToHead(function () {
?>
    <link rel="stylesheet" href="<?= assetUrl('/styles/footer.css') ?>">
<?php
});



function renderFooter()
{
?>
    <footer class="d-flex justify-content-end align-items-center">
        <span class="text-muted me-4">© 2022</span>
        <p class="text-muted m-0 py-2">MEALIM GHIL AL-SHBOUL TRAD</p>
    </footer>
<?php
}
