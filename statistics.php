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
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$totalPersonnel = 0;
$totalRanks = 0;
$totalBranches = 0;
$totalCourses = 0;

$rankStatistics = [];
$branchStatistics = [];
$standingStatistics = [];
$courseStatistics = [];
$yearStatistics = [];

$databaseError = false;


/*
|--------------------------------------------------------------------------
| GET STATISTICS
|--------------------------------------------------------------------------
*/

try {

    /*
     * TOTAL PERSONNEL
     */

    $stmt = $connection->query("
        SELECT COUNT(*)
        FROM military_personnel
    ");

    $totalPersonnel = (int) $stmt->fetchColumn();


    /*
     * TOTAL RANKS
     */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT NULLIF(TRIM(rank), ''))
        FROM military_personnel
    ");

    $totalRanks = (int) $stmt->fetchColumn();


    /*
     * TOTAL BRANCHES
     */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT NULLIF(TRIM(branch_of_service), ''))
        FROM military_personnel
    ");

    $totalBranches = (int) $stmt->fetchColumn();


    /*
     * TOTAL COURSES
     */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT NULLIF(TRIM(courses), ''))
        FROM military_personnel
    ");

    $totalCourses = (int) $stmt->fetchColumn();


    /*
     * PERSONNEL BY RANK
     */

    $stmt = $connection->query("
        SELECT
            COALESCE(
                NULLIF(TRIM(rank), ''),
                'Not Specified'
            ) AS label,
            COUNT(*) AS total
        FROM military_personnel
        GROUP BY label
        ORDER BY total DESC
    ");

    $rankStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * PERSONNEL BY BRANCH
     */

    $stmt = $connection->query("
        SELECT
            COALESCE(
                NULLIF(TRIM(branch_of_service), ''),
                'Not Specified'
            ) AS label,
            COUNT(*) AS total
        FROM military_personnel
        GROUP BY label
        ORDER BY total DESC
    ");

    $branchStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * PERSONNEL BY STANDING
     */

    $stmt = $connection->query("
        SELECT
            COALESCE(
                NULLIF(TRIM(standing), ''),
                'Not Specified'
            ) AS label,
            COUNT(*) AS total
        FROM military_personnel
        GROUP BY label
        ORDER BY total DESC
    ");

    $standingStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * PERSONNEL BY COURSE
     */

    $stmt = $connection->query("
        SELECT
            COALESCE(
                NULLIF(TRIM(courses), ''),
                'Not Specified'
            ) AS label,
            COUNT(*) AS total
        FROM military_personnel
        GROUP BY label
        ORDER BY total DESC
    ");

    $courseStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * PERSONNEL BY YEAR
     */

    $stmt = $connection->query("
        SELECT
            COALESCE(
                CAST(year_graduated AS TEXT),
                'Not Specified'
            ) AS label,
            COUNT(*) AS total
        FROM military_personnel
        GROUP BY label
        ORDER BY
            CASE
                WHEN label = 'Not Specified' THEN 1
                ELSE 0
            END,
            label ASC
    ");

    $yearStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $databaseError = true;

}


/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

$rankLabels = [];
$rankData = [];

foreach ($rankStatistics as $row) {

    $rankLabels[] = $row['label'];
    $rankData[] = (int) $row['total'];

}


$branchLabels = [];
$branchData = [];

foreach ($branchStatistics as $row) {

    $branchLabels[] = $row['label'];
    $branchData[] = (int) $row['total'];

}


$standingLabels = [];
$standingData = [];

foreach ($standingStatistics as $row) {

    $standingLabels[] = $row['label'];
    $standingData[] = (int) $row['total'];

}


$courseLabels = [];
$courseData = [];

foreach ($courseStatistics as $row) {

    $courseLabels[] = $row['label'];
    $courseData[] = (int) $row['total'];

}


$yearLabels = [];
$yearData = [];

foreach ($yearStatistics as $row) {

    $yearLabels[] = $row['label'];
    $yearData[] = (int) $row['total'];

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
        Statistics - CMO Training Squadron
    </title>


    <!-- Bootstrap -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- Shared Dashboard CSS -->

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >


    <!-- Statistics CSS -->

    <link
        rel="stylesheet"
        href="css/statistics.css"
    >


    <!-- Chart.js -->

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>

</head>


<body>


<!-- =========================================================
     MOBILE OVERLAY
========================================================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
>
</div>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside
    class="sidebar"
    id="sidebar"
>


    <!-- BRAND -->

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


    <!-- NAVIGATION -->

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
            class="nav-item active"
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
                    href="index.php"
                >

                    <i class="bi bi-table"></i>

                    Go to Personnel Table

                </a>

            </div>

        </div>

    </nav>


    <!-- MANAGEMENT -->

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


    <!-- SIDEBAR BOTTOM -->

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


        <!-- USER -->

        <div class="topbar-right">


            <button
                type="button"
                class="header-icon-btn"
                title="Statistics"
            >

                <i class="bi bi-bar-chart"></i>

            </button>


            <div class="user-menu">


                <div class="user-avatar">

                    <i class="bi bi-person-fill"></i>

                </div>


                <div class="user-info">

                    <strong>

                        <?= htmlspecialchars(
                            $_SESSION['username']
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

    <main class="page-content statistics-content">


        <!-- =================================================
             PAGE HEADING
        ================================================== -->

        <div class="statistics-heading">


            <div>

                <div class="page-label">
                    CMO INFORMATION SYSTEM
                </div>


                <h1>
                    Personnel Statistics
                </h1>


                <p>
                    Overview and analysis of military personnel
                    information in the database.
                </p>

            </div>


            <div class="statistics-date">

                <i class="bi bi-bar-chart-line-fill"></i>

                Database Overview

            </div>


        </div>


        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if ($databaseError): ?>

            <div class="statistics-error">

                <i class="bi bi-exclamation-triangle-fill"></i>

                Unable to load personnel statistics.

            </div>

        <?php endif; ?>


        <!-- =================================================
             SUMMARY CARDS
        ================================================== -->

        <section class="statistics-summary">


            <!-- TOTAL PERSONNEL -->

            <div class="statistics-card blue-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-people-fill"></i>

                </div>


                <div class="statistics-card-content">

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


            <!-- RANKS -->

            <div class="statistics-card cyan-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-person-badge-fill"></i>

                </div>


                <div class="statistics-card-content">

                    <span>
                        RANKS
                    </span>

                    <strong>
                        <?= number_format($totalRanks); ?>
                    </strong>

                    <small>
                        Different ranks
                    </small>

                </div>

            </div>


            <!-- BRANCHES -->

            <div class="statistics-card purple-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-diagram-3-fill"></i>

                </div>


                <div class="statistics-card-content">

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


            <!-- COURSES -->

            <div class="statistics-card green-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-mortarboard-fill"></i>

                </div>


                <div class="statistics-card-content">

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


        </section>


        <!-- =================================================
             CHART GRID
        ================================================== -->

        <section class="statistics-grid">


            <!-- RANK -->

            <div class="chart-panel">


                <div class="chart-panel-header">

                    <div>

                        <span class="chart-label">
                            PERSONNEL DISTRIBUTION
                        </span>

                        <h2>
                            Personnel by Rank
                        </h2>

                    </div>


                    <div class="chart-icon">

                        <i class="bi bi-bar-chart-fill"></i>

                    </div>

                </div>


                <div class="chart-wrapper">

                    <canvas id="rankChart"></canvas>

                </div>

            </div>


            <!-- BRANCH -->

            <div class="chart-panel">


                <div class="chart-panel-header">

                    <div>

                        <span class="chart-label">
                            SERVICE DISTRIBUTION
                        </span>

                        <h2>
                            Branch of Service
                        </h2>

                    </div>


                    <div class="chart-icon">

                        <i class="bi bi-diagram-3-fill"></i>

                    </div>

                </div>


                <div class="chart-wrapper doughnut-wrapper">

                    <canvas id="branchChart"></canvas>

                </div>

            </div>


            <!-- STANDING -->

            <div class="chart-panel">


                <div class="chart-panel-header">

                    <div>

                        <span class="chart-label">
                            PERFORMANCE
                        </span>

                        <h2>
                            Personnel by Standing
                        </h2>

                    </div>


                    <div class="chart-icon">

                        <i class="bi bi-award-fill"></i>

                    </div>

                </div>


                <div class="chart-wrapper doughnut-wrapper">

                    <canvas id="standingChart"></canvas>

                </div>

            </div>


            <!-- YEAR -->

            <div class="chart-panel">


                <div class="chart-panel-header">

                    <div>

                        <span class="chart-label">
                            GRADUATION HISTORY
                        </span>

                        <h2>
                            Year Graduated
                        </h2>

                    </div>


                    <div class="chart-icon">

                        <i class="bi bi-calendar3"></i>

                    </div>

                </div>


                <div class="chart-wrapper">

                    <canvas id="yearChart"></canvas>

                </div>

            </div>


            <!-- COURSE -->

            <div class="chart-panel chart-panel-wide">


                <div class="chart-panel-header">

                    <div>

                        <span class="chart-label">
                            TRAINING DISTRIBUTION
                        </span>

                        <h2>
                            Personnel by Course
                        </h2>

                    </div>


                    <div class="chart-icon">

                        <i class="bi bi-mortarboard-fill"></i>

                    </div>

                </div>


                <div class="chart-wrapper course-chart-wrapper">

                    <canvas id="courseChart"></canvas>

                </div>

            </div>


        </section>


    </main>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| PHP DATA
|--------------------------------------------------------------------------
*/

const rankLabels =
    <?= json_encode($rankLabels); ?>;

const rankData =
    <?= json_encode($rankData); ?>;


const branchLabels =
    <?= json_encode($branchLabels); ?>;

const branchData =
    <?= json_encode($branchData); ?>;


const standingLabels =
    <?= json_encode($standingLabels); ?>;

const standingData =
    <?= json_encode($standingData); ?>;


const courseLabels =
    <?= json_encode($courseLabels); ?>;

const courseData =
    <?= json_encode($courseData); ?>;


const yearLabels =
    <?= json_encode($yearLabels); ?>;

const yearData =
    <?= json_encode($yearData); ?>;


/*
|--------------------------------------------------------------------------
| COLORS
|--------------------------------------------------------------------------
*/

const colors = [

    '#2d7fc1',
    '#42a5d5',
    '#6c8fd8',
    '#7c6dcc',
    '#3baf86',
    '#e2a83b',
    '#d46b6b',
    '#527fa8',
    '#365f86',
    '#86a9c7'

];


/*
|--------------------------------------------------------------------------
| DEFAULT CHART SETTINGS
|--------------------------------------------------------------------------
*/

Chart.defaults.font.family =
    'Inter, Arial, sans-serif';

Chart.defaults.color =
    '#728096';

Chart.defaults.font.size = 11;


/*
|--------------------------------------------------------------------------
| RANK CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('rankChart'),

    {

        type: 'bar',

        data: {

            labels: rankLabels,

            datasets: [{

                data: rankData,

                backgroundColor: '#2d7fc1',

                borderRadius: 5,

                borderSkipped: false,

                maxBarThickness: 42

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {
                        precision: 0
                    },

                    grid: {
                        color: '#e9eef4'
                    }

                },

                x: {

                    grid: {
                        display: false
                    }

                }

            }

        }

    }

);


/*
|--------------------------------------------------------------------------
| BRANCH CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('branchChart'),

    {

        type: 'doughnut',

        data: {

            labels: branchLabels,

            datasets: [{

                data: branchData,

                backgroundColor: colors,

                borderColor: '#ffffff',

                borderWidth: 3

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '66%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        padding: 16,

                        usePointStyle: true,

                        boxWidth: 8

                    }

                }

            }

        }

    }

);


/*
|--------------------------------------------------------------------------
| STANDING CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('standingChart'),

    {

        type: 'doughnut',

        data: {

            labels: standingLabels,

            datasets: [{

                data: standingData,

                backgroundColor: [

                    '#2d7fc1',
                    '#3baf86',
                    '#e2a83b',
                    '#d46b6b',
                    '#7c6dcc',
                    '#527fa8'

                ],

                borderColor: '#ffffff',

                borderWidth: 3

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '66%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        padding: 16,

                        usePointStyle: true,

                        boxWidth: 8

                    }

                }

            }

        }

    }

);


/*
|--------------------------------------------------------------------------
| YEAR CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('yearChart'),

    {

        type: 'line',

        data: {

            labels: yearLabels,

            datasets: [{

                data: yearData,

                borderColor: '#2d7fc1',

                backgroundColor:
                    'rgba(45, 127, 193, 0.10)',

                borderWidth: 3,

                fill: true,

                tension: 0.35,

                pointRadius: 4,

                pointHoverRadius: 6,

                pointBackgroundColor: '#2d7fc1',

                pointBorderColor: '#ffffff',

                pointBorderWidth: 2

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {
                        precision: 0
                    },

                    grid: {
                        color: '#e9eef4'
                    }

                },

                x: {

                    grid: {
                        display: false
                    }

                }

            }

        }

    }

);


/*
|--------------------------------------------------------------------------
| COURSE CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('courseChart'),

    {

        type: 'bar',

        data: {

            labels: courseLabels,

            datasets: [{

                data: courseData,

                backgroundColor: colors,

                borderRadius: 5,

                borderSkipped: false,

                maxBarThickness: 32

            }]

        },

        options: {

            indexAxis: 'y',

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                }

            },

            scales: {

                x: {

                    beginAtZero: true,

                    ticks: {
                        precision: 0
                    },

                    grid: {
                        color: '#e9eef4'
                    }

                },

                y: {

                    grid: {
                        display: false
                    }

                }

            }

        }

    }

);


/*
|--------------------------------------------------------------------------
| COPY FILE MENU
|--------------------------------------------------------------------------
*/

document
    .getElementById('copyFileToggle')
    .addEventListener('click', function () {

        document
            .getElementById('copyFileMenu')
            .classList.toggle('show');

        this.classList.toggle('open');

    });


/*
|--------------------------------------------------------------------------
| MOBILE SIDEBAR
|--------------------------------------------------------------------------
*/

document
    .getElementById('mobileMenuBtn')
    .addEventListener('click', function () {

        document
            .getElementById('sidebar')
            .classList.toggle('show');

        document
            .getElementById('sidebarOverlay')
            .classList.toggle('show');

    });


document
    .getElementById('sidebarOverlay')
    .addEventListener('click', function () {

        document
            .getElementById('sidebar')
            .classList.remove('show');

        this.classList.remove('show');

    });

</script>


</body>

</html>