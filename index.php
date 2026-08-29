<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success">
  ✅ Record deleted successfully.
</div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Philippine Air Force - Personnel System</title>

  <!-- Bootstrap + DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <!-- DataTables Buttons CSS for Excel/PDF/Print -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <style>
    body {
      background: linear-gradient(135deg, #0d1117, #1b2838);
      color: #e0e0e0;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      margin: 0;
    }

    /* ===== PAF HEADER ===== */
    .paf-topbar {
      width: 100%;
      padding: 15px 40px;
      background: rgba(0, 40, 90, 0.45);
      backdrop-filter: blur(12px);
      border-bottom: 2px solid #ffd700;
      display: flex;
      align-items: center;
      justify-content: space-between; /* brand left, controls right */
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
      font-size: 1.5rem;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .paf-text span {
      font-size: 0.9rem;
      color: #ffd700;
    }

    .logout-btn {
      font-size: 0.9rem;
      padding: 6px 14px;
      border-radius: 20px;
    }

    .export-menu-btn {
      font-size: 18px;
      padding: 6px 10px;
      border-radius: 50%;
      line-height: 1;
    }

    /* ===== MAIN CONTAINER ===== */
    .container-main {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      margin-top: 40px;
    }

    h2 {
      color: #58a6ff;
      font-weight: 600;
      margin-bottom: 25px;
    }

    .btn-primary {
      background: linear-gradient(90deg, #007bff, #00b4d8);
      border: none;
      transition: 0.3s;
    }

    .btn-primary:hover {
      opacity: 0.9;
    }

    /* ===== TABLE STYLES ===== */
    .table {
      color: #e0e0e0;
      background-color: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
      overflow: hidden;
    }

    .table th {
      background-color: rgba(0, 123, 255, 0.2);
      color: #58a6ff;
      text-transform: uppercase;
    }

    .table td, .table th {
      text-align: center;
      vertical-align: middle;
    }

    .no-wrap {
      white-space: nowrap;
      min-width: 180px;
    }

    /* Hide default DataTables buttons – we use burger menu instead */
    .dt-buttons {
      display: none !important;
      
    }

    /* Align Show entries LEFT */
.dataTables_length {
  float: left;
  margin-bottom: 20px;
}

/* Align Search bar RIGHT */
.dataTables_filter {
  float: right;
  text-align: right;
}

/* Force both on the same row */
.dataTables_wrapper .row:nth-child(1) {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
 
    
  </style>
</head>
<body>

<!-- ✅ PHILIPPINE AIR FORCE HEADER -->
<div class="paf-topbar">

  <!-- LEFT SIDE : LOGO + TEXT -->
  <div class="paf-brand">
    <img src="cmo1.png" alt="Philippine Air Force Logo">
    <div class="paf-text">
      <h1>CMO Training Squadron</h1>
      <span>Student Database Information System</span>
    </div>
  </div>

  <!-- RIGHT SIDE : BURGER + CHANGE PASSWORD + LOGOUT -->
  <div class="d-flex align-items-center gap-2">

    <!-- 🍔 Export Burger Menu -->
    <div class="dropdown">
      <button class="btn btn-outline-info btn-sm export-menu-btn"
              type="button"
              id="exportMenuBtn"
              data-bs-toggle="dropdown"
              aria-expanded="false">
        &#9776;
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportMenuBtn">
        <li><a class="dropdown-item" href="#" id="exportExcel">Export to Excel</a></li>
        <li><a class="dropdown-item" href="#" id="exportPDF">Export to PDF</a></li>
        <li><a class="dropdown-item" href="#" id="exportPrint">Print Table</a></li>
      </ul>
    </div>

    <!-- 🔑 Change Password -->
    <a href="/airforceinfo/change_password.php" class="btn btn-outline-warning btn-sm">
      Change Password
    </a>

    <!-- 🚪 Logout -->
    <a href="/airforceinfo/logout.php" class="btn btn-outline-light btn-sm logout-btn">
      Logout
    </a>

  </div>
</div>

<div class="container container-main">

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
      ✅ Record deleted successfully.
    </div>
  <?php endif; ?>

  <h2>Military Personnel Information</h2>
  <a class="btn btn-primary mb-3" href="/airforceinfo/create.php">Add New Military Personnel Information</a>

  <div class="table-responsive">
    <table id="personnelTable" class="table table-hover table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Rank</th>
          <th>Name</th>
          <th>Serial Number</th>
          <th>Branch of Service</th>
          <th>Courses</th>
          <th>Year Graduated</th>
          <th>Standing</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $connection = new mysqli("localhost", "root", "", "airforce_info");

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $sql = "SELECT * FROM military_personnel";
        $result = $connection->query($sql);

        if (!$result) {
            die("Invalid query: " . $connection->error);
        }

        while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['rank']); ?></td>
            <td class="no-wrap"><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['serial_number']); ?></td>
            <td><?= htmlspecialchars($row['branch_of_service']); ?></td>
            <td><?= htmlspecialchars($row['courses']); ?></td>
            <td><?= htmlspecialchars($row['year_graduated']); ?></td>
            <td><?= htmlspecialchars($row['standing']); ?></td>
            <td><?= date('F d, Y h:i A', strtotime($row['created_at'])); ?></td>
            <td><?= date('F d, Y h:i A', strtotime($row['updated_at'])); ?></td>
            <td>
              <a class="btn btn-primary btn-sm" href="/airforceinfo/edit.php?id=<?= $row['id']; ?>">Update</a>
              <!-- Delete uses confirmation modal -->
              <button 
                type="button"
                class="btn btn-danger btn-sm btn-delete"
                data-id="<?= $row['id']; ?>"
                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES); ?>">
                Delete
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 🧨 DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color:#0f1724; color:#e6edf3; border:1px solid #1f3b63;">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this record?<br>
        <strong id="deletePersonName" style="color:#ffd700;"></strong>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>

<!-- 🔧 SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons + dependencies (Excel/PDF/Print) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
  // Initialize DataTable WITH "Show entries" dropdown
  var table = $('#personnelTable').DataTable({
    order: [[0, 'asc']],
    pageLength: 5,
    lengthMenu: [5, 10, 25, 50],

    // ✅ Show "Show X entries" + buttons + filter + table + info + pagination
    dom: 'lBfrtip',

    buttons: [
      {
        extend: 'excelHtml5',
        title: 'Military Personnel Information'
      },
      {
        extend: 'pdfHtml5',
        title: 'Military Personnel Information',
        orientation: 'landscape',
        pageSize: 'A4'
      },
      {
        extend: 'print',
        title: 'Military Personnel Information'
      }
    ]
  });

  // 🧨 Export dropdown handlers (burger menu)
  $('#exportExcel').on('click', function (e) {
    e.preventDefault();
    table.button('.buttons-excel').trigger();
  });

  $('#exportPDF').on('click', function (e) {
    e.preventDefault();
    table.button('.buttons-pdf').trigger();
  });

  $('#exportPrint').on('click', function (e) {
    e.preventDefault();
    table.button('.buttons-print').trigger();
  });

  // Modal + delete logic
  const deleteModalEl = document.getElementById('deleteModal');
  const deleteModal   = new bootstrap.Modal(deleteModalEl);
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const deletePersonName = document.getElementById('deletePersonName');

  $('#personnelTable').on('click', '.btn-delete', function () {
    const id   = $(this).data('id');
    const name = $(this).data('name');

    deletePersonName.textContent = name ? 'Personnel: ' + name : '';
    confirmDeleteBtn.href = '/airforceinfo/delete.php?id=' + id;

    deleteModal.show();
  });
});
</script>

</body>
</html>
