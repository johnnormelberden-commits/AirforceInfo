<?php
$servername   = "localhost";
$username     = "root";
$password     = "";
$database     = "airforce_info";

$connection = new mysqli($servername, $username, $password, $database);

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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET["id"])) {
        header("location: /airforceinfo/index.php");
        exit;
    }

    $id = $_GET["id"];

    $sql    = "SELECT * FROM military_personnel WHERE id = $id";
    $result = $connection->query($sql);
    $row    = $result->fetch_assoc();

    if (!$row) {
        header("location: /airforceinfo/index.php");
        exit;
    }

    $rank              = $row["rank"];
    $name              = $row["name"];
    $serial_number     = $row["serial_number"];
    $branch_of_service = $row["branch_of_service"];
    $courses           = $row["courses"];
    $year_graduated    = $row["year_graduated"];
    $standing          = $row["standing"];

} else {

    $id                = $_POST["id"];
    $rank              = $_POST["rank"];
    $name              = $_POST["name"];
    $serial_number     = $_POST["serial_number"];
    $branch_of_service = $_POST["branch_of_service"];
    $courses           = $_POST["courses"];
    $year_graduated    = $_POST["year_graduated"];
    $standing          = $_POST["standing"];

    do {
        if (
            empty($id) || empty($rank) || empty($name) || empty($serial_number) ||
            empty($branch_of_service) || empty($courses) || empty($year_graduated) || empty($standing)
        ) {
            $errorMessage = "All fields are required.";
            break;
        }

        $sql = "UPDATE military_personnel SET
                    rank = '$rank',
                    name = '$name',
                    serial_number = '$serial_number',
                    branch_of_service = '$branch_of_service',
                    courses = '$courses',
                    year_graduated = '$year_graduated',
                    standing = '$standing'
                WHERE id = $id";

        $result = $connection->query($sql);

        if (!$result) {
            $errorMessage = "Invalid query: " . $connection->error;
            break;
        }

        $successMessage = "Military personnel information updated successfully!";
        header("location: /airforceinfo/index.php");
        exit;

    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Philippine Air Force – Edit Personnel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

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
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Top PAF banner */
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

    label {
      color: #d0e2ff;
      transition: color 0.3s;
      font-weight: 500;
    }

    .form-control, .form-select {
      background-color: #050b16;
      border: 1px solid #264b7c;
      color: #e6edf3;
      transition: all 0.3s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
      background-color: #071021;
      border-color: #ffd700;
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.6);
      transform: scale(1.02);
    }

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

    .alert {
      border-radius: 10px;
      animation: fadeInAlert 0.5s ease-in-out;
    }

    @keyframes fadeInAlert {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .container-main {
      animation: slideIn 0.8s ease-in-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
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

  <!-- Top Philippine Air Force banner -->
  <div class="paf-header">
    <img src="cmo1.png" alt="Philippine Air Force Logo">
    <div class="paf-header-text">
      <h1>CMO Squadron Training</h1>
      <span>Student Database Information System</span>
    </div>
  </div>

  <div class="container container-main my-5 d-flex justify-content-center">
    <div class="card w-75">
      <div class="card-header">
        <div class="title-text">
          <span>✏️ Update Student Database Information</span>
        </div>
      </div>

      <div class="card-body p-4">

        <?php if (!empty($errorMessage)): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><?= $errorMessage ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

          <!-- Rank dropdown -->
          <div class="mb-3">
            <label class="form-label">Rank</label>
            <select class="form-select" name="rank">
              <option value="">-- Select Rank --</option>
              <option value="Airman Basic"           <?= ($rank == 'Airman Basic') ? 'selected' : '' ?>>Airman Basic</option>
              <option value="Airman"                 <?= ($rank == 'Airman') ? 'selected' : '' ?>>Airman</option>
              <option value="Airman First Class"     <?= ($rank == 'Airman First Class') ? 'selected' : '' ?>>Airman First Class</option>
              <option value="Sergeant"               <?= ($rank == 'Sergeant') ? 'selected' : '' ?>>Sergeant</option>
              <option value="Technical Sergeant"     <?= ($rank == 'Technical Sergeant') ? 'selected' : '' ?>>Technical Sergeant</option>
              <option value="Master Sergeant"        <?= ($rank == 'Master Sergeant') ? 'selected' : '' ?>>Master Sergeant</option>
              <option value="Senior Master Sergeant" <?= ($rank == 'Senior Master Sergeant') ? 'selected' : '' ?>>Senior Master Sergeant</option>
              <option value="Chief Master Sergeant"  <?= ($rank == 'Chief Master Sergeant') ? 'selected' : '' ?>>Chief Master Sergeant</option>
              <option value="Lieutenant"             <?= ($rank == 'Lieutenant') ? 'selected' : '' ?>>Lieutenant</option>
              <option value="Captain"                <?= ($rank == 'Captain') ? 'selected' : '' ?>>Captain</option>
              <option value="Major"                  <?= ($rank == 'Major') ? 'selected' : '' ?>>Major</option>
              <option value="Lieutenant Colonel"     <?= ($rank == 'Lieutenant Colonel') ? 'selected' : '' ?>>Lieutenant Colonel</option>
              <option value="Colonel"                <?= ($rank == 'Colonel') ? 'selected' : '' ?>>Colonel</option>
              <option value="AW1C"                <?= ($rank == 'AW1C') ? 'selected' : '' ?>>AW1C</option>
              <option value="A1C"                <?= ($rank == 'A1C') ? 'selected' : '' ?>>A1C</option>
              <option value="A2C"                <?= ($rank == 'A2C') ? 'selected' : '' ?>>A2C</option>
            </select>
          </div>

          <!-- Name -->
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>">
          </div>

          <!-- Serial Number -->
          <div class="mb-3">
            <label class="form-label">Serial Number</label>
            <input type="text" class="form-control" name="serial_number" value="<?= htmlspecialchars($serial_number) ?>">
          </div>

          <!-- Branch of Service dropdown -->
          <div class="mb-3">
            <label class="form-label">Branch of Service</label>
            <select class="form-select" name="branch_of_service">
              <option value="">-- Select Branch --</option>
              <option value="Philippine Air Force" <?= ($branch_of_service == 'Philippine Air Force') ? 'selected' : '' ?>>Philippine Air Force</option>
              <option value="Philippine Army"      <?= ($branch_of_service == 'Philippine Army') ? 'selected' : '' ?>>Philippine Army</option>
              <option value="Philippine Navy"      <?= ($branch_of_service == 'Philippine Navy') ? 'selected' : '' ?>>Philippine Navy</option>
              <option value="Reserved Force"       <?= ($branch_of_service == 'Reserved Force') ? 'selected' : '' ?>>Reserved Force</option>
              <option value="Others"               <?= ($branch_of_service == 'Others') ? 'selected' : '' ?>>Others</option>
            </select>
          </div>

          <!-- Course/s -->
          <div class="mb-3">
            <label class="form-label">Course/s</label>
            <input type="text" class="form-control" name="courses" value="<?= htmlspecialchars($courses) ?>">
          </div>

          <!-- Year Graduated dropdown -->
          <div class="mb-3">
            <label class="form-label">Year Graduated</label>
            <select class="form-select" name="year_graduated">
              <option value="">-- Select Year --</option>
              <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 1960; $y--) {
                    $selected = ($year_graduated == $y) ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
              ?>
            </select>
          </div>

          <!-- Standing -->
          <div class="mb-3">
            <label class="form-label">Standing</label>
            <input type="text" class="form-control" name="standing" value="<?= htmlspecialchars($standing) ?>">
          </div>

          <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <strong><?= $successMessage ?></strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary px-4">Update</button>
            <a href="/airforceinfo/index.php" class="btn btn-outline-primary px-4">Cancel</a>
          </div>
        </form>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
