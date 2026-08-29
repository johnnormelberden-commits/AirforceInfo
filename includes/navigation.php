<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>

<!-- ==========================================================
     CMO / PAF MAIN NAVIGATION
     ========================================================== -->

<header class="main-header">

    <!-- BRAND -->
    <div class="header-brand">

        <div class="header-logo">

            <img
                src="cmo1.png"
                alt="CMO Training Squadron"
            >

        </div>

        <div class="header-brand-text">

            <div class="header-title">
                CMO Training Squadron
            </div>

            <div class="header-subtitle">
                Personnel Information System
            </div>

        </div>

    </div>


    <!-- NAVIGATION -->
    <nav class="main-navigation">

        <a
            href="index.php"
            class="nav-link <?= $currentPage === 'index.php' ? 'active' : ''; ?>"
        >

            <i class="bi bi-grid-1x2-fill"></i>

            <span>Dashboard</span>

        </a>


        <a
            href="index.php"
            class="nav-link"
        >

            <i class="bi bi-person-badge-fill"></i>

            <span>Military Personnel</span>

        </a>


        <a
            href="statistics.php"
            class="nav-link <?= $currentPage === 'statistics.php' ? 'active' : ''; ?>"
        >

            <i class="bi bi-bar-chart-fill"></i>

            <span>Statistics</span>

        </a>


        <div class="nav-dropdown">

            <button
                type="button"
                class="nav-link nav-dropdown-button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >

                <i class="bi bi-file-earmark-arrow-down-fill"></i>

                <span>Copy File</span>

                <i class="bi bi-chevron-down dropdown-arrow"></i>

            </button>


            <ul class="dropdown-menu">

                <li>

                    <a
                        href="#"
                        id="exportExcel"
                        class="dropdown-item"
                    >

                        <i class="bi bi-file-earmark-excel"></i>

                        Export to Excel

                    </a>

                </li>


                <li>

                    <a
                        href="#"
                        id="exportPDF"
                        class="dropdown-item"
                    >

                        <i class="bi bi-file-earmark-pdf"></i>

                        Export to PDF

                    </a>

                </li>


                <li>

                    <a
                        href="#"
                        id="exportPrint"
                        class="dropdown-item"
                    >

                        <i class="bi bi-printer"></i>

                        Print Table

                    </a>

                </li>

            </ul>

        </div>


        <a
            href="create.php"
            class="nav-link <?= $currentPage === 'create.php' ? 'active' : ''; ?>"
        >

            <i class="bi bi-person-plus-fill"></i>

            <span>Add Personnel</span>

        </a>


        <a
            href="change_password.php"
            class="nav-link <?= $currentPage === 'change_password.php' ? 'active' : ''; ?>"
        >

            <i class="bi bi-key-fill"></i>

            <span>Change Password</span>

        </a>

    </nav>


    <!-- USER / LOGOUT -->
    <div class="header-user">

        <div class="user-info">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div class="user-details">

                <strong>
                    <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </strong>

                <small>
                    Authorized Personnel
                </small>

            </div>

        </div>


        <a
            href="logout.php"
            class="logout-button"
            title="Logout"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>Logout</span>

        </a>

    </div>

</header>