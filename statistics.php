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
                NULLIF(TRIM(rank), ''),
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
     * PERSONNEL BY BRANCH
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
     * Do not expose database details.
     */

    $databaseError = true;

}


/*
 * ==========================================================
 * RANK CHART DATA
 * ==========================================================
 */

$rankLabels = [];
$rankData = [];

foreach ($rankStatistics as $row) {

    $rankLabels[] = $row['label'];

    $rankData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * BRANCH CHART DATA
 * ==========================================================
 */

$branchLabels = [];
$branchData = [];

foreach ($branchStatistics as $row) {

    $branchLabels[] = $row['label'];

    $branchData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * STANDING CHART DATA
 * ==========================================================
 */

$standingLabels = [];
$standingData = [];

foreach ($standingStatistics as $row) {

    $standingLabels[] = $row['label'];

    $standingData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * COURSE CHART DATA
 * ==========================================================
 */

$courseLabels = [];
$courseData = [];

foreach ($courseStatistics as $row) {

    $courseLabels[] = $row['label'];

    $courseData[] =
        (int) $row['total'];

}


/*
 * ==========================================================
 * YEAR CHART DATA
 * ==========================================================
 */

$yearLabels = [];
$yearData = [];

foreach ($yearStatistics as $row) {

    $yearLabels[] = $row['label'];

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
         DASHBOARD CSS
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
     HEADER
     ====================================================== -->

<div class="paf-topbar">


    <!-- ==================================================
         BRAND
         ================================================== -->

    <div class="paf-brand">

        <img
            src="cmo1.png"
            alt="CMO Training Squadron Logo"
        >


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
         RIGHT SIDE
         ================================================== -->

    <div class="d-flex align-items-center gap-2">


        <!-- USERNAME -->

        <span
            class="text-light small d-none d-md-inline"
        >

            <?= htmlspecialchars(
                $_SESSION['username']
            ); ?>

        </span>


        <!-- CHANGE PASSWORD -->

        <a
            href="change_password.php"
            class="btn btn-outline-warning btn-sm"
        >

            <i class="bi bi-key"></i>

            Change Password

        </a>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="btn btn-outline-light btn-sm logout-btn"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>


<!-- ======================================================
     MAIN CONTENT
     ====================================================== -->

<div class="container-fluid statistics-page">


    <!-- ==================================================
         DATABASE ERROR
         ================================================== -->

    <?php if ($databaseError): ?>

        <div class="database-error">

            <i class="bi bi-exclamation-triangle"></i>

            Unable to load personnel statistics
            from the database.

        </div>

    <?php endif; ?>


    <!-- ==================================================
         PAGE HEADER
         ================================================== -->

    <div class="statistics-header">

        <h2>

            Personnel Statistics

        </h2>


        <p>

            Overview of military personnel information
            recorded in the CMO Training Squadron database.

        </p>

    </div>


    <!-- ==================================================
         SUMMARY CARDS
         ================================================== -->

    <div class="row g-4 mb-4">


        <!-- TOTAL PERSONNEL -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="summary-card">

                <div class="summary-icon">

                    <i class="bi bi-people-fill"></i>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        $totalPersonnel
                    ); ?>

                </div>


                <div class="summary-label">

                    Total Personnel

                </div>

            </div>

        </div>


        <!-- DIFFERENT RANKS -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="summary-card">

                <div class="summary-icon">

                    <i class="bi bi-person-badge-fill"></i>

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

        </div>


        <!-- BRANCHES -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="summary-card">

                <div class="summary-icon">

                    <i class="bi bi-shield-fill"></i>

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

        </div>


        <!-- COURSES -->

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="summary-card">

                <div class="summary-icon">

                    <i class="bi bi-mortarboard-fill"></i>

                </div>


                <div class="summary-number">

                    <?= number_format(
                        count($courseStatistics)
                    ); ?>

                </div>


                <div class="summary-label">

                    Courses

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         RANK + BRANCH
         ================================================== -->

    <div class="row g-4 mb-4">


        <!-- RANK -->

        <div class="col-12 col-lg-6">

            <div class="chart-card">

                <div class="chart-title">

                    Personnel by Rank

                </div>


                <div class="chart-subtitle">

                    Distribution of personnel according
                    to rank.

                </div>


                <div class="chart-container">

                    <canvas id="rankChart"></canvas>

                </div>

            </div>

        </div>


        <!-- BRANCH -->

        <div class="col-12 col-lg-6">

            <div class="chart-card">

                <div class="chart-title">

                    Personnel by Branch of Service

                </div>


                <div class="chart-subtitle">

                    Distribution according to branch
                    of service.

                </div>


                <div class="chart-container">

                    <canvas id="branchChart"></canvas>

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         STANDING + YEAR
         ================================================== -->

    <div class="row g-4 mb-4">


        <!-- STANDING -->

        <div class="col-12 col-lg-6">

            <div class="chart-card">

                <div class="chart-title">

                    Personnel by Standing

                </div>


                <div class="chart-subtitle">

                    Personnel distribution according
                    to standing.

                </div>


                <div class="chart-container">

                    <canvas id="standingChart"></canvas>

                </div>

            </div>

        </div>


        <!-- YEAR -->

        <div class="col-12 col-lg-6">

            <div class="chart-card">

                <div class="chart-title">

                    Personnel by Year Graduated

                </div>


                <div class="chart-subtitle">

                    Number of personnel according
                    to graduation year.

                </div>


                <div class="chart-container">

                    <canvas id="yearChart"></canvas>

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         COURSE
         ================================================== -->

    <div class="row g-4">


        <div class="col-12">

            <div class="chart-card">

                <div class="chart-title">

                    Personnel by Course

                </div>


                <div class="chart-subtitle">

                    Number of personnel associated
                    with each course.

                </div>


                <div class="chart-container">

                    <canvas id="courseChart"></canvas>

                </div>

            </div>

        </div>

    </div>


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
 * PHP DATA
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

    '#1262a2',
    '#1c7bc1',
    '#2c96d2',
    '#52add8',
    '#74c2e3',
    '#8dcef0',
    '#3c82b3',
    '#195a8f',
    '#0b4775',
    '#6ba6cc'

];


/*
 * ==========================================================
 * CHART DEFAULTS
 * ==========================================================
 */

Chart.defaults.font.family =
    'Inter, Arial, sans-serif';

Chart.defaults.font.size = 10;

Chart.defaults.color =
    '#68798b';


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

                backgroundColor: '#1262a2',

                borderRadius: 6,

                borderSkipped: false

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

                borderWidth: 2,

                borderColor: '#ffffff'

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '65%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        padding: 15,

                        usePointStyle: true

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

        type: 'pie',

        data: {

            labels: standingLabels,

            datasets: [{

                data: standingData,

                backgroundColor: [

                    '#1262a2',
                    '#20a464',
                    '#f0ad32',
                    '#d94a4a',
                    '#8b6fc1',
                    '#607d8b'

                ],

                borderWidth: 2,

                borderColor: '#ffffff'

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        padding: 15,

                        usePointStyle: true

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

                borderColor: '#1262a2',

                backgroundColor:
                    'rgba(18, 98, 162, 0.10)',

                borderWidth: 3,

                fill: true,

                tension: 0.35,

                pointBackgroundColor:
                    '#1262a2',

                pointRadius: 4

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

                borderSkipped: false

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

</script>


</body>

</html>