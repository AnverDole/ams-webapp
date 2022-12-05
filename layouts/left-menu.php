<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();


require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../helpers/layout_hooks.php";

require_once __DIR__ . "/../footer.php";

$currentPageId = null;
$menuPageIconHtml = null;
$menuPageTitle = null;
$menuPageSteps = [];
$menuPageMainContent = null;

function setCurrentPageId($id)
{
    global $currentPageId;
    $currentPageId = $id;
}
function isCurrentPage(...$ids)
{
    global $currentPageId;
    foreach ($ids as $id) {
        if ($currentPageId == $id) {
            return true;
        }
    }
    return false;
}


function renderLeftMenu()
{
    $admin = getAuthAdmin();

?>
    <aside id="main-left-menu" class="left-menu">
        <div id="main-left-menu-heading" class="heading">
            <h3><i class="fa fa-bars" aria-hidden="true"></i> My Account</h3>
            <i class="fa fa-angle-down toggle-animater" aria-hidden="true"></i>
        </div>
        <nav>
        <div class="c-card py-3 px-4 mb-3 system-name">
            <h5 class="m-0">MEALIM GHIL AL-SHBOUL <i class="text-primary">TRAD</i></h5>
        </div>

       
            <div class="account-info c-card">
                <span class="user-name"><?= $admin["first_name"] ?> <?= $admin["last_name"] ?></span>
                <span class="user-email"><?= $admin["email"] ?></span>
                <span class="user-type"><?= $admin["type"]->name ?></span>
            </div>
            <a href="./../application-managment/search.php" class="navigation-item c-card" <?= isCurrentPage("application-managment") ? "current" : "" ?>>
                <i class="fa fa-search" aria-hidden="true"></i>
                <span>Search</span>
            </a>
            <a href="./../application-managment/new.php" class="navigation-item c-card" <?= isCurrentPage("register") ? "current" : "" ?>>
                <i class="fa fa-plus-circle" aria-hidden="true"></i>
                <span>Register</span>
            </a>
            <?php if ($admin["type"]->id == 1) /*super admin*/ { ?>
                <a href="./../admin-managment/all.php" class="navigation-item c-card" <?= isCurrentPage("admin-managment") ? "current" : "" ?>>
                    <i class="fa fa-cog" aria-hidden="true"></i>
                    <span>Admin Managment</span>
                </a>
            <?php } ?>
            <a href="javascript:void(0)" class="navigation-item c-card" data-bs-toggle="modal" data-bs-target="#logout-model">
                <i class="fa fa-sign-out" aria-hidden="true"></i>
                <span>Log Out</span>
            </a>
        </nav>
    </aside>

    <div id="logout-model" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="./../logout.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Logout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure that you wants to logout?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
}


function setPageContent($title_, $iconHtml_, $steps_, callable $main)
{
    global $menuPageTitle;
    global $menuPageSteps;
    global $menuPageMainContent;
    global $menuPageIconHtml;

    $menuPageTitle = $title_;
    $menuPageSteps = $steps_;
    $menuPageIconHtml = $iconHtml_;


    ob_start();
    $main();
    $menuPageMainContent = ob_get_clean();
}
function renderPageContent()
{
    global $menuPageTitle;
    global $menuPageSteps;
    global $menuPageMainContent;
    global $menuPageIconHtml;

    $anchers = array_map(function ($step) {
        $step = (object)$step;
        if (($step->url ?? null) == null) {
            return "<a href='javascript:void(0)' class='ancher-primary'>{$step->title}</a>";
        } else {
            return "<a href='{$step->url}' class='ancher-primary'>{$step->title}</a>";
        }
    }, $menuPageSteps);

    $breadcrumbHtml = join("<i class='fa fa-angle-right' aria-hidden='true'></i>", $anchers);
?>
    <div class="right-content">
        <div class="heading">
            <h3><?= $menuPageIconHtml ?><?= $menuPageTitle ?></h3>
            <div class="c-breadcrumb">
                <?= $breadcrumbHtml ?>
            </div>
        </div>
        <main class="main-content">
            <?= $menuPageMainContent ?>
        </main>
    </div>
<?php
}


function renderBaseLayout()
{
?>
    <div class="page-divider-wrapper">
        <div class="page-divider">
            <?= renderLeftMenu() ?>
            <?= renderPageContent() ?>
        </div>
        <?= renderFooter() ?>
    </div>
<?php
}





addToFoot(function () {
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

    <script>
        $("#main-left-menu-heading").on("click", function(e) {
            if ($("#main-left-menu").attr("expanded") == null) {
                $("#main-left-menu").attr("expanded", true);
            } else {
                $("#main-left-menu").removeAttr("expanded");
            }
        });
    </script>
<?php
});

addToHead(function () {
?>
    <link rel="stylesheet" href="<?= assetUrl('/styles/left-menu.css') ?>">
<?php
});
