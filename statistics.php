<?php

/*
 * ==========================================================
 * LOGIN SECURITY
 * ==========================================================
 */

session_start();

if (!isset($_SESSION['username'])) {

    header("Location: login.php");

    exit;
}


/*
 * ==========================================================
 * DATABASE CONNECTION
 * ==========================================================
 */

require_once 'db.php';


/*
 * ==========================================================
 * DEFAULT VALUES
 * ==========================================================
 */

$totalPersonnel = 0;

$rankStatistics = [];
$branchStatistics = [];
$standingStatistics = [];
$courseStatistics = [];
$yearStatistics = [];

$databaseError = false;


/*
 * ==========================================================
 * GET STATISTICS
 * ==========================================================
 */

try {


    /*
     * ------------------------------------------------------
     * TOTAL PERSONNEL
     * ------------------------------------------------------
     */

    $stmt = $connection->query("
        SELECT COUNT(*)
        FROM military_personnel
    ");

    $totalPersonnel = (int) $stmt->fetchColumn();


    /*
     * ------------------------------------------------------
     * PERSONNEL BY RANK
     * ------------------------------------------------------
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
     * ------------------------------------------------------
     * PERSONNEL BY BRANCH OF SERVICE
     * ------------------------------------------------------
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
     * ------------------------------------------------------
     * PERSONNEL BY STANDING
     * ------------------------------------------------------
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
     * ------------------------------------------------------
     * PERSONNEL BY COURSE
     * ------------------------------------------------------
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
     * ------------------------------------------------------
     * PERSONNEL BY YEAR GRADUATED
     * ------------------------------------------------------
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

        ORDER BY label ASC
    ");

    $yearStatistics =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    /*
     * Do not expose database information.
     */

    $databaseError = true;

}


/*
 * ==========================================================
 * PREPARE RANK CHART DATA
 * ==========================================================
 */

$rankLabels = [];
$rankData = [];

foreach ($rankStatistics as $row) {

    $rankLabels[] =
        $row['label'];

    $rankData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * PREPARE BRANCH CHART DATA
 * ==========================================================
 */

$branchLabels = [];
$branchData = [];

foreach ($branchStatistics as $row) {

    $branchLabels[] =
        $row['label'];

    $branchData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * PREPARE STANDING CHART DATA
 * ==========================================================
 */

$standingLabels = [];
$standingData = [];

foreach ($standingStatistics as $row) {

    $standingLabels[] =
        $row['label'];

    $standingData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * PREPARE COURSE CHART DATA
 * ==========================================================
 */

$courseLabels = [];
$courseData = [];

foreach ($courseStatistics as $row) {

    $courseLabels[] =
        $row['label'];

    $courseData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * PREPARE YEAR CHART DATA
 * ==========================================================
 */

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
        Statistics | CMO Training Squadron
    </title>


    <!-- ==================================================
         BOOTSTRAP
         ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >


    <!-- ==================================================
         BOOTSTRAP ICONS
         ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- ==================================================
         GOOGLE FONT
         ================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- ==================================================
         SHARED DASHBOARD CSS
         ================================================== -->

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >


    <!-- ==================================================
         STATISTICS CSS
         ================================================== -->

    <link
        rel="stylesheet"
        href="css/statistics.css"
    >


    <!-- ==================================================
         CHART.JS
         ================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>

</head>


<body>


<!-- ======================================================
     TOP HEADER
     ====================================================== -->

<header class="paf-topbar">


    <!-- ==================================================
         LEFT BRAND
         ================================================== -->

    <div class="paf-brand">


        <div class="logo-wrapper">

            <img
                src="cmo1.png"
                alt="CMO Training Squadron Logo"
            >

        </div>


        <div class="paf-text">

            <h1>
                CMO Training Squadron
            </h1>

            <span>
                Student Database Information System
            </span>

        </div>

    </div>


    <!-- ==================================================
         RIGHT USER AREA
         ================================================== -->

    <div class="header-user">


        <i class="bi bi-person-circle"></i>


        <span>

            <?= htmlspecialchars(
                $_SESSION['username']
            ); ?>

        </span>


    </div>


</header>


<!-- ======================================================
     APPLICATION LAYOUT
     ====================================================== -->

<div class="app-layout">


    <!-- ==================================================
         SIDEBAR
         ================================================== -->

    <aside class="sidebar">


        <!-- ==================================================
             SIDEBAR BRAND
             ================================================== -->

        <div class="sidebar-heading">

            <i class="bi bi-grid-1x2-fill"></i>

            <span>
                MAIN MENU
            </span>

        </div>


        <!-- ==================================================
             DASHBOARD
             ================================================== -->

        <a
            href="index.php"
            class="nav-item"
        >

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- ==================================================
             MILITARY PERSONNEL
             ================================================== -->

        <a
            href="index.php"
            class="nav-item"
        >

            <i class="bi bi-people-fill"></i>

            <span>
                Military Personnel
            </span>

        </a>


        <!-- ==================================================
             STATISTICS
             ================================================== -->

        <a
            href="statistics.php"
            class="nav-item active"
        >

            <i class="bi bi-bar-chart-fill"></i>

            <span>
                Statistics
            </span>

        </a>


        <!-- ==================================================
             COPY FILE
             ================================================== -->

        <div class="nav-group">


            <button
                type="button"
                class="nav-item nav-dropdown-toggle"
                id="copyFileToggle"
            >

                <i class="bi bi-file-earmark-arrow-down-fill"></i>

                <span>
                    Copy File
                </span>

                <i
                    class="bi bi-chevron-down nav-arrow"
                ></i>

            </button>


            <div
                class="submenu"
                id="copyFileMenu"
            >

                <a href="index.php">

                    <i class="bi bi-file-earmark-excel"></i>

                    <span>
                        Export to Excel
                    </span>

                </a>


                <a href="index.php">

                    <i class="bi bi-file-earmark-pdf"></i>

                    <span>
                        Export to PDF
                    </span>

                </a>


                <a href="index.php">

                    <i class="bi bi-printer"></i>

                    <span>
                        Print / Table
                    </span>

                </a>

            </div>

        </div>


        <!-- ==================================================
             ADD PERSONNEL
             ================================================== -->

        <a
            href="create.php"
            class="nav-item"
        >

            <i class="bi bi-person-plus-fill"></i>

            <span>
                Add Military Personnel
            </span>

        </a>


        <!-- ==================================================
             DIVIDER
             ================================================== -->

        <div class="sidebar-divider"></div>


        <!-- ==================================================
             CHANGE PASSWORD
             ================================================== -->

        <a
            href="change_password.php"
            class="nav-item"
        >

            <i class="bi bi-key-fill"></i>

            <span>
                Change Password
            </span>

        </a>


        <!-- ==================================================
             LOGOUT
             ================================================== -->

        <a
            href="logout.php"
            class="nav-item logout-item"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>


    </aside>


    <!-- ==================================================
         MAIN CONTENT
         ================================================== -->

    <main class="main-content">


        <!-- ==================================================
             MOBILE PAGE HEADER
             ================================================== -->

        <div class="mobile-page-header">

            <button
                type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
            >

                <i class="bi bi-list"></i>

            </button>


            <span>
                Personnel Statistics
            </span>

        </div>


        <!-- ==================================================
             PAGE HEADER
             ================================================== -->

        <div class="statistics-header">


            <div>

                <div class="page-eyebrow">

                    <i class="bi bi-bar-chart-line-fill"></i>

                    INFORMATION ANALYTICS

                </div>


                <h2>
                    Personnel Statistics
                </h2>


                <p>

                    Overview of military personnel
                    information recorded in the
                    CMO Training Squadron database.

                </p>

            </div>


            <div class="statistics-date">

                <i class="bi bi-calendar3"></i>

                Personnel Overview

            </div>


        </div>


        <!-- ==================================================
             DATABASE ERROR
             ================================================== -->

        <?php if ($databaseError): ?>

            <div class="database-error">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>

                    <strong>
                        Unable to load statistics
                    </strong>

                    <span>
                        Please check the database connection
                        and try again.
                    </span>

                </div>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             SUMMARY CARDS
             ================================================== -->

        <div class="summary-grid">


            <!-- TOTAL PERSONNEL -->

            <div class="summary-card">


                <div class="summary-card-top">

                    <div class="summary-icon personnel-icon">

                        <i class="bi bi-people-fill"></i>

                    </div>


                    <span class="summary-card-label">

                        TOTAL

                    </span>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        $totalPersonnel
                    ); ?>

                </div>


                <div class="summary-label">

                    Military Personnel

                </div>


            </div>


            <!-- RANKS -->

            <div class="summary-card">


                <div class="summary-card-top">

                    <div class="summary-icon rank-icon">

                        <i class="bi bi-person-badge-fill"></i>

                    </div>


                    <span class="summary-card-label">

                        CATEGORIES

                    </span>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        count($rankStatistics)
                    ); ?>

                </div>


                <div class="summary-label">

                    Different Ranks

                </div>


            </div>


            <!-- BRANCHES -->

            <div class="summary-card">


                <div class="summary-card-top">

                    <div class="summary-icon branch-icon">

                        <i class="bi bi-shield-fill"></i>

                    </div>


                    <span class="summary-card-label">

                        SERVICE

                    </span>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        count($branchStatistics)
                    ); ?>

                </div>


                <div class="summary-label">

                    Branches of Service

                </div>


            </div>


            <!-- COURSES -->

            <div class="summary-card">


                <div class="summary-card-top">

                    <div class="summary-icon course-icon">

                        <i class="bi bi-mortarboard-fill"></i>

                    </div>


                    <span class="summary-card-label">

                        TRAINING

                    </span>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        count($courseStatistics)
                    ); ?>

                </div>


                <div class="summary-label">

                    Recorded Courses

                </div>


            </div>


        </div>


        <!-- ==================================================
             FIRST CHART ROW
             ================================================== -->

        <div class="statistics-grid">


            <!-- ==================================================
                 RANK CHART
                 ================================================== -->

            <section class="chart-card">


                <div class="chart-card-header">


                    <div>

                        <div class="chart-icon">

                            <i class="bi bi-person-badge"></i>

                        </div>

                    </div>


                    <div class="chart-heading">

                        <h3>
                            Personnel by Rank
                        </h3>

                        <p>
                            Distribution of personnel
                            according to rank.
                        </p>

                    </div>


                </div>


                <div class="chart-container">

                    <canvas id="rankChart"></canvas>

                </div>


            </section>


            <!-- ==================================================
                 BRANCH CHART
                 ================================================== -->

            <section class="chart-card">


                <div class="chart-card-header">


                    <div>

                        <div class="chart-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>

                    </div>


                    <div class="chart-heading">

                        <h3>
                            Branch of Service
                        </h3>

                        <p>
                            Personnel distribution
                            by service branch.
                        </p>

                    </div>


                </div>


                <div class="chart-container doughnut-container">

                    <canvas id="branchChart"></canvas>

                </div>


            </section>


        </div>


        <!-- ==================================================
             SECOND CHART ROW
             ================================================== -->

        <div class="statistics-grid">


            <!-- ==================================================
                 STANDING CHART
                 ================================================== -->

            <section class="chart-card">


                <div class="chart-card-header">


                    <div>

                        <div class="chart-icon">

                            <i class="bi bi-award-fill"></i>

                        </div>

                    </div>


                    <div class="chart-heading">

                        <h3>
                            Personnel by Standing
                        </h3>

                        <p>
                            Distribution according
                            to personnel standing.
                        </p>

                    </div>


                </div>


                <div class="chart-container doughnut-container">

                    <canvas id="standingChart"></canvas>

                </div>


            </section>


            <!-- ==================================================
                 YEAR CHART
                 ================================================== -->

            <section class="chart-card">


                <div class="chart-card-header">


                    <div>

                        <div class="chart-icon">

                            <i class="bi bi-calendar3"></i>

                        </div>

                    </div>


                    <div class="chart-heading">

                        <h3>
                            Year Graduated
                        </h3>

                        <p>
                            Personnel recorded according
                            to graduation year.
                        </p>

                    </div>


                </div>


                <div class="chart-container">

                    <canvas id="yearChart"></canvas>

                </div>


            </section>


        </div>


        <!-- ==================================================
             COURSE CHART
             ================================================== -->

        <section class="chart-card course-chart-card">


            <div class="chart-card-header">


                <div>

                    <div class="chart-icon">

                        <i class="bi bi-mortarboard-fill"></i>

                    </div>

                </div>


                <div class="chart-heading">

                    <h3>
                        Personnel by Course
                    </h3>

                    <p>
                        Number of personnel associated
                        with each recorded course.
                    </p>

                </div>


            </div>


            <div class="chart-container course-container">

                <canvas id="courseChart"></canvas>

            </div>


        </section>


        <!-- ==================================================
             FOOTER
             ================================================== -->

        <div class="statistics-footer">

            <span>

                <i class="bi bi-database-check"></i>

                Data source: Military Personnel Database

            </span>


            <span>

                CMO Training Squadron

            </span>

        </div>


    </main>


</div>


<!-- ======================================================
     BOOTSTRAP JAVASCRIPT
     ====================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
 * ==========================================================
 * PHP CHART DATA
 * ==========================================================
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
 * ==========================================================
 * CHART COLORS
 * ==========================================================
 */

const chartColors = [

    '#1d6fa5',
    '#2583bd',
    '#3198ce',
    '#45a9d7',
    '#65b8df',
    '#83c8e7',
    '#3d82ad',
    '#24638d',
    '#174e73',
    '#528eaf'

];


/*
 * ==========================================================
 * CHART DEFAULTS
 * ==========================================================
 */

Chart.defaults.font.family =
    'Inter, Arial, sans-serif';

Chart.defaults.font.size = 11;

Chart.defaults.color =
    '#8b9aaa';


/*
 * ==========================================================
 * RANK CHART
 * ==========================================================
 */

new Chart(

    document.getElementById('rankChart'),

    {

        type: 'bar',

        data: {

            labels: rankLabels,

            datasets: [{

                label: 'Personnel',

                data: rankData,

                backgroundColor: '#1d6fa5',

                borderRadius: 6,

                borderSkipped: false,

                barThickness: 28

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

                        precision: 0,

                        color: '#8b9aaa'

                    },

                    grid: {

                        color:
                            'rgba(255,255,255,0.06)'

                    }

                },

                x: {

                    ticks: {

                        color: '#8b9aaa'

                    },

                    grid: {

                        display: false

                    }

                }

            }

        }

    }

);


/*
 * ==========================================================
 * BRANCH CHART
 * ==========================================================
 */

new Chart(

    document.getElementById('branchChart'),

    {

        type: 'doughnut',

        data: {

            labels: branchLabels,

            datasets: [{

                data: branchData,

                backgroundColor: chartColors,

                borderWidth: 3,

                borderColor: '#101923',

                hoverOffset: 6

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

                        color: '#9aa8b6',

                        padding: 16,

                        usePointStyle: true,

                        pointStyle: 'circle'

                    }

                }

            }

        }

    }

);


/*
 * ==========================================================
 * STANDING CHART
 * ==========================================================
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

                    '#1d6fa5',
                    '#20a464',
                    '#d9a52e',
                    '#d94a4a',
                    '#8066b3',
                    '#607d8b'

                ],

                borderWidth: 3,

                borderColor: '#101923',

                hoverOffset: 6

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '58%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        color: '#9aa8b6',

                        padding: 16,

                        usePointStyle: true,

                        pointStyle: 'circle'

                    }

                }

            }

        }

    }

);


/*
 * ==========================================================
 * YEAR CHART
 * ==========================================================
 */

new Chart(

    document.getElementById('yearChart'),

    {

        type: 'line',

        data: {

            labels: yearLabels,

            datasets: [{

                label: 'Personnel',

                data: yearData,

                borderColor: '#3198ce',

                backgroundColor:
                    'rgba(49,152,206,0.10)',

                borderWidth: 3,

                fill: true,

                tension: 0.35,

                pointBackgroundColor:
                    '#3198ce',

                pointBorderColor:
                    '#101923',

                pointBorderWidth: 2,

                pointRadius: 4,

                pointHoverRadius: 6

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

                        precision: 0,

                        color: '#8b9aaa'

                    },

                    grid: {

                        color:
                            'rgba(255,255,255,0.06)'

                    }

                },

                x: {

                    ticks: {

                        color: '#8b9aaa'

                    },

                    grid: {

                        display: false

                    }

                }

            }

        }

    }

);


/*
 * ==========================================================
 * COURSE CHART
 * ==========================================================
 */

new Chart(

    document.getElementById('courseChart'),

    {

        type: 'bar',

        data: {

            labels: courseLabels,

            datasets: [{

                label: 'Personnel',

                data: courseData,

                backgroundColor: chartColors,

                borderRadius: 6,

                borderSkipped: false,

                barThickness: 22

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

                        precision: 0,

                        color: '#8b9aaa'

                    },

                    grid: {

                        color:
                            'rgba(255,255,255,0.06)'

                    }

                },

                y: {

                    ticks: {

                        color: '#8b9aaa'

                    },

                    grid: {

                        display: false

                    }

                }

            }

        }

    }

);


/*
 * ==========================================================
 * COPY FILE DROPDOWN
 * ==========================================================
 */

const copyFileToggle =
    document.getElementById(
        'copyFileToggle'
    );

const copyFileMenu =
    document.getElementById(
        'copyFileMenu'
    );


if (
    copyFileToggle &&
    copyFileMenu
) {

    copyFileToggle.addEventListener(
        'click',
        function () {

            copyFileMenu.classList.toggle(
                'show'
            );

            copyFileToggle.classList.toggle(
                'open'
            );

        }
    );

}


/*
 * ==========================================================
 * MOBILE SIDEBAR
 * ==========================================================
 */

const sidebarToggle =
    document.getElementById(
        'sidebarToggle'
    );

const sidebar =
    document.querySelector(
        '.sidebar'
    );


if (
    sidebarToggle &&
    sidebar
) {

    sidebarToggle.addEventListener(
        'click',
        function () {

            sidebar.classList.toggle(
                'mobile-open'
            );

        }
    );

}

</script>


</body>

</html>