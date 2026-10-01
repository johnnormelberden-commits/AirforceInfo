<?php

/*
|--------------------------------------------------------------------------
| EDIT PERSONNEL
|--------------------------------------------------------------------------
| CMO Training Squadron
| Student Database Information System
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SESSION SECURITY
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
| CSRF TOKEN
|--------------------------------------------------------------------------
|
| Create a CSRF token for this form.
|
*/

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));

}


/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$id                = '';
$rank              = '';
$name              = '';
$serial_number     = '';
$branch_of_service = '';
$courses           = '';
$year_graduated    = '';
$standing          = '';

$errorMessage = '';


/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| LOAD RECORD
|--------------------------------------------------------------------------
|
| This happens when edit.php?id=123 is opened.
|
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    /*
    |--------------------------------------------------------------------------
    | CHECK ID
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_GET['id']) ||
        !ctype_digit((string) $_GET['id'])
    ) {

        header("Location: index.php");
        exit;

    }


    $id = (int) $_GET['id'];


    if ($id <= 0) {

        header("Location: index.php");
        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | GET PERSONNEL RECORD
    |--------------------------------------------------------------------------
    */

    try {

        $stmt = $connection->prepare(
            "
            SELECT
                id,
                `rank`,
                name,
                serial_number,
                branch_of_service,
                courses,
                year_graduated,
                standing
            FROM military_personnel
            WHERE id = :id
            LIMIT 1
            "
        );


        $stmt->execute([
            ':id' => $id
        ]);


        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | RECORD NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (!$row) {

            header("Location: index.php");
            exit;

        }


        /*
        |--------------------------------------------------------------------------
        | LOAD VALUES INTO FORM
        |--------------------------------------------------------------------------
        */

        $rank =
            $row['rank'] ?? '';

        $name =
            $row['name'] ?? '';

        $serial_number =
            $row['serial_number'] ?? '';

        $branch_of_service =
            $row['branch_of_service'] ?? '';

        $courses =
            $row['courses'] ?? '';

        $year_graduated =
            $row['year_graduated'] ?? '';

        $standing =
            $row['standing'] ?? '';


    } catch (PDOException $e) {

        /*
        |--------------------------------------------------------------------------
        | DO NOT DISPLAY DATABASE DETAILS
        |--------------------------------------------------------------------------
        */

        $errorMessage =
            "Unable to load the personnel information.";

    }

}


/*
|--------------------------------------------------------------------------
| HANDLE UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | GET FORM VALUES
    |--------------------------------------------------------------------------
    */

    $id =
        $_POST['id'] ?? '';

    $rank =
        trim($_POST['rank'] ?? '');

    $name =
        trim($_POST['name'] ?? '');

    $serial_number =
        trim($_POST['serial_number'] ?? '');

    $branch_of_service =
        trim($_POST['branch_of_service'] ?? '');

    $courses =
        trim($_POST['courses'] ?? '');

    $year_graduated =
        trim($_POST['year_graduated'] ?? '');

    $standing =
        trim($_POST['standing'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VERIFY CSRF TOKEN
    |--------------------------------------------------------------------------
    */

    $submittedToken =
        $_POST['csrf_token'] ?? '';

    if (
        empty($submittedToken) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $submittedToken
        )
    ) {

        $errorMessage =
            "Invalid form request. Please refresh the page and try again.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE ID
    |--------------------------------------------------------------------------
    */

    elseif (
        !ctype_digit((string) $id)
    ) {

        $errorMessage =
            "Invalid personnel ID.";

    } else {

        $id = (int) $id;


        if ($id <= 0) {

            $errorMessage =
                "Invalid personnel ID.";

        }


        /*
        |--------------------------------------------------------------------------
        | REQUIRED FIELDS
        |--------------------------------------------------------------------------
        */

        elseif (
            $rank === '' ||
            $name === '' ||
            $serial_number === '' ||
            $branch_of_service === '' ||
            $courses === '' ||
            $year_graduated === '' ||
            $standing === ''
        ) {

            $errorMessage =
                "All fields are required.";

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE YEAR
        |--------------------------------------------------------------------------
        */

        elseif (
            !ctype_digit($year_graduated)
        ) {

            $errorMessage =
                "Please select a valid graduation year.";

        } else {

            $year =
                (int) $year_graduated;

            $currentYear =
                (int) date('Y');


            if (
                $year < 1960 ||
                $year > $currentYear
            ) {

                $errorMessage =
                    "Please select a valid graduation year.";

            }

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE DATABASE
        |--------------------------------------------------------------------------
        */

        if ($errorMessage === '') {

            try {

                /*
                |--------------------------------------------------------------------------
                | START TRANSACTION
                |--------------------------------------------------------------------------
                */

                $connection->beginTransaction();


                /*
                |--------------------------------------------------------------------------
                | CHECK RECORD EXISTS
                |--------------------------------------------------------------------------
                */

                $checkStmt =
                    $connection->prepare(
                        "
                        SELECT
                            id
                        FROM military_personnel
                        WHERE id = :id
                        LIMIT 1
                        "
                    );


                $checkStmt->execute([
                    ':id' => $id
                ]);


                $existingRecord =
                    $checkStmt->fetch(PDO::FETCH_ASSOC);


                if (!$existingRecord) {

                    $connection->rollBack();

                    $errorMessage =
                        "Personnel record was not found.";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK DUPLICATE SERIAL NUMBER
                    |--------------------------------------------------------------------------
                    |
                    | Prevent another personnel record from using
                    | the same serial number.
                    |
                    */

                    $duplicateStmt =
                        $connection->prepare(
                            "
                            SELECT
                                id
                            FROM military_personnel
                            WHERE serial_number = :serial_number
                            AND id <> :id
                            LIMIT 1
                            "
                        );


                    $duplicateStmt->execute([
                        ':serial_number' =>
                            $serial_number,

                        ':id' =>
                            $id
                    ]);


                    $duplicate =
                        $duplicateStmt->fetch(
                            PDO::FETCH_ASSOC
                        );


                    if ($duplicate) {

                        $connection->rollBack();

                        $errorMessage =
                            "The serial number is already registered to another personnel record.";

                    } else {


                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE RECORD
                        |--------------------------------------------------------------------------
                        */

                        $sql = "
                            UPDATE military_personnel
                            SET
                                `rank` = :rank,
                                name = :name,
                                serial_number = :serial_number,
                                branch_of_service = :branch_of_service,
                                courses = :courses,
                                year_graduated = :year_graduated,
                                standing = :standing,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id
                        ";


                        $stmt =
                            $connection->prepare($sql);


                        $stmt->execute([
                            ':rank' =>
                                $rank,

                            ':name' =>
                                $name,

                            ':serial_number' =>
                                $serial_number,

                            ':branch_of_service' =>
                                $branch_of_service,

                            ':courses' =>
                                $courses,

                            ':year_graduated' =>
                                $year,

                            ':standing' =>
                                $standing,

                            ':id' =>
                                $id
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | COMMIT
                        |--------------------------------------------------------------------------
                        */

                        $connection->commit();


                        /*
                        |--------------------------------------------------------------------------
                        | SUCCESS REDIRECT
                        |--------------------------------------------------------------------------
                        */

                        header(
                            "Location: index.php?updated=1"
                        );

                        exit;

                    }

                }


            } catch (PDOException $e) {


                /*
                |--------------------------------------------------------------------------
                | ROLLBACK IF NECESSARY
                |--------------------------------------------------------------------------
                */

                if (
                    $connection->inTransaction()
                ) {

                    $connection->rollBack();

                }


                /*
                |--------------------------------------------------------------------------
                | GENERIC ERROR
                |--------------------------------------------------------------------------
                */

                $errorMessage =
                    "Unable to update the personnel information.";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| CURRENT YEAR
|--------------------------------------------------------------------------
*/

$currentYear =
    (int) date('Y');

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
        Edit Personnel - CMO Training Squadron
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


    <style>

        /*
        |--------------------------------------------------------------------------
        | GLOBAL
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top left,
                    #021631 0%,
                    #05254d 45%,
                    #020b18 100%
                );

            color: #e6edf3;

            font-family:
                "Inter",
                "Segoe UI",
                sans-serif;

            overflow-x: hidden;

        }


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .paf-header {

            background:
                linear-gradient(
                    90deg,
                    #002b6b,
                    #0057b7
                );

            border-bottom:
                3px solid #ffd700;

            padding:
                12px 24px;

            display: flex;

            align-items: center;

            gap: 16px;

            color: #ffffff;

            box-shadow:
                0 6px 20px
                rgba(0, 0, 0, 0.4);

        }


        .paf-header img {

            width: 60px;

            height: 60px;

            object-fit: contain;

        }


        .paf-header-text h1 {

            margin: 0;

            font-size: 1.25rem;

            font-weight: 800;

            letter-spacing: 0.8px;

            text-transform: uppercase;

        }


        .paf-header-text span {

            display: block;

            margin-top: 3px;

            font-size: 0.82rem;

            opacity: 0.9;

        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTAINER
        |--------------------------------------------------------------------------
        */

        .container-main {

            max-width: 900px;

            margin-top: 50px;

            margin-bottom: 50px;

            animation:
                slideIn
                0.6s
                ease-in-out;

        }


        @keyframes slideIn {

            from {

                opacity: 0;

                transform:
                    translateY(25px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .edit-card {

            background:
                #0f1724;

            border:
                1px solid #1f3b63;

            border-radius:
                15px;

            overflow: hidden;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.55);

        }


        /*
        |--------------------------------------------------------------------------
        | CARD HEADER
        |--------------------------------------------------------------------------
        */

        .card-header-custom {

            background:
                linear-gradient(
                    90deg,
                    #003b88,
                    #0057b7
                );

            border-bottom:
                2px solid #ffd700;

            padding:
                18px 24px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

        }


        .card-title {

            display: flex;

            align-items: center;

            gap: 10px;

            color: #ffffff;

            font-size: 1.05rem;

            font-weight: 700;

        }


        .card-title i {

            color: #ffd700;

            font-size: 1.25rem;

        }


        .record-id {

            color:
                rgba(255,255,255,0.75);

            font-size:
                0.82rem;

        }


        /*
        |--------------------------------------------------------------------------
        | CARD BODY
        |--------------------------------------------------------------------------
        */

        .card-body-custom {

            padding:
                30px;

        }


        /*
        |--------------------------------------------------------------------------
        | FORM LABEL
        |--------------------------------------------------------------------------
        */

        .form-label {

            color:
                #d0e2ff;

            font-size:
                0.9rem;

            font-weight:
                600;

            margin-bottom:
                8px;

        }


        /*
        |--------------------------------------------------------------------------
        | FORM CONTROLS
        |--------------------------------------------------------------------------
        */

        .form-control,
        .form-select {

            min-height:
                46px;

            color:
                #ffffff !important;

            background:
                rgba(0, 0, 0, 0.35) !important;

            border:
                1px solid #264b7c;

            border-radius:
                8px;

            padding:
                10px 13px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;

        }


        .form-control:focus,
        .form-select:focus {

            color:
                #ffffff !important;

            background:
                #071021 !important;

            border-color:
                #ffd700;

            box-shadow:
                0 0 0 3px
                rgba(255, 215, 0, 0.12);

            outline:
                none;

            transform:
                translateY(-1px);

        }


        .form-control::placeholder {

            color:
                rgba(255,255,255,0.45);

        }


        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        .form-select option {

            color:
                #ffffff;

            background:
                #17243a;

        }


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        .alert-danger {

            color:
                #ffdede;

            background:
                rgba(220, 53, 69, 0.15);

            border:
                1px solid
                rgba(220, 53, 69, 0.45);

            border-radius:
                9px;

        }


        /*
        |--------------------------------------------------------------------------
        | BUTTON AREA
        |--------------------------------------------------------------------------
        */

        .form-actions {

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                12px;

            margin-top:
                30px;

            padding-top:
                22px;

            border-top:
                1px solid #1f3b63;

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE BUTTON
        |--------------------------------------------------------------------------
        */

        .btn-update {

            border: none;

            background:
                #0057b7;

            color:
                #ffffff;

            border-radius:
                8px;

            padding:
                11px 24px;

            font-weight:
                600;

            transition:
                all 0.2s ease;

        }


        .btn-update:hover {

            background:
                #003b88;

            color:
                #ffffff;

            box-shadow:
                0 0 14px
                rgba(255, 215, 0, 0.35);

            transform:
                translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | CANCEL BUTTON
        |--------------------------------------------------------------------------
        */

        .btn-cancel {

            border:
                1px solid #ffd700;

            color:
                #ffd700;

            background:
                transparent;

            border-radius:
                8px;

            padding:
                10px 24px;

            font-weight:
                600;

            text-decoration:
                none;

            transition:
                all 0.2s ease;

        }


        .btn-cancel:hover {

            background:
                #ffd700;

            color:
                #001234;

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .paf-header {

                padding:
                    10px 15px;

            }


            .paf-header img {

                width:
                    50px;

                height:
                    50px;

            }


            .paf-header-text h1 {

                font-size:
                    1rem;

            }


            .paf-header-text span {

                font-size:
                    0.72rem;

            }


            .container-main {

                margin-top:
                    25px;

                margin-bottom:
                    25px;

                padding-left:
                    15px;

                padding-right:
                    15px;

            }


            .card-body-custom {

                padding:
                    20px;

            }


            .card-header-custom {

                padding:
                    15px 18px;

            }


            .form-actions {

                flex-direction:
                    column-reverse;

                align-items:
                    stretch;

            }


            .btn-update,
            .btn-cancel {

                width:
                    100%;

                text-align:
                    center;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<header class="paf-header">


    <img
        src="cmo1.png"
        alt="CMO Training Squadron Logo"
    >


    <div class="paf-header-text">

        <h1>
            CMO Squadron Training
        </h1>

        <span>
            Student Database Information System
        </span>

    </div>

</header>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container container-main">


    <div class="edit-card">


        <!-- =================================================
             CARD HEADER
        ================================================== -->

        <div class="card-header-custom">


            <div class="card-title">

                <i class="bi bi-pencil-square"></i>

                <span>
                    Update Student Database Information
                </span>

            </div>


            <?php if ($id !== ''): ?>

                <div class="record-id">

                    Record ID:
                    <strong><?= e($id); ?></strong>

                </div>

            <?php endif; ?>


        </div>


        <!-- =================================================
             CARD BODY
        ================================================== -->

        <div class="card-body-custom">


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if ($errorMessage !== ''): ?>

                <div
                    class="alert alert-danger d-flex align-items-center gap-2 mb-4"
                    role="alert"
                >

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    <span>
                        <?= e($errorMessage); ?>
                    </span>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="edit.php"
                autocomplete="off"
            >


                <!-- CSRF -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']); ?>"
                >


                <!-- ID -->

                <input
                    type="hidden"
                    name="id"
                    value="<?= e($id); ?>"
                >


                <!-- =================================================
                     RANK
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="rank"
                        class="form-label"
                    >
                        Rank
                    </label>


                    <select
                        id="rank"
                        name="rank"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Select Rank --
                        </option>


                        <option
                            value="Airman Basic"
                            <?= $rank === 'Airman Basic'
                                ? 'selected'
                                : ''; ?>
                        >
                            Airman Basic
                        </option>


                        <option
                            value="Airman"
                            <?= $rank === 'Airman'
                                ? 'selected'
                                : ''; ?>
                        >
                            Airman
                        </option>


                        <option
                            value="Airman First Class"
                            <?= $rank === 'Airman First Class'
                                ? 'selected'
                                : ''; ?>
                        >
                            Airman First Class
                        </option>


                        <option
                            value="Sergeant"
                            <?= $rank === 'Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Sergeant
                        </option>


                        <option
                            value="Technical Sergeant"
                            <?= $rank === 'Technical Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Technical Sergeant
                        </option>


                        <option
                            value="Master Sergeant"
                            <?= $rank === 'Master Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Master Sergeant
                        </option>


                        <option
                            value="Senior Master Sergeant"
                            <?= $rank === 'Senior Master Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Senior Master Sergeant
                        </option>


                        <option
                            value="Chief Master Sergeant"
                            <?= $rank === 'Chief Master Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Chief Master Sergeant
                        </option>


                        <option
                            value="Lieutenant"
                            <?= $rank === 'Lieutenant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Lieutenant
                        </option>


                        <option
                            value="Captain"
                            <?= $rank === 'Captain'
                                ? 'selected'
                                : ''; ?>
                        >
                            Captain
                        </option>


                        <option
                            value="Major"
                            <?= $rank === 'Major'
                                ? 'selected'
                                : ''; ?>
                        >
                            Major
                        </option>


                        <option
                            value="Lieutenant Colonel"
                            <?= $rank === 'Lieutenant Colonel'
                                ? 'selected'
                                : ''; ?>
                        >
                            Lieutenant Colonel
                        </option>


                        <option
                            value="Colonel"
                            <?= $rank === 'Colonel'
                                ? 'selected'
                                : ''; ?>
                        >
                            Colonel
                        </option>


                        <option
                            value="AW1C"
                            <?= $rank === 'AW1C'
                                ? 'selected'
                                : ''; ?>
                        >
                            AW1C
                        </option>


                        <option
                            value="A1C"
                            <?= $rank === 'A1C'
                                ? 'selected'
                                : ''; ?>
                        >
                            A1C
                        </option>


                        <option
                            value="A2C"
                            <?= $rank === 'A2C'
                                ? 'selected'
                                : ''; ?>
                        >
                            A2C
                        </option>


                        <option
                            value="Staff Sergeant"
                            <?= $rank === 'Staff Sergeant'
                                ? 'selected'
                                : ''; ?>
                        >
                            Staff Sergeant
                        </option>


                    </select>

                </div>


                <!-- =================================================
                     NAME
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Name
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?= e($name); ?>"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- =================================================
                     SERIAL NUMBER
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="serial_number"
                        class="form-label"
                    >
                        Serial Number
                    </label>


                    <input
                        type="text"
                        id="serial_number"
                        name="serial_number"
                        class="form-control"
                        value="<?= e($serial_number); ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- =================================================
                     BRANCH
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="branch_of_service"
                        class="form-label"
                    >
                        Branch of Service
                    </label>


                    <select
                        id="branch_of_service"
                        name="branch_of_service"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Select Branch --
                        </option>


                        <option
                            value="Philippine Air Force"
                            <?= $branch_of_service === 'Philippine Air Force'
                                ? 'selected'
                                : ''; ?>
                        >
                            Philippine Air Force
                        </option>


                        <option
                            value="Philippine Army"
                            <?= $branch_of_service === 'Philippine Army'
                                ? 'selected'
                                : ''; ?>
                        >
                            Philippine Army
                        </option>


                        <option
                            value="Philippine Navy"
                            <?= $branch_of_service === 'Philippine Navy'
                                ? 'selected'
                                : ''; ?>
                        >
                            Philippine Navy
                        </option>


                        <option
                            value="Reserved Force"
                            <?= $branch_of_service === 'Reserved Force'
                                ? 'selected'
                                : ''; ?>
                        >
                            Reserved Force
                        </option>


                        <option
                            value="Others"
                            <?= $branch_of_service === 'Others'
                                ? 'selected'
                                : ''; ?>
                        >
                            Others
                        </option>


                    </select>

                </div>


                <!-- =================================================
                     COURSES
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="courses"
                        class="form-label"
                    >
                        Course/s
                    </label>


                    <input
                        type="text"
                        id="courses"
                        name="courses"
                        class="form-control"
                        value="<?= e($courses); ?>"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- =================================================
                     YEAR GRADUATED
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="year_graduated"
                        class="form-label"
                    >
                        Year Graduated
                    </label>


                    <select
                        id="year_graduated"
                        name="year_graduated"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Select Year --
                        </option>


                        <?php for (
                            $y = $currentYear;
                            $y >= 1960;
                            $y--
                        ): ?>

                            <option
                                value="<?= $y; ?>"
                                <?= (string) $year_graduated ===
                                    (string) $y
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?= $y; ?>
                            </option>

                        <?php endfor; ?>


                    </select>

                </div>


                <!-- =================================================
                     STANDING
                ================================================== -->

                <div class="mb-3">

                    <label
                        for="standing"
                        class="form-label"
                    >
                        Standing
                    </label>


                    <input
                        type="text"
                        id="standing"
                        name="standing"
                        class="form-control"
                        value="<?= e($standing); ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- =================================================
                     BUTTONS
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="index.php"
                        class="btn-cancel"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn-update"
                    >

                        <i class="bi bi-check-lg"></i>

                        Update Personnel

                    </button>


                </div>


            </form>


        </div>

    </div>

</main>


<!-- =========================================================
     BOOTSTRAP JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
