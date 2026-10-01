<?php

/*
|--------------------------------------------------------------------------
| LOGIN SECURITY
|--------------------------------------------------------------------------
*/

session_start();

if (!isset($_SESSION['username'])) {

    header("Location: login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
|
| db.php handles the TiDB / MySQL PDO connection.
|
*/

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$totalPersonnel = 0;
$totalCourses   = 0;
$totalBranches  = 0;

$personnel = [];

$databaseError = false;
$databaseErrorMessage = "";


/*
|--------------------------------------------------------------------------
| LOAD DASHBOARD DATA
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | TOTAL PERSONNEL
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(*)
        FROM military_personnel
    ");

    $totalPersonnel = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | TOTAL COURSES
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT courses)
        FROM military_personnel
        WHERE courses IS NOT NULL
        AND TRIM(courses) <> ''
    ");

    $totalCourses = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | TOTAL BRANCHES
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT branch_of_service)
        FROM military_personnel
        WHERE branch_of_service IS NOT NULL
        AND TRIM(branch_of_service) <> ''
    ");

    $totalBranches = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | GET PERSONNEL RECORDS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | `rank` is enclosed in backticks because this is TiDB/MySQL syntax.
    |
    */

    $stmt = $connection->query("
        SELECT
            id,
            `rank`,
            name,
            serial_number,
            branch_of_service,
            courses,
            year_graduated,
            standing,
            created_at,
            updated_at
        FROM military_personnel
        ORDER BY id ASC
    ");

    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | DATABASE ERROR
    |--------------------------------------------------------------------------
    |
    | Do not expose database credentials or SQL details.
    |
    */

    $databaseError = true;

    $databaseErrorMessage =
        "Unable to load personnel records from the database.";

    $totalPersonnel = 0;
    $totalCourses   = 0;
    $totalBranches  = 0;

    $personnel = [];

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        CMO Training Squadron - Dashboard
    </title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         DATATABLES
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"
    >


    <!-- =====================================================
         DATATABLE BUTTONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css"
    >


    <!-- =====================================================
         DASHBOARD CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/dashboard.css?v=2"
    >

</head>


<body>


<!-- =========================================================
     MOBILE OVERLAY
========================================================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside
    class="sidebar"
    id="sidebar"
>


    <!-- =====================================================
         SIDEBAR BRAND
    ====================================================== -->

    <div class="sidebar-brand">

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

    </div>


    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <div class="sidebar-section-title">
        NAVIGATION
    </div>


    <nav class="sidebar-nav">


        <!-- DASHBOARD -->

        <a
            href="index.php"
            class="nav-item active"
        >

            <span class="nav-icon">
                <i class="bi bi-grid-1x2-fill"></i>
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- MILITARY PERSONNEL -->

        <a
            href="index.php#personnel"
            class="nav-item"
        >

            <span class="nav-icon">
                <i class="bi bi-person-badge-fill"></i>
            </span>

            <span>
                Military Personnel
            </span>

        </a>


        <!-- STATISTICS -->

        <a
            href="statistics.php"
            class="nav-item"
        >

            <span class="nav-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </span>

            <span>
                Statistics
            </span>

        </a>


        <!-- COPY FILE -->

        <div class="nav-dropdown">

            <button
                type="button"
                class="nav-item nav-dropdown-toggle"
                id="copyFileToggle"
            >

                <span class="nav-icon">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                </span>

                <span>
                    Copy File
                </span>

                <i class="bi bi-chevron-down nav-chevron"></i>

            </button>


            <div
                class="nav-submenu"
                id="copyFileMenu"
            >

                <a
                    href="#"
                    id="exportExcel"
                >

                    <i class="bi bi-file-earmark-excel"></i>

                    Export to Excel

                </a>


                <a
                    href="#"
                    id="exportPDF"
                >

                    <i class="bi bi-file-earmark-pdf"></i>

                    Export to PDF

                </a>


                <a
                    href="#"
                    id="exportPrint"
                >

                    <i class="bi bi-printer"></i>

                    Print Table

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


    <nav class="sidebar-nav">


        <!-- ADD PERSONNEL -->

        <a
            href="create.php"
            class="nav-item add-personnel-nav"
        >

            <span class="nav-icon">
                <i class="bi bi-person-plus-fill"></i>
            </span>

            <span>
                Add New Personnel
            </span>

        </a>


        <!-- CHANGE PASSWORD -->

        <a
            href="change_password.php"
            class="nav-item"
        >

            <span class="nav-icon">
                <i class="bi bi-key-fill"></i>
            </span>

            <span>
                Change Password
            </span>

        </a>

    </nav>


    <!-- =====================================================
         SIDEBAR BOTTOM
    ====================================================== -->

    <div class="sidebar-bottom">


        <div class="system-status">

            <span class="status-dot"></span>

            System Online

        </div>


        <a
            href="logout.php"
            class="logout-nav"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</aside>


<!-- =========================================================
     MAIN WRAPPER
========================================================= -->

<div class="main-wrapper">


    <!-- =====================================================
         TOP HEADER
    ====================================================== -->

    <header class="topbar">


        <!-- MOBILE MENU -->

        <button
            type="button"
            class="mobile-menu-btn"
            id="mobileMenuBtn"
        >

            <i class="bi bi-list"></i>

        </button>


        <!-- HEADER TITLE -->

        <div class="topbar-title">

            <span>
                CMO Training Squadron
            </span>

            <small>
                Student Database Information System
            </small>

        </div>


        <!-- HEADER RIGHT -->

        <div class="topbar-right">


            <!-- NOTIFICATION -->

            <button
                type="button"
                class="header-icon-btn"
                title="Notifications"
            >

                <i class="bi bi-bell"></i>

            </button>


            <!-- USER -->

            <div class="user-menu">


                <div class="user-avatar">

                    <i class="bi bi-person-fill"></i>

                </div>


                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION['username'],
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


    <!-- =====================================================
         PAGE CONTENT
    ====================================================== -->

    <main class="page-content">


        <!-- =================================================
             WELCOME
        ================================================== -->

        <div class="page-heading">


            <div>

                <div class="page-label">
                    CMO INFORMATION SYSTEM
                </div>


                <h1>
                    Dashboard
                </h1>


                <p>

                    Welcome back,

                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION['username'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </strong>.

                    Here's an overview of the personnel database.

                </p>

            </div>


            <a
                href="create.php"
                class="add-button"
            >

                <i class="bi bi-plus-lg"></i>

                Add New Personnel

            </a>

        </div>


        <!-- =================================================
             SUCCESS MESSAGES
        ================================================== -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success-message">

                <i class="bi bi-check-circle-fill"></i>

                Record deleted successfully.

            </div>

        <?php endif; ?>


        <?php if (isset($_GET['updated'])): ?>

            <div class="success-message">

                <i class="bi bi-check-circle-fill"></i>

                Record updated successfully.

            </div>

        <?php endif; ?>


        <?php if (isset($_GET['created'])): ?>

            <div class="success-message">

                <i class="bi bi-check-circle-fill"></i>

                Personnel record added successfully.

            </div>

        <?php endif; ?>


        <!-- =================================================
             DATABASE ERROR
        ================================================== -->

        <?php if ($databaseError): ?>

            <div
                class="alert alert-danger"
                role="alert"
            >

                <i class="bi bi-exclamation-triangle-fill"></i>

                <?= htmlspecialchars(
                    $databaseErrorMessage,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             STATISTICS CARDS
        ================================================== -->

        <section class="stats-grid">


            <!-- TOTAL PERSONNEL -->

            <div class="stat-card">


                <div class="stat-icon blue">

                    <i class="bi bi-people-fill"></i>

                </div>


                <div class="stat-content">

                    <span>
                        TOTAL PERSONNEL
                    </span>

                    <strong>
                        <?= number_format($totalPersonnel); ?>
                    </strong>

                    <small>
                        Registered personnel
                    </small>

                </div>

            </div>


            <!-- TOTAL COURSES -->

            <div class="stat-card">


                <div class="stat-icon cyan">

                    <i class="bi bi-mortarboard-fill"></i>

                </div>


                <div class="stat-content">

                    <span>
                        COURSES
                    </span>

                    <strong>
                        <?= number_format($totalCourses); ?>
                    </strong>

                    <small>
                        Registered courses
                    </small>

                </div>

            </div>


            <!-- BRANCHES -->

            <div class="stat-card">


                <div class="stat-icon purple">

                    <i class="bi bi-diagram-3-fill"></i>

                </div>


                <div class="stat-content">

                    <span>
                        BRANCHES
                    </span>

                    <strong>
                        <?= number_format($totalBranches); ?>
                    </strong>

                    <small>
                        Branches represented
                    </small>

                </div>

            </div>

        </section>


        <!-- =================================================
             PERSONNEL SECTION
        ================================================== -->

        <section
            class="content-card"
            id="personnel"
        >


            <!-- CARD HEADER -->

            <div class="content-card-header">


                <div>

                    <div class="section-label">
                        DATABASE RECORDS
                    </div>

                    <h2>
                        Military Personnel
                    </h2>

                    <p>
                        View and manage registered military personnel information.
                    </p>

                </div>


                <a
                    href="create.php"
                    class="secondary-add-button"
                >

                    <i class="bi bi-person-plus-fill"></i>

                    Add Personnel

                </a>

            </div>


            <!-- =================================================
                 TABLE
            ================================================== -->

            <div class="table-container">


                <table
                    id="personnelTable"
                    class="table personnel-table"
                >


                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Rank</th>

                            <th>Name</th>

                            <th>Serial Number</th>

                            <th>Branch of Service</th>

                            <th>Courses</th>

                            <th>Year Graduated</th>

                            <th>Standing</th>

                            <th>Created At</th>

                            <th>Updated At</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($personnel as $row): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="id-badge">

                                    <?= htmlspecialchars(
                                        (string) $row['id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </span>

                            </td>


                            <!-- RANK -->

                            <td>

                                <span class="rank-text">

                                    <?= htmlspecialchars(
                                        $row['rank'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </span>

                            </td>


                            <!-- NAME -->

                            <td class="person-name">

                                <?= htmlspecialchars(
                                    $row['name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <!-- SERIAL NUMBER -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['serial_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <!-- BRANCH -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['branch_of_service'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <!-- COURSES -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['courses'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <!-- YEAR GRADUATED -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['year_graduated'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </td>


                            <!-- STANDING -->

                            <td>

                                <?php

                                $standing =
                                    trim(
                                        $row['standing'] ?? ''
                                    );

                                $standingClass =
                                    strtolower(
                                        preg_replace(
                                            '/[^a-zA-Z0-9]+/',
                                            '-',
                                            $standing
                                        )
                                    );

                                ?>


                                <span
                                    class="standing-badge <?= htmlspecialchars(
                                        $standingClass,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>"
                                >

                                    <?= htmlspecialchars(
                                        $standing,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </span>

                            </td>


                            <!-- CREATED -->

                            <td class="date-cell">

                                <?php

                                if (
                                    !empty(
                                        $row['created_at']
                                    )
                                ) {

                                    $createdTimestamp =
                                        strtotime(
                                            $row['created_at']
                                        );

                                    if (
                                        $createdTimestamp !== false
                                    ) {

                                        echo htmlspecialchars(
                                            date(
                                                'M d, Y',
                                                $createdTimestamp
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    }

                                }

                                ?>

                            </td>


                            <!-- UPDATED -->

                            <td class="date-cell">

                                <?php

                                if (
                                    !empty(
                                        $row['updated_at']
                                    )
                                ) {

                                    $updatedTimestamp =
                                        strtotime(
                                            $row['updated_at']
                                        );

                                    if (
                                        $updatedTimestamp !== false
                                    ) {

                                        echo htmlspecialchars(
                                            date(
                                                'M d, Y',
                                                $updatedTimestamp
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    }

                                }

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="action-buttons">


                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?= urlencode(
                                            $row['id']
                                        ); ?>"
                                        class="action-btn edit"
                                        title="Edit"
                                    >

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <!-- DELETE -->

                                    <button
                                        type="button"
                                        class="action-btn delete btn-delete"
                                        data-id="<?= htmlspecialchars(
                                            (string) $row['id'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>"
                                        data-name="<?= htmlspecialchars(
                                            $row['name'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>"
                                        title="Delete"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>


                    <?php endforeach; ?>


                    <?php if (empty($personnel) && !$databaseError): ?>

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-4"
                            >

                                No personnel records found.

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<!-- =========================================================
     DELETE MODAL
========================================================= -->

<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
    aria-hidden="true"
>


    <div class="modal-dialog modal-dialog-centered">


        <div class="modal-content delete-modal">


            <div class="modal-header">


                <h5 class="modal-title">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    Confirm Deletion

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">

                <p>
                    Are you sure you want to delete this personnel record?
                </p>


                <strong id="deletePersonName"></strong>

            </div>


            <div class="modal-footer">


                <button
                    type="button"
                    class="modal-cancel"
                    data-bs-dismiss="modal"
                >

                    Cancel

                </button>


                <a
                    id="confirmDeleteBtn"
                    class="modal-delete"
                >

                    Delete Record

                </a>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT LIBRARIES
========================================================= -->

<!-- jQuery -->

<script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
></script>


<!-- Bootstrap -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =========================================================
     DATATABLES
========================================================= -->

<script
    src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"
></script>

<script
    src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"
></script>


<!-- =========================================================
     DATATABLE BUTTONS
========================================================= -->

<script
    src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"
></script>

<script
    src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"
></script>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"
></script>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
></script>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
></script>

<script
    src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"
></script>

<script
    src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"
></script>


<!-- =========================================================
     CUSTOM DASHBOARD JAVASCRIPT
========================================================= -->

<script
    src="js/dashboard.js"
></script>


</body>

</html>
