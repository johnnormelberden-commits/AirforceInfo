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
*/

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$rank              = "";
$name              = "";
$serial_number     = "";
$branch_of_service = "";
$courses           = "";
$year_graduated    = "";
$standing          = "";

$errorMessage = "";


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rank              = trim($_POST['rank'] ?? '');
    $name              = trim($_POST['name'] ?? '');
    $serial_number     = trim($_POST['serial_number'] ?? '');
    $branch_of_service = trim($_POST['branch_of_service'] ?? '');
    $courses           = trim($_POST['courses'] ?? '');
    $year_graduated    = trim($_POST['year_graduated'] ?? '');
    $standing          = trim($_POST['standing'] ?? '');


    /*
     * ==========================================================
     * VALIDATION
     * ==========================================================
     */

    if (
        $rank === '' ||
        $name === '' ||
        $serial_number === '' ||
        $branch_of_service === '' ||
        $courses === '' ||
        $year_graduated === '' ||
        $standing === ''
    ) {

        $errorMessage = "All the fields are required.";

    } else {

        try {

            /*
             * ==================================================
             * INSERT PERSONNEL
             * ==================================================
             */

            $sql = "
                INSERT INTO military_personnel
                (
                    rank,
                    name,
                    serial_number,
                    branch_of_service,
                    courses,
                    year_graduated,
                    standing
                )
                VALUES
                (
                    :rank,
                    :name,
                    :serial_number,
                    :branch_of_service,
                    :courses,
                    :year_graduated,
                    :standing
                )
            ";

            $stmt = $connection->prepare($sql);

            $stmt->execute([
                ':rank'              => $rank,
                ':name'              => $name,
                ':serial_number'     => $serial_number,
                ':branch_of_service' => $branch_of_service,
                ':courses'           => $courses,
                ':year_graduated'    => $year_graduated,
                ':standing'          => $standing
            ]);


            /*
             * ==================================================
             * SUCCESS
             * ==================================================
             */

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            $errorMessage =
                "Unable to save the personnel information.";

        }

    }

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
        Add Military Personnel - CMO Training Squadron
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
         DASHBOARD CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/dashboard.css?v=2"
    >


    <!-- =====================================================
         CREATE PAGE CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/create.css?v=1"
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
            class="nav-item"
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
            class="nav-item add-personnel-nav active"
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
         TOPBAR
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


        <!-- TITLE -->

        <div class="topbar-title">

            <span>
                CMO Training Squadron
            </span>

            <small>
                Student Database Information System
            </small>

        </div>


        <!-- RIGHT SIDE -->

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
                        <?= htmlspecialchars($_SESSION['username']); ?>
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
             PAGE HEADING
        ================================================== -->

        <div class="create-heading">

            <div>

                <div class="page-label">
                    CMO INFORMATION SYSTEM
                </div>


                <h1>
                    Add New Personnel
                </h1>


                <p>
                    Register a new military personnel record
                    in the database.
                </p>

            </div>


            <a
                href="index.php"
                class="create-back-button"
            >

                <i class="bi bi-arrow-left"></i>

                Back to Dashboard

            </a>

        </div>


        <!-- =================================================
             ERROR MESSAGE
        ================================================== -->

        <?php if (!empty($errorMessage)): ?>

            <div
                class="create-error"
                role="alert"
            >

                <i class="bi bi-exclamation-triangle-fill"></i>

                <span>
                    <?= htmlspecialchars($errorMessage); ?>
                </span>

            </div>

        <?php endif; ?>


        <!-- =================================================
             FORM CARD
        ================================================== -->

        <section class="create-card">


            <!-- CARD HEADER -->

            <div class="create-card-header">

                <div>

                    <div class="section-label">
                        PERSONNEL INFORMATION
                    </div>

                    <h2>
                        Military Personnel Details
                    </h2>

                    <p>
                        Enter the required information
                        below to register personnel.
                    </p>

                </div>


                <div class="create-header-icon">

                    <i class="bi bi-person-plus-fill"></i>

                </div>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="post"
                class="personnel-form"
                autocomplete="off"
            >


                <!-- =================================================
                     RANK
                ================================================== -->

                <div class="form-field">

                    <label for="rank">

                        Rank

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-award-fill"></i>

                        <select
                            id="rank"
                            class="form-control"
                            name="rank"
                            required
                        >

                            <option value="">
                                -- Select Rank --
                            </option>

                            <option
                                value="Airman Basic"
                                <?= ($rank === 'Airman Basic') ? 'selected' : ''; ?>
                            >
                                Airman Basic
                            </option>

                            <option
                                value="Airman"
                                <?= ($rank === 'Airman') ? 'selected' : ''; ?>
                            >
                                Airman
                            </option>

                            <option
                                value="Airman First Class"
                                <?= ($rank === 'Airman First Class') ? 'selected' : ''; ?>
                            >
                                Airman First Class
                            </option>

                            <option
                                value="Sergeant"
                                <?= ($rank === 'Sergeant') ? 'selected' : ''; ?>
                            >
                                Sergeant
                            </option>

                            <option
                                value="Technical Sergeant"
                                <?= ($rank === 'Technical Sergeant') ? 'selected' : ''; ?>
                            >
                                Technical Sergeant
                            </option>

                            <option
                                value="Master Sergeant"
                                <?= ($rank === 'Master Sergeant') ? 'selected' : ''; ?>
                            >
                                Master Sergeant
                            </option>

                            <option
                                value="Senior Master Sergeant"
                                <?= ($rank === 'Senior Master Sergeant') ? 'selected' : ''; ?>
                            >
                                Senior Master Sergeant
                            </option>

                            <option
                                value="Chief Master Sergeant"
                                <?= ($rank === 'Chief Master Sergeant') ? 'selected' : ''; ?>
                            >
                                Chief Master Sergeant
                            </option>

                            <option
                                value="Lieutenant"
                                <?= ($rank === 'Lieutenant') ? 'selected' : ''; ?>
                            >
                                Lieutenant
                            </option>

                            <option
                                value="Captain"
                                <?= ($rank === 'Captain') ? 'selected' : ''; ?>
                            >
                                Captain
                            </option>

                            <option
                                value="Major"
                                <?= ($rank === 'Major') ? 'selected' : ''; ?>
                            >
                                Major
                            </option>

                            <option
                                value="Lieutenant Colonel"
                                <?= ($rank === 'Lieutenant Colonel') ? 'selected' : ''; ?>
                            >
                                Lieutenant Colonel
                            </option>

                            <option
                                value="Colonel"
                                <?= ($rank === 'Colonel') ? 'selected' : ''; ?>
                            >
                                Colonel
                            </option>

                            <option
                                value="AW1C"
                                <?= ($rank === 'AW1C') ? 'selected' : ''; ?>
                            >
                                AW1C
                            </option>

                            <option
                                value="A1C"
                                <?= ($rank === 'A1C') ? 'selected' : ''; ?>
                            >
                                A1C
                            </option>

                            <option
                                value="A2C"
                                <?= ($rank === 'A2C') ? 'selected' : ''; ?>
                            >
                                A2C
                            </option>

                            <option
                                value="Staff Sargent"
                                <?= ($rank === 'Staff Sargent') ? 'selected' : ''; ?>
                            >
                                Staff Sargent
                            </option>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     NAME
                ================================================== -->

                <div class="form-field">

                    <label for="name">

                        Name

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-person-fill"></i>

                        <input
                            type="text"
                            id="name"
                            class="form-control"
                            name="name"
                            value="<?= htmlspecialchars($name); ?>"
                            placeholder="Enter full name"
                            required
                        >

                    </div>

                </div>


                <!-- =================================================
                     SERIAL NUMBER
                ================================================== -->

                <div class="form-field">

                    <label for="serial_number">

                        Serial Number

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-upc-scan"></i>

                        <input
                            type="text"
                            id="serial_number"
                            class="form-control"
                            name="serial_number"
                            value="<?= htmlspecialchars($serial_number); ?>"
                            placeholder="Enter serial number"
                            required
                        >

                    </div>

                </div>


                <!-- =================================================
                     BRANCH
                ================================================== -->

                <div class="form-field">

                    <label for="branch_of_service">

                        Branch of Service

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-diagram-3-fill"></i>

                        <select
                            id="branch_of_service"
                            class="form-control"
                            name="branch_of_service"
                            required
                        >

                            <option value="">
                                -- Select Branch --
                            </option>

                            <option
                                value="Philippine Air Force"
                                <?= ($branch_of_service === 'Philippine Air Force') ? 'selected' : ''; ?>
                            >
                                Philippine Air Force
                            </option>

                            <option
                                value="Philippine Army"
                                <?= ($branch_of_service === 'Philippine Army') ? 'selected' : ''; ?>
                            >
                                Philippine Army
                            </option>

                            <option
                                value="Philippine Navy"
                                <?= ($branch_of_service === 'Philippine Navy') ? 'selected' : ''; ?>
                            >
                                Philippine Navy
                            </option>

                            <option
                                value="Reserved Force"
                                <?= ($branch_of_service === 'Reserved Force') ? 'selected' : ''; ?>
                            >
                                Reserved Force
                            </option>

                            <option
                                value="Others"
                                <?= ($branch_of_service === 'Others') ? 'selected' : ''; ?>
                            >
                                Others
                            </option>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     COURSE
                ================================================== -->

                <div class="form-field">

                    <label for="courses">

                        Course/s

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-mortarboard-fill"></i>

                        <input
                            type="text"
                            id="courses"
                            class="form-control"
                            name="courses"
                            value="<?= htmlspecialchars($courses); ?>"
                            placeholder="Enter course or courses"
                            required
                        >

                    </div>

                </div>


                <!-- =================================================
                     YEAR
                ================================================== -->

                <div class="form-field">

                    <label for="year_graduated">

                        Year Graduated

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-calendar-event-fill"></i>

                        <select
                            id="year_graduated"
                            class="form-control"
                            name="year_graduated"
                            required
                        >

                            <option value="">
                                -- Select Year --
                            </option>

                            <?php

                            $currentYear = (int) date('Y');

                            for (
                                $y = $currentYear;
                                $y >= 1960;
                                $y--
                            ):

                            ?>

                                <option
                                    value="<?= $y; ?>"
                                    <?= ((string) $year_graduated === (string) $y) ? 'selected' : ''; ?>
                                >
                                    <?= $y; ?>
                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     STANDING
                ================================================== -->

                <div class="form-field">

                    <label for="standing">

                        Standing

                        <span>*</span>

                    </label>


                    <div class="input-wrapper">

                        <i class="bi bi-trophy-fill"></i>

                        <input
                            type="text"
                            id="standing"
                            class="form-control"
                            name="standing"
                            value="<?= htmlspecialchars($standing); ?>"
                            placeholder="Enter standing"
                            required
                        >

                    </div>

                </div>


                <!-- =================================================
                     FORM ACTIONS
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="index.php"
                        class="form-cancel-button"
                    >

                        <i class="bi bi-x-lg"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="form-submit-button"
                    >

                        <i class="bi bi-check-lg"></i>

                        Save Personnel

                    </button>

                </div>


            </form>

        </section>

    </main>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


<!-- =========================================================
     SHARED DASHBOARD JAVASCRIPT
========================================================= -->

<script src="js/dashboard.js"></script>


</body>

</html>
