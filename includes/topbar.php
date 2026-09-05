<?php
/*
|--------------------------------------------------------------------------
| SHARED TOPBAR
| CMO TRAINING SQUADRON
|--------------------------------------------------------------------------
|
| This topbar is included by all pages of the system.
|
| Example:
|
| require_once __DIR__ . '/includes/topbar.php';
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| CURRENT PAGE
|--------------------------------------------------------------------------
*/

$currentPage = basename($_SERVER['PHP_SELF']);


/*
|--------------------------------------------------------------------------
| PAGE TITLES
|--------------------------------------------------------------------------
|
| These can be changed later if you want the topbar title to
| automatically reflect the current system page.
|
*/

$pageTitles = [

    'index.php' => 'Dashboard',

    'statistics.php' => 'Statistics',

    'create.php' => 'Add New Personnel',

    'change_password.php' => 'Change Password'

];


/*
|--------------------------------------------------------------------------
| CURRENT PAGE TITLE
|--------------------------------------------------------------------------
*/

$currentPageTitle = $pageTitles[$currentPage]
    ?? 'CMO Training Squadron';


/*
|--------------------------------------------------------------------------
| LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$topbarUsername = $_SESSION['username']
    ?? 'Administrator';

?>

<!-- =========================================================
     TOPBAR
========================================================= -->

<header
    class="topbar"
    id="topbar"
>


    <!-- =====================================================
         LEFT SIDE
    ====================================================== -->

    <div class="topbar-left">


        <!-- MOBILE MENU BUTTON -->

        <button
            type="button"
            class="mobile-menu-btn"
            id="mobileMenuBtn"
            aria-label="Open navigation menu"
            aria-controls="sidebar"
            aria-expanded="false"
        >

            <i class="bi bi-list"></i>

        </button>


        <!-- TITLE -->

        <div class="topbar-title">

            <span>
                CMO Training Squadron
            </span>

            <small>
                Student Database Information System
            </small>

        </div>


    </div>


    <!-- =====================================================
         RIGHT SIDE
    ====================================================== -->

    <div class="topbar-right">


        <!-- =================================================
             CURRENT PAGE ICON
        ================================================== -->

        <button
            type="button"
            class="header-icon-btn"
            title="<?= htmlspecialchars($currentPageTitle); ?>"
            aria-label="<?= htmlspecialchars($currentPageTitle); ?>"
        >

            <?php if ($currentPage === 'statistics.php'): ?>

                <i class="bi bi-bar-chart-fill"></i>

            <?php elseif ($currentPage === 'create.php'): ?>

                <i class="bi bi-person-plus-fill"></i>

            <?php elseif ($currentPage === 'change_password.php'): ?>

                <i class="bi bi-key-fill"></i>

            <?php else: ?>

                <i class="bi bi-grid-1x2-fill"></i>

            <?php endif; ?>

        </button>


        <!-- =================================================
             USER
        ================================================== -->

        <div
            class="user-menu"
            id="userMenu"
        >


            <!-- USER AVATAR -->

            <div
                class="user-avatar"
                title="<?= htmlspecialchars($topbarUsername); ?>"
            >

                <i class="bi bi-person-fill"></i>

            </div>


            <!-- USER INFORMATION -->

            <div class="user-info">

                <strong>

                    <?= htmlspecialchars(
                        $topbarUsername,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </strong>

                <span>
                    Administrator
                </span>

            </div>


        </div>


    </div>


</header>
