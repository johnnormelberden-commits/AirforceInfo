<?php
/*
|--------------------------------------------------------------------------
| SHARED SIDEBAR
| CMO TRAINING SQUADRON
|--------------------------------------------------------------------------
|
| This sidebar is included by every system page.
|
| Example:
|
| require_once 'includes/sidebar.php';
|
| or, depending on the page location:
|
| require_once __DIR__ . '/includes/sidebar.php';
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| DETERMINE CURRENT PAGE
|--------------------------------------------------------------------------
*/

$currentPage = basename($_SERVER['PHP_SELF']);


/*
|--------------------------------------------------------------------------
| SIDEBAR ACTIVE STATE
|--------------------------------------------------------------------------
*/

$isDashboard = (
    $currentPage === 'index.php'
);

$isStatistics = (
    $currentPage === 'statistics.php'
);

$isCreate = (
    $currentPage === 'create.php'
);

$isChangePassword = (
    $currentPage === 'change_password.php'
);


/*
|--------------------------------------------------------------------------
| COPY FILE MENU STATE
|--------------------------------------------------------------------------
|
| If you later add dedicated copy-file pages, add them here.
|
*/

$copyFilePages = [
    'copy_file.php',
    'copy.php'
];

$isCopyFile = in_array(
    $currentPage,
    $copyFilePages,
    true
);

?>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside
    class="sidebar"
    id="sidebar"
>


    <!-- =====================================================
         BRAND
    ====================================================== -->

    <div class="sidebar-brand">

        <a
            href="index.php"
            class="sidebar-brand-link"
            aria-label="CMO Training Squadron Dashboard"
        >

            <div class="sidebar-logo">

                <img
                    src="cmo1.png"
                    alt="CMO Training Squadron"
                >

            </div>


            <div class="sidebar-brand-text">

                <strong>
                    CMO TRAINING
                </strong>

                <span>
                    SQUADRON
                </span>

            </div>

        </a>

    </div>


    <!-- =====================================================
         NAVIGATION TITLE
    ====================================================== -->

    <div class="sidebar-section-title">

        NAVIGATION

    </div>


    <!-- =====================================================
         MAIN NAVIGATION
    ====================================================== -->

    <nav
        class="sidebar-nav"
        aria-label="Main navigation"
    >


        <!-- =================================================
             DASHBOARD
        ================================================== -->

        <a
            href="index.php"
            class="nav-item <?= $isDashboard ? 'active' : ''; ?>"
        >

            <span class="nav-icon">

                <i class="bi bi-grid-1x2-fill"></i>

            </span>

            <span class="nav-text">
                Dashboard
            </span>

        </a>


        <!-- =================================================
             MILITARY PERSONNEL
        ================================================== -->

        <a
            href="index.php#personnel"
            class="nav-item <?= $isDashboard ? 'personnel-nav-active' : ''; ?>"
        >

            <span class="nav-icon">

                <i class="bi bi-person-badge-fill"></i>

            </span>

            <span class="nav-text">
                Military Personnel
            </span>

        </a>


        <!-- =================================================
             STATISTICS
        ================================================== -->

        <a
            href="statistics.php"
            class="nav-item <?= $isStatistics ? 'active' : ''; ?>"
        >

            <span class="nav-icon">

                <i class="bi bi-bar-chart-fill"></i>

            </span>

            <span class="nav-text">
                Statistics
            </span>

        </a>


        <!-- =================================================
             COPY FILE DROPDOWN
        ================================================== -->

        <div
            class="nav-dropdown <?= $isCopyFile ? 'active-dropdown' : ''; ?>"
        >


            <button
                type="button"
                class="nav-item nav-dropdown-toggle <?= $isCopyFile ? 'open' : ''; ?>"
                id="copyFileToggle"
                aria-expanded="<?= $isCopyFile ? 'true' : 'false'; ?>"
                aria-controls="copyFileMenu"
            >

                <span class="nav-icon">

                    <i class="bi bi-file-earmark-arrow-down-fill"></i>

                </span>

                <span class="nav-text">
                    Copy File
                </span>

                <i
                    class="bi bi-chevron-down nav-chevron"
                ></i>

            </button>


            <div
                class="nav-submenu <?= $isCopyFile ? 'show' : ''; ?>"
                id="copyFileMenu"
            >

                <a
                    href="index.php"
                >

                    <i class="bi bi-table"></i>

                    <span>
                        Go to Personnel Table
                    </span>

                </a>

            </div>

        </div>


    </nav>


    <!-- =====================================================
         MANAGEMENT
    ====================================================== -->

    <div class="sidebar-section-title management-title">

        MANAGEMENT

    </div>


    <nav
        class="sidebar-nav"
        aria-label="Management navigation"
    >


        <!-- =================================================
             ADD PERSONNEL
        ================================================== -->

        <a
            href="create.php"
            class="nav-item add-personnel-nav <?= $isCreate ? 'active' : ''; ?>"
        >

            <span class="nav-icon">

                <i class="bi bi-person-plus-fill"></i>

            </span>

            <span class="nav-text">
                Add New Personnel
            </span>

        </a>


        <!-- =================================================
             CHANGE PASSWORD
        ================================================== -->

        <a
            href="change_password.php"
            class="nav-item <?= $isChangePassword ? 'active' : ''; ?>"
        >

            <span class="nav-icon">

                <i class="bi bi-key-fill"></i>

            </span>

            <span class="nav-text">
                Change Password
            </span>

        </a>


    </nav>


    <!-- =====================================================
         SIDEBAR BOTTOM
    ====================================================== -->

    <div class="sidebar-bottom">


        <!-- SYSTEM STATUS -->

        <div class="system-status">

            <span
                class="status-dot"
                aria-hidden="true"
            ></span>

            <span>
                System Online
            </span>

        </div>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="logout-nav"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>


    </div>


</aside>


<!-- =========================================================
     MOBILE SIDEBAR OVERLAY
========================================================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
    aria-hidden="true"
></div>
