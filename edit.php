<?php

/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 *
 * These values come from your Render Environment Variables:
 *
 * DB_HOST
 * DB_PORT
 * DB_NAME
 * DB_USER
 * DB_PASSWORD
 *
 * Do NOT put your database password directly in this file.
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

    $connection->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (Exception $e) {

    /*
     * Do not expose database credentials or
     * detailed connection information.
     */
    die("Unable to connect to the database.");

}


/*
 * ==========================================================
 * FORM VARIABLES
 * ==========================================================
 */

$id                = "";
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
 * GET EXISTING RECORD
 * ==========================================================
 */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /*
     * Check if ID was provided.
     */
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

        header("Location: index.php");
        exit;

    }

    $id = (int) $_GET['id'];

    try {

        /*
         * Prepared statement for security.
         */
        $stmt = $connection->prepare(
            "SELECT
                id,
                rank,
                name,
                serial_number,
                branch_of_service,
                courses,
                year_graduated,
                standing
             FROM military_personnel
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        $row = $stmt->fetch();

        /*
         * Record does not exist.
         */
        if (!$row) {

            header("Location: index.php");
            exit;

        }

        /*
         * Put database values into the form.
         */
        $rank              = $row['rank'];
        $name              = $row['name'];
        $serial_number     = $row['serial_number'];
        $branch_of_service = $row['branch_of_service'];
        $courses           = $row['courses'];
        $year_graduated    = $row['year_graduated'];
        $standing          = $row['standing'];

    } catch (PDOException $e) {

        $errorMessage = "Unable to load the personnel information.";

    }


/*
 * ==========================================================
 * HANDLE UPDATE FORM
 * ==========================================================
 */

} else {

    /*
     * Get submitted values.
     */
    $id                = $_POST['id'] ?? '';
    $rank              = trim($_POST['rank'] ?? '');
    $name              = trim($_POST['name'] ?? '');
    $serial_number     = trim($_POST['serial_number'] ?? '');
    $branch_of_service = trim($_POST['branch_of_service'] ?? '');
    $courses           = trim($_POST['courses'] ?? '');
    $year_graduated    = trim($_POST['year_graduated'] ?? '');
    $standing          = trim($_POST['standing'] ?? '');


    /*
     * ======================================================
     * VALIDATE ID
     * ======================================================
     */

    if (!is_numeric($id)) {

        $errorMessage = "Invalid personnel ID.";

    } else {

        $id = (int) $id;


        /*
         * ==================================================
         * VALIDATE REQUIRED FIELDS
         * ==================================================
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

            $errorMessage = "All fields are required.";

        } else {

            try {

                /*
                 * ==================================================
                 * UPDATE POSTGRESQL RECORD
                 * ==================================================
                 *
                 * Prepared statements protect against SQL injection.
                 */

               $sql = "
     UPDATE military_personnel
    SET
        rank = :rank,
        name = :name,
        serial_number = :serial_number,
        branch_of_service = :branch_of_service,
        courses = :courses,
        year_graduated = :year_graduated,
        standing = :standing,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
";

                $stmt = $connection->prepare($sql);

                $stmt->execute([
                    ':rank'              => $rank,
                    ':name'              => $name,
                    ':serial_number'     => $serial_number,
                    ':branch_of_service' => $branch_of_service,
                    ':courses'           => $courses,
                    ':year_graduated'    => $year_graduated,
                    ':standing'          => $standing,
                    ':id'                => $id
                ]);


                /*
                 * ==================================================
                 * SUCCESS
                 * ==================================================
                 *
                 * Your PostgreSQL table has updated_at configured
                 * with ON UPDATE behavior from the original table
                 * structure. If needed, we can also handle it
                 * explicitly in PostgreSQL.
                 */

                header("Location: index.php");
                exit;

            } catch (PDOException $e) {

                $errorMessage =
                    "Unable to update the personnel information.";

            }

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

  <title>Philippine Air Force – Edit Personnel</title>

  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
  >

  <style>

    body {
      background: radial-gradient(circle at top left, #021631, #05254d);
      color: #e6edf3;
      font-family: "Segoe UI", sans-serif;
      overflow-x: hidden;
      animation: fadeInBody 0.8s ease-in-out;
      margin: 0;
    }

    @keyframes fadeInBody {

      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }

    }

    /* ======================================================
       TOP PAF HEADER
       ====================================================== */

    .paf-header {

      background: linear-gradient(90deg, #002b6b, #0057b7);
      border-bottom: 3px solid #ffd700;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
      color: #ffffff;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);

    }

    .paf-header img {

      height: 60px;
      width: 60px;
      object-fit: contain;

    }

    .paf-header-text h1 {

      font-size: 1.4rem;
      margin: 0;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;

    }

    .paf-header-text span {

      font-size: 0.85rem;
      opacity: 0.9;

    }


    /* ======================================================
       CARD
       ====================================================== */

    .card {

      background-color: #0f1724;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
      border: 1px solid #1f3b63;
      transform: scale(0.97);
      opacity: 0;
      animation: fadeInCard 0.9s ease-in-out forwards;

    }

    @keyframes fadeInCard {

      to {

        transform: scale(1);
        opacity: 1;

      }

    }


    /* ======================================================
       CARD HEADER
       ====================================================== */

    .card-header {

      background: linear-gradient(90deg, #003b88, #0057b7);
      color: #fff;
      font-size: 1.15rem;
      font-weight: 600;
      text-align: left;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      padding: 14px 20px;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #ffd700;
      display: flex;
      align-items: center;
      justify-content: space-between;

    }

    .card-header .title-text {

      display: flex;
      align-items: center;
      gap: 8px;

    }

    .card-header .title-text span {

      font-size: 1.1rem;

    }


    /* ======================================================
       LABELS
       ====================================================== */

    label {

      color: #d0e2ff;
      transition: color 0.3s;
      font-weight: 500;

    }


    /* ======================================================
       FORM CONTROLS
       ====================================================== */

    .form-control,
    .form-select {

      background-color: #050b16;
      border: 1px solid #264b7c;
      color: #e6edf3;
      transition: all 0.3s ease-in-out;

    }

    .form-control:focus,
    .form-select:focus {

      background-color: #071021;
      border-color: #ffd700;
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.6);
      transform: scale(1.02);

    }


    /* ======================================================
       BUTTONS
       ====================================================== */

    .btn-primary {

      background-color: #0057b7;
      border: none;
      border-radius: 8px;
      transition: all 0.3s;

    }

    .btn-primary:hover {

      background-color: #003b88;
      box-shadow: 0 0 12px rgba(255, 215, 0, 0.9);
      transform: scale(1.05);

    }

    .btn-outline-primary {

      border: 1px solid #ffd700;
      color: #ffd700;
      border-radius: 8px;
      transition: all 0.3s;

    }

    .btn-outline-primary:hover {

      background-color: #ffd700;
      color: #001234;
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);

    }


    /* ======================================================
       ALERT
       ====================================================== */

    .alert {

      border-radius: 10px;
      animation: fadeInAlert 0.5s ease-in-out;

    }

    @keyframes fadeInAlert {

      from {

        opacity: 0;
        transform: translateY(-10px);

      }

      to {

        opacity: 1;
        transform: translateY(0);

      }

    }


    /* ======================================================
       CONTAINER ANIMATION
       ====================================================== */

    .container-main {

      animation: slideIn 0.8s ease-in-out;

    }

    @keyframes slideIn {

      from {

        opacity: 0;
        transform: translateY(30px);

      }

      to {

        opacity: 1;
        transform: translateY(0);

      }

    }


    /* ======================================================
       INPUT COLORS
       ====================================================== */

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


    /* ======================================================
       SELECT OPTIONS
       ====================================================== */

    select.form-select option {

      color: #ffffff !important;
      background-color: #1c2942 !important;

    }

    select.form-select option:checked,
    select.form-select option:hover {

      background-color: #324a78 !important;
      color: #ffffff !important;

    }

    select.form-select {

      color: #ffffff !important;
      background-color: rgba(0, 0, 0, 0.35) !important;
      border: 1px solid #ffd700 !important;

    }

  </style>

</head>


<body>


  <!-- ======================================================
       TOP PHILIPPINE AIR FORCE HEADER
       ====================================================== -->

  <div class="paf-header">

    <img
      src="cmo1.png"
      alt="Philippine Air Force Logo"
    >

    <div class="paf-header-text">

      <h1>
        CMO Squadron Training
      </h1>

      <span>
        Student Database Information System
      </span>

    </div>

  </div>


  <!-- ======================================================
       MAIN CONTENT
       ====================================================== -->

  <div
    class="container container-main my-5 d-flex justify-content-center"
  >

    <div class="card w-75">


      <!-- CARD HEADER -->

      <div class="card-header">

        <div class="title-text">

          <span>
            ✏️ Update Student Database Information
          </span>

        </div>

      </div>


      <!-- CARD BODY -->

      <div class="card-body p-4">


        <!-- ==================================================
             ERROR MESSAGE
             ================================================== -->

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


        <!-- ==================================================
             FORM
             ================================================== -->

        <form method="post">

          <!-- Hidden ID -->

          <input
            type="hidden"
            name="id"
            value="<?= htmlspecialchars((string)$id); ?>"
          >


          <!-- ==================================================
               RANK
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Rank
            </label>

            <select
              class="form-select"
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


          <!-- ==================================================
               NAME
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Name
            </label>

            <input
              type="text"
              class="form-control"
              name="name"
              value="<?= htmlspecialchars($name); ?>"
              required
            >

          </div>


          <!-- ==================================================
               SERIAL NUMBER
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Serial Number
            </label>

            <input
              type="text"
              class="form-control"
              name="serial_number"
              value="<?= htmlspecialchars($serial_number); ?>"
              required
            >

          </div>


          <!-- ==================================================
               BRANCH OF SERVICE
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Branch of Service
            </label>

            <select
              class="form-select"
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


          <!-- ==================================================
               COURSES
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Course/s
            </label>

            <input
              type="text"
              class="form-control"
              name="courses"
              value="<?= htmlspecialchars($courses); ?>"
              required
            >

          </div>


          <!-- ==================================================
               YEAR GRADUATED
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Year Graduated
            </label>

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
                      ((string)$year_graduated === (string)$y)
                      ? 'selected'
                      : '';

                  echo
                      '<option value="' .
                      htmlspecialchars((string)$y) .
                      '" ' .
                      $selected .
                      '>' .
                      htmlspecialchars((string)$y) .
                      '</option>';

              }

              ?>

            </select>

          </div>


          <!-- ==================================================
               STANDING
               ================================================== -->

          <div class="mb-3">

            <label class="form-label">
              Standing
            </label>

            <input
              type="text"
              class="form-control"
              name="standing"
              value="<?= htmlspecialchars($standing); ?>"
              required
            >

          </div>


          <!-- ==================================================
               SUCCESS MESSAGE
               ================================================== -->

          <?php if (!empty($successMessage)): ?>

            <div
              class="alert alert-success alert-dismissible fade show"
              role="alert"
            >

              <strong>
                <?= htmlspecialchars($successMessage); ?>
              </strong>

              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
              ></button>

            </div>

          <?php endif; ?>


          <!-- ==================================================
               BUTTONS
               ================================================== -->

          <div class="d-flex justify-content-between mt-4">

            <button
              type="submit"
              class="btn btn-primary px-4"
            >
              Update
            </button>

            <a
              href="index.php"
              class="btn btn-outline-primary px-4"
            >
              Cancel
            </a>

          </div>

        </form>

      </div>

    </div>

  </div>


  <!-- ======================================================
       BOOTSTRAP
       ====================================================== -->

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  ></script>

</body>

</html>