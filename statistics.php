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
    | TOTAL RANKS
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(DISTINCT NULLIF(TRIM(rank), ''))
        FROM military_personnel
    ");

    $totalRanks = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | TOTAL BRANCHES
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(
            DISTINCT NULLIF(
                TRIM(branch_of_service),
                ''
            )
        )
        FROM military_personnel
    ");

    $totalBranches = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | TOTAL COURSES
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT COUNT(
            DISTINCT NULLIF(
                TRIM(courses),
                ''
            )
        )
        FROM military_personnel
    ");

    $totalCourses = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | PERSONNEL BY RANK
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT

            COALESCE(
                NULLIF(
                    TRIM(rank),
                    ''
                ),
                'Not Specified'
            ) AS label,

            COUNT(*) AS total

        FROM military_personnel

        GROUP BY label

        ORDER BY total DESC
    ");

    $rankStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PERSONNEL BY BRANCH
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT

            COALESCE(
                NULLIF(
                    TRIM(branch_of_service),
                    ''
                ),
                'Not Specified'
            ) AS label,

            COUNT(*) AS total

        FROM military_personnel

        GROUP BY label

        ORDER BY total DESC
    ");

    $branchStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PERSONNEL BY STANDING
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT

            COALESCE(
                NULLIF(
                    TRIM(standing),
                    ''
                ),
                'Not Specified'
            ) AS label,

            COUNT(*) AS total

        FROM military_personnel

        GROUP BY label

        ORDER BY total DESC
    ");

    $standingStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PERSONNEL BY COURSE
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT

            COALESCE(
                NULLIF(
                    TRIM(courses),
                    ''
                ),
                'Not Specified'
            ) AS label,

            COUNT(*) AS total

        FROM military_personnel

        GROUP BY label

        ORDER BY total DESC
    ");

    $courseStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PERSONNEL BY YEAR
    |--------------------------------------------------------------------------
    |
    | Handles:
    | NULL
    | Empty values
    | Whitespace
    |
    |--------------------------------------------------------------------------
    */

    $stmt = $connection->query("
        SELECT

            COALESCE(
                NULLIF(
                    TRIM(
                        CAST(year_graduated AS TEXT)
                    ),
                    ''
                ),
                'Not Specified'
            ) AS label,

            COUNT(*) AS total

        FROM military_personnel

        GROUP BY label

        ORDER BY

            CASE
                WHEN label = 'Not Specified'
                THEN 1
                ELSE 0
            END,

            label ASC
    ");

    $yearStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


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

    $rankLabels[] =
        $row['label'];

    $rankData[] =
        (int) $row['total'];

}


$branchLabels = [];
$branchData = [];

foreach ($branchStatistics as $row) {

    $branchLabels[] =
        $row['label'];

    $branchData[] =
        (int) $row['total'];

}


$standingLabels = [];
$standingData = [];

foreach ($standingStatistics as $row) {

    $standingLabels[] =
        $row['label'];

    $standingData[] =
        (int) $row['total'];

}


$courseLabels = [];
$courseData = [];

foreach ($courseStatistics as $row) {

    $courseLabels[] =
        $row['label'];

    $courseData[] =
        (int) $row['total'];

}


$yearLabels = [];
$yearData = [];

foreach ($yearStatistics as $row) {

    $yearLabels[] =
        $row['label'];

    $yearData[] =
        (int) $row['total'];

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
         SHARED DASHBOARD CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >


    <!-- =====================================================
         STATISTICS CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/statistics.css"
    >


    <!-- =====================================================
         CHART.JS
    ====================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>

</head>


<body>


<!-- =========================================================
     MOBILE SIDEBAR OVERLAY
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
         BRAND
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
         NAVIGATION TITLE
    ====================================================== -->

    <div class="sidebar-section-title">

        NAVIGATION

    </div>


    <!-- =====================================================
         MAIN NAVIGATION
    ====================================================== -->

    <nav class="sidebar-nav">


        <!-- =================================================
             DASHBOARD
        ================================================== -->

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


        <!-- =================================================
             MILITARY PERSONNEL
        ================================================== -->

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


        <!-- =================================================
             STATISTICS
        ================================================== -->

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


        <!-- =================================================
             COPY FILE
        ================================================== -->

        <div class="nav-dropdown">


            <button
                type="button"
                class="nav-item nav-dropdown-toggle"
                id="copyFileToggle"
                aria-expanded="false"
                aria-controls="copyFileMenu"
            >

                <span class="nav-icon">

                    <i class="bi bi-file-earmark-arrow-down-fill"></i>

                </span>

                <span>
                    Copy File
                </span>

                <i
                    class="bi bi-chevron-down nav-chevron"
                ></i>

            </button>


            <div
                class="nav-submenu"
                id="copyFileMenu"
            >

                <a
                    href="index.php#personnel"
                >

                    <i class="bi bi-table"></i>

                    Go to Personnel Table

                </a>

            </div>

        </div>

    </nav>


    <!-- =====================================================
         MANAGEMENT
    ====================================================== -->

    <div
        class="sidebar-section-title management-title"
    >

        MANAGEMENT

    </div>


    <nav class="sidebar-nav">


        <!-- =================================================
             ADD PERSONNEL
        ================================================== -->

        <a
            href="create.php"
            class="nav-item"
        >

            <span class="nav-icon">

                <i class="bi bi-person-plus-fill"></i>

            </span>

            <span>
                Add New Personnel
            </span>

        </a>


        <!-- =================================================
             CHANGE PASSWORD
        ================================================== -->

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


        <!-- SYSTEM STATUS -->

        <div class="system-status">

            <span class="status-dot"></span>

            System Online

        </div>


        <!-- LOGOUT -->

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


        <!-- TOPBAR TITLE -->

        <div class="topbar-title">

            <span>
                CMO Training Squadron
            </span>

            <small>
                Student Database Information System
            </small>

        </div>


        <!-- TOPBAR RIGHT -->

        <div class="topbar-right">


            <!-- CURRENT PAGE ICON -->

            <div
                class="header-icon-btn"
                title="Statistics"
                aria-label="Statistics"
            >

                <i class="bi bi-bar-chart"></i>

            </div>


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

    <main
        class="page-content statistics-content"
        id="statistics"
    >


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
             DATABASE ERROR
        ================================================== -->

        <?php if ($databaseError): ?>

            <div
                class="statistics-error"
                role="alert"
            >

                <i
                    class="bi bi-exclamation-triangle-fill"
                ></i>

                Unable to load personnel statistics.

            </div>

        <?php endif; ?>


        <!-- =================================================
             SUMMARY CARDS
        ================================================== -->

        <section
            class="statistics-summary"
            aria-label="Personnel summary"
        >


            <!-- =================================================
                 TOTAL PERSONNEL
            ================================================== -->

            <div class="statistics-card blue-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-people-fill"></i>

                </div>


                <div class="statistics-card-content">

                    <span>
                        TOTAL PERSONNEL
                    </span>


                    <strong>

                        <?= number_format(
                            $totalPersonnel
                        ); ?>

                    </strong>


                    <small>
                        Registered personnel
                    </small>

                </div>

            </div>


            <!-- =================================================
                 TOTAL RANKS
            ================================================== -->

            <div class="statistics-card cyan-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-person-badge-fill"></i>

                </div>


                <div class="statistics-card-content">

                    <span>
                        RANKS
                    </span>


                    <strong>

                        <?= number_format(
                            $totalRanks
                        ); ?>

                    </strong>


                    <small>
                        Different ranks
                    </small>

                </div>

            </div>


            <!-- =================================================
                 TOTAL BRANCHES
            ================================================== -->

            <div class="statistics-card purple-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-diagram-3-fill"></i>

                </div>


                <div class="statistics-card-content">

                    <span>
                        BRANCHES
                    </span>


                    <strong>

                        <?= number_format(
                            $totalBranches
                        ); ?>

                    </strong>


                    <small>
                        Branches represented
                    </small>

                </div>

            </div>


            <!-- =================================================
                 TOTAL COURSES
            ================================================== -->

            <div class="statistics-card green-card">

                <div class="statistics-card-icon">

                    <i class="bi bi-mortarboard-fill"></i>

                </div>


                <div class="statistics-card-content">

                    <span>
                        COURSES
                    </span>


                    <strong>

                        <?= number_format(
                            $totalCourses
                        ); ?>

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

        <section
            class="statistics-grid"
            aria-label="Personnel statistics charts"
        >


            <!-- =================================================
                 PERSONNEL BY RANK
            ================================================== -->

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

                    <canvas
                        id="rankChart"
                    ></canvas>

                </div>


            </div>


            <!-- =================================================
                 BRANCH OF SERVICE
            ================================================== -->

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


                <div
                    class="chart-wrapper doughnut-wrapper"
                >

                    <canvas
                        id="branchChart"
                    ></canvas>

                </div>


            </div>


            <!-- =================================================
                 PERSONNEL BY STANDING
            ================================================== -->

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


                <div
                    class="chart-wrapper doughnut-wrapper"
                >

                    <canvas
                        id="standingChart"
                    ></canvas>

                </div>


            </div>


            <!-- =================================================
                 YEAR GRADUATED
            ================================================== -->

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

                    <canvas
                        id="yearChart"
                    ></canvas>

                </div>


            </div>


            <!-- =================================================
                 PERSONNEL BY COURSE
            ================================================== -->

            <div
                class="chart-panel chart-panel-wide"
            >


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


                <div
                    class="chart-wrapper course-chart-wrapper"
                >

                    <canvas
                        id="courseChart"
                    ></canvas>

                </div>


            </div>


        </section>


    </main>


</div>


<!-- =========================================================
     BOOTSTRAP JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =========================================================
     CHART DATA FROM PHP
========================================================= -->

<script>

/*
|--------------------------------------------------------------------------
| RANK DATA
|--------------------------------------------------------------------------
*/

const rankLabels =
    <?= json_encode(
        $rankLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const rankData =
    <?= json_encode(
        $rankData
    ); ?>;


/*
|--------------------------------------------------------------------------
| BRANCH DATA
|--------------------------------------------------------------------------
*/

const branchLabels =
    <?= json_encode(
        $branchLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const branchData =
    <?= json_encode(
        $branchData
    ); ?>;


/*
|--------------------------------------------------------------------------
| STANDING DATA
|--------------------------------------------------------------------------
*/

const standingLabels =
    <?= json_encode(
        $standingLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const standingData =
    <?= json_encode(
        $standingData
    ); ?>;


/*
|--------------------------------------------------------------------------
| COURSE DATA
|--------------------------------------------------------------------------
*/

const courseLabels =
    <?= json_encode(
        $courseLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const courseData =
    <?= json_encode(
        $courseData
    ); ?>;


/*
|--------------------------------------------------------------------------
| YEAR DATA
|--------------------------------------------------------------------------
*/

const yearLabels =
    <?= json_encode(
        $yearLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const yearData =
    <?= json_encode(
        $yearData
    ); ?>;


/*
|--------------------------------------------------------------------------
| CHART COLORS
|--------------------------------------------------------------------------
*/

const chartColors = [

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
| CHART.JS DEFAULTS
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

const rankCanvas =
    document.getElementById('rankChart');


if (rankCanvas) {

    new Chart(

        rankCanvas,

        {

            type: 'bar',

            data: {

                labels: rankLabels,

                datasets: [{

                    data: rankData,

                    backgroundColor:
                        '#2d7fc1',

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

}


/*
|--------------------------------------------------------------------------
| BRANCH CHART
|--------------------------------------------------------------------------
*/

const branchCanvas =
    document.getElementById('branchChart');


if (branchCanvas) {

    new Chart(

        branchCanvas,

        {

            type: 'doughnut',

            data: {

                labels: branchLabels,

                datasets: [{

                    data: branchData,

                    backgroundColor:
                        chartColors,

                    borderColor:
                        '#ffffff',

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

}


/*
|--------------------------------------------------------------------------
| STANDING CHART
|--------------------------------------------------------------------------
*/

const standingCanvas =
    document.getElementById(
        'standingChart'
    );


if (standingCanvas) {

    new Chart(

        standingCanvas,

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

                    borderColor:
                        '#ffffff',

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

}


/*
|--------------------------------------------------------------------------
| YEAR CHART
|--------------------------------------------------------------------------
*/

const yearCanvas =
    document.getElementById(
        'yearChart'
    );


if (yearCanvas) {

    new Chart(

        yearCanvas,

        {

            type: 'line',

            data: {

                labels: yearLabels,

                datasets: [{

                    data: yearData,

                    borderColor:
                        '#2d7fc1',

                    backgroundColor:
                        'rgba(45, 127, 193, 0.10)',

                    borderWidth: 3,

                    fill: true,

                    tension: 0.35,

                    pointRadius: 4,

                    pointHoverRadius: 6,

                    pointBackgroundColor:
                        '#2d7fc1',

                    pointBorderColor:
                        '#ffffff',

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

}


/*
|--------------------------------------------------------------------------
| COURSE CHART
|--------------------------------------------------------------------------
*/

const courseCanvas =
    document.getElementById(
        'courseChart'
    );


if (courseCanvas) {

    new Chart(

        courseCanvas,

        {

            type: 'bar',

            data: {

                labels: courseLabels,

                datasets: [{

                    data: courseData,

                    backgroundColor:
                        chartColors,

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

}

</script>


<!-- =========================================================
     SHARED DASHBOARD JAVASCRIPT
========================================================= -->

<script
    src="js/dashboard.js"
></script>


</body>

</html>
