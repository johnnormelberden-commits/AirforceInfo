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
        SELECT COUNT(
            DISTINCT NULLIF(
                TRIM(rank),
                ''
            )
        )
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
     SHARED SIDEBAR
========================================================= -->

<?php

require_once __DIR__ . '/includes/sidebar.php';

?>


<!-- =========================================================
     MAIN WRAPPER
========================================================= -->

<div class="main-wrapper">


    <!-- =====================================================
         SHARED TOPBAR
    ====================================================== -->

    <?php

    require_once __DIR__ . '/includes/topbar.php';

    ?>


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


            <!-- TOTAL RANKS -->

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


            <!-- TOTAL BRANCHES -->

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


            <!-- TOTAL COURSES -->

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

                    <canvas id="rankChart"></canvas>

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


                <div class="chart-wrapper doughnut-wrapper">

                    <canvas id="branchChart"></canvas>

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


                <div class="chart-wrapper doughnut-wrapper">

                    <canvas id="standingChart"></canvas>

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

                    <canvas id="yearChart"></canvas>

                </div>

            </div>


            <!-- =================================================
                 PERSONNEL BY COURSE
            ================================================== -->

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
     BOOTSTRAP JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =========================================================
     CHART DATA
========================================================= -->

<script>

const rankLabels =
    <?= json_encode(
        $rankLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const rankData =
    <?= json_encode($rankData); ?>;


const branchLabels =
    <?= json_encode(
        $branchLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const branchData =
    <?= json_encode($branchData); ?>;


const standingLabels =
    <?= json_encode(
        $standingLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const standingData =
    <?= json_encode($standingData); ?>;


const courseLabels =
    <?= json_encode(
        $courseLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const courseData =
    <?= json_encode($courseData); ?>;


const yearLabels =
    <?= json_encode(
        $yearLabels,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ); ?>;

const yearData =
    <?= json_encode($yearData); ?>;


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

    new Chart(rankCanvas, {

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
                        color: '#263b55'
                    }

                },

                x: {

                    grid: {
                        display: false
                    }

                }

            }

        }

    });

}


/*
|--------------------------------------------------------------------------
| BRANCH CHART
|--------------------------------------------------------------------------
*/

const branchCanvas =
    document.getElementById('branchChart');

if (branchCanvas) {

    new Chart(branchCanvas, {

        type: 'doughnut',

        data: {

            labels: branchLabels,

            datasets: [{

                data: branchData,

                backgroundColor: chartColors,

                borderColor: '#111d2d',

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

                        color: '#8fa3bd',

                        padding: 16,

                        usePointStyle: true,

                        boxWidth: 8

                    }

                }

            }

        }

    });

}


/*
|--------------------------------------------------------------------------
| STANDING CHART
|--------------------------------------------------------------------------
*/

const standingCanvas =
    document.getElementById('standingChart');

if (standingCanvas) {

    new Chart(standingCanvas, {

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

                borderColor: '#111d2d',

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

                        color: '#8fa3bd',

                        padding: 16,

                        usePointStyle: true,

                        boxWidth: 8

                    }

                }

            }

        }

    });

}


/*
|--------------------------------------------------------------------------
| YEAR CHART
|--------------------------------------------------------------------------
*/

const yearCanvas =
    document.getElementById('yearChart');

if (yearCanvas) {

    new Chart(yearCanvas, {

        type: 'line',

        data: {

            labels: yearLabels,

            datasets: [{

                data: yearData,

                borderColor: '#3b9cff',

                backgroundColor:
                    'rgba(59, 156, 255, 0.10)',

                borderWidth: 3,

                fill: true,

                tension: 0.35,

                pointRadius: 4,

                pointHoverRadius: 6,

                pointBackgroundColor: '#3b9cff',

                pointBorderColor: '#111d2d',

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
                        precision: 0,
                        color: '#7f95b0'
                    },

                    grid: {
                        color: '#263b55'
                    }

                },

                x: {

                    ticks: {
                        color: '#7f95b0'
                    },

                    grid: {
                        display: false
                    }

                }

            }

        }

    });

}


/*
|--------------------------------------------------------------------------
| COURSE CHART
|--------------------------------------------------------------------------
*/

const courseCanvas =
    document.getElementById('courseChart');

if (courseCanvas) {

    new Chart(courseCanvas, {

        type: 'bar',

        data: {

            labels: courseLabels,

            datasets: [{

                data: courseData,

                backgroundColor: chartColors,

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
                        precision: 0,
                        color: '#7f95b0'
                    },

                    grid: {
                        color: '#263b55'
                    }

                },

                y: {

                    ticks: {
                        color: '#7f95b0'
                    },

                    grid: {
                        display: false
                    }

                }

            }

        }

    });

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
