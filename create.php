<?php

/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 */

try {

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '5432';
    $database = getenv('DB_NAME');
    $dbUsername = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    if (!$host || !$database || !$dbUsername || !$dbPassword) {
        throw new Exception("Database configuration is missing.");
    }

    $connection = new PDO(
        "pgsql:host={$host};port={$port};dbname={$database}",
        $dbUsername,
        $dbPassword
    );

    $connection->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (Exception $e) {

    die("Unable to connect to the database.");

}


/*
 * ==========================================================
 * FORM VARIABLES
 * ==========================================================
 */

$rank              = "";
$name              = "";
$serial_number     = "";
$branch_of_service = "";
$courses           = "";
$year_graduated    = "";
$standing          = "";

$errorMessage   = "";
$successMessage = "";


/*
 * ==========================================================
 * HANDLE FORM SUBMISSION
 * ==========================================================
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $rank              = trim($_POST["rank"] ?? '');
    $name              = trim($_POST["name"] ?? '');
    $serial_number     = trim($_POST["serial_number"] ?? '');
    $branch_of_service = trim($_POST["branch_of_service"] ?? '');
    $courses           = trim($_POST["courses"] ?? '');
    $year_graduated    = trim($_POST["year_graduated"] ?? '');
    $standing          = trim($_POST["standing"] ?? '');


    /*
     * ======================================================
     * VALIDATE REQUIRED FIELDS
     * ======================================================
     */

    if (
        empty($rank) ||
        empty($name) ||
        empty($serial_number) ||
        empty($branch_of_service) ||
        empty($courses) ||
        empty($year_graduated) ||
        empty($standing)
    ) {

        $errorMessage = "All the fields are required.";

    } else {

        try {

            /*
             * ==================================================
             * INSERT INTO POSTGRESQL
             * ==================================================
             *
             * Prepared statements are used instead of putting
             * user input directly into the SQL query.
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

            $errorMessage = "Unable to save the personnel information.";

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

  <title>Add Military Personnel</title>

  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
  >

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  ></script>


  <style>

    body {
      background: radial-gradient(circle at top left, #021631, #05254d);
      color: #eaeaea;
      font-family: "Poppins", sans-serif;
      min-height: 100vh;
      margin: 0;
      animation: fadeIn 1.2s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .paf-topbar {
      width: 100%;
      padding: 15px 40px;
      background: rgba(0, 40, 90, 0.45);
      backdrop-filter: blur(12px);
      border-bottom: 2px solid #ffd700;
      display: flex;
      align-items: center;
      box-shadow: 0 5px 20px rgba(0,0,0,0.6);
    }

    .paf-brand {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .paf-brand img {
      height: 70px;
      width: 70px;
      object-fit: contain;
      transition: 0.4s;
    }

    .paf-brand img:hover {
      transform: scale(1.15) rotate(5deg);
      filter: drop-shadow(0 0 12px #00b4d8);
    }

    .paf-text h1 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 700;
      color: #ffffff;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .paf-text span {
      font-size: 0.85rem;
      color: #ffd700;
    }

    .container-main {
      background: rgba(255,255,255,0.05);
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 0 25px rgba(0,0,0,0.7);
      margin-top: 40px;
      animation: slideUp 1s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h2 {
      text-align: center;
      font-weight: 600;
      margin-bottom: 30px;
      color: #58a6ff;
      text-shadow: 0 0 10px rgba(88,166,255,0.8);
    }

    .form-control,
    .form-select {
      background-color: #050b16;
      border: 1px solid #264b7c;
      color: #f1f1f1;
      transition: 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
      background-color: #071021;
      border-color: #ffd700;
      box-shadow: 0 0 8px rgba(255,215,0,0.7);
    }

    label {
      color: #e0e0e0;
      font-weight: 500;
    }

    .btn-primary {
      background-color: #0057b7;
      border: none;
      transition: all 0.3s ease;
      border-radius: 8px;
    }

    .btn-primary:hover {
      background-color: #003b88;
      box-shadow: 0 0 12px rgba(255,215,0,0.9);
      transform: scale(1.03);
    }

    .btn-outline-primary {
      color: #ffd700;
      border-color: #ffd700;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
      background-color: #ffd700;
      color: #021631;
      box-shadow: 0 0 10px rgba(255,215,0,0.8);
      transform: scale(1.03);
    }

    .alert {
      border: none;
      color: #fff;
      border-radius: 10px;
    }

    .alert-warning {
      background-color: rgba(255, 204, 72, 0.35);
      color: #000;
      border: 1px solid rgba(255, 193, 7, 0.8);
    }

    .alert-success {
      background-color: rgba(72, 207, 122, 0.35);
      color: #000;
      border: 1px solid rgba(40, 167, 69, 0.8);
    }

    .btn-close {
      filter: invert(1);
    }

    input.form-control,
    select.form-select,
    textarea.form-control {
      color: #ffffff !important;
      background-color: rgba(0, 0, 0, 0.35) !important;
    }

    input::placeholder,
    textarea::placeholder {
      color: rgba(255, 255, 255, 0.6) !important;
    }

    select.form-select option {
      color: #000 !important;
    }

    input.form-control:focus,
    select.form-select:focus {
      border-color: #ffd700 !important;
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.6) !important;
    }

    select.form-select {
      color: #ffffff !important;
      background-color: rgba(0, 0, 0, 0.35) !important;
      border: 1px solid #ffd700 !important;
    }

    select.form-select option {
      background-color: #1c2942 !important;
      color: #ffffff !important;
    }

    select.form-select option:checked,
    select.form-select option:hover {
      background-color: #324a78 !important;
      color: #ffffff !important;
    }

  </style>

</head>


<body>


  <!-- PAF HEADER -->

  <div class="paf-topbar">

    <div class="paf-brand">

      <img
        src="cmo1.png"
        alt="Philippine Air Force Logo"
      >

      <div class="paf-text">

        <h1>CMO Squadron Training</h1>

        <span>
          Student Database Information System
        </span>

      </div>

    </div>

  </div>


  <!-- MAIN CONTENT -->

  <div class="container container-main my-5">

    <h2>
      Add Military Personnel Information
    </h2>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($errorMessage)): ?>

      <div
        class="alert alert-warning alert-dismissible fade show"
        role="alert"
      >

        <strong>
          <?= htmlspecialchars($errorMessage); ?>
        </strong>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="alert"
          aria-label="Close"
        ></button>

      </div>

    <?php endif; ?>


    <!-- FORM -->

    <form method="post">


      <!-- RANK -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Rank
        </label>

        <div class="col-sm-6">

          <select
            class="form-select"
            name="rank"
            required
          >

            <option value="">
              -- Select Rank --
            </option>

            <option value="Airman Basic"
              <?= ($rank == 'Airman Basic') ? 'selected' : ''; ?>>
              Airman Basic
            </option>

            <option value="Airman"
              <?= ($rank == 'Airman') ? 'selected' : ''; ?>>
              Airman
            </option>

            <option value="Airman First Class"
              <?= ($rank == 'Airman First Class') ? 'selected' : ''; ?>>
              Airman First Class
            </option>

            <option value="Sergeant"
              <?= ($rank == 'Sergeant') ? 'selected' : ''; ?>>
              Sergeant
            </option>

            <option value="Technical Sergeant"
              <?= ($rank == 'Technical Sergeant') ? 'selected' : ''; ?>>
              Technical Sergeant
            </option>

            <option value="Master Sergeant"
              <?= ($rank == 'Master Sergeant') ? 'selected' : ''; ?>>
              Master Sergeant
            </option>

            <option value="Senior Master Sergeant"
              <?= ($rank == 'Senior Master Sergeant') ? 'selected' : ''; ?>>
              Senior Master Sergeant
            </option>

            <option value="Chief Master Sergeant"
              <?= ($rank == 'Chief Master Sergeant') ? 'selected' : ''; ?>>
              Chief Master Sergeant
            </option>

            <option value="Lieutenant"
              <?= ($rank == 'Lieutenant') ? 'selected' : ''; ?>>
              Lieutenant
            </option>

            <option value="Captain"
              <?= ($rank == 'Captain') ? 'selected' : ''; ?>>
              Captain
            </option>

            <option value="Major"
              <?= ($rank == 'Major') ? 'selected' : ''; ?>>
              Major
            </option>

            <option value="Lieutenant Colonel"
              <?= ($rank == 'Lieutenant Colonel') ? 'selected' : ''; ?>>
              Lieutenant Colonel
            </option>

            <option value="Colonel"
              <?= ($rank == 'Colonel') ? 'selected' : ''; ?>>
              Colonel
            </option>

            <option value="AW1C"
              <?= ($rank == 'AW1C') ? 'selected' : ''; ?>>
              AW1C
            </option>

            <option value="A1C"
              <?= ($rank == 'A1C') ? 'selected' : ''; ?>>
              A1C
            </option>

            <option value="A2C"
              <?= ($rank == 'A2C') ? 'selected' : ''; ?>>
              A2C
            </option>

            <option value="Staff Sargent"
              <?= ($rank == 'Staff Sargent') ? 'selected' : ''; ?>>
              Staff Sargent
            </option>

          </select>

        </div>

      </div>


      <!-- NAME -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Name
        </label>

        <div class="col-sm-6">

          <input
            type="text"
            class="form-control"
            name="name"
            value="<?= htmlspecialchars($name); ?>"
            required
          >

        </div>

      </div>


      <!-- SERIAL NUMBER -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Serial Number
        </label>

        <div class="col-sm-6">

          <input
            type="text"
            class="form-control"
            name="serial_number"
            value="<?= htmlspecialchars($serial_number); ?>"
            required
          >

        </div>

      </div>


      <!-- BRANCH -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Branch of Service
        </label>

        <div class="col-sm-6">

          <select
            class="form-select"
            name="branch_of_service"
            required
          >

            <option value="">
              -- Select Branch --
            </option>

            <option value="Philippine Air Force"
              <?= ($branch_of_service == 'Philippine Air Force') ? 'selected' : ''; ?>>
              Philippine Air Force
            </option>

            <option value="Philippine Army"
              <?= ($branch_of_service == 'Philippine Army') ? 'selected' : ''; ?>>
              Philippine Army
            </option>

            <option value="Philippine Navy"
              <?= ($branch_of_service == 'Philippine Navy') ? 'selected' : ''; ?>>
              Philippine Navy
            </option>

            <option value="Reserved Force"
              <?= ($branch_of_service == 'Reserved Force') ? 'selected' : ''; ?>>
              Reserved Force
            </option>

            <option value="Others"
              <?= ($branch_of_service == 'Others') ? 'selected' : ''; ?>>
              Others
            </option>

          </select>

        </div>

      </div>


      <!-- COURSES -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Course/s
        </label>

        <div class="col-sm-6">

          <input
            type="text"
            class="form-control"
            name="courses"
            value="<?= htmlspecialchars($courses); ?>"
            required
          >

        </div>

      </div>


      <!-- YEAR GRADUATED -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Year Graduated
        </label>

        <div class="col-sm-6">

          <select
            class="form-select"
            name="year_graduated"
            required
          >

            <option value="">
              -- Select Year --
            </option>

            <?php

            $currentYear = date('Y');

           for ($y = $currentYear; $y >= 1960; $y--) {

                $selected =
                    ($year_graduated == $y)
                    ? 'selected'
                    : '';

                echo "
                <option value=\"$y\" $selected>
                    $y
                </option>
                ";

            }

            ?>

          </select>

        </div>

      </div>


      <!-- STANDING -->

      <div class="row mb-3">

        <label class="col-sm-3 col-form-label">
          Standing
        </label>

        <div class="col-sm-6">

          <input
            type="text"
            class="form-control"
            name="standing"
            value="<?= htmlspecialchars($standing); ?>"
            required
          >

        </div>

      </div>


      <!-- BUTTONS -->

      <div class="row mb-3">

        <div class="offset-sm-3 col-sm-6 d-flex justify-content-between">

          <button
            type="submit"
            class="btn btn-primary px-4"
          >
            Submit
          </button>

          <a
            class="btn btn-outline-primary px-4"
            href="index.php"
            role="button"
          >
            Cancel
          </a>

        </div>

      </div>


    </form>

  </div>


</body>

</html>