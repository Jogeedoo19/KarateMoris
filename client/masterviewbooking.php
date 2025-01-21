<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Fetch the master's dojo
$dojoStmt = $pdo->prepare("
    SELECT dojo_id, name 
    FROM dojo 
    WHERE master_id = ?
");
$dojoStmt->execute([$master_id]);
$dojos = $dojoStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dojos)) {
    $_SESSION['error'] = "No dojos assigned to this master.";
    header("Location: index.php");
    exit();
}

// Get selected dojo (default to first dojo if none selected)
$selected_dojo = isset($_GET['dojo_id']) ? (int)$_GET['dojo_id'] : $dojos[0]['dojo_id'];

// Get date filter
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'upcoming';

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the date condition based on filter
switch($date_filter) {
    case 'past':
        $date_condition = "b.booking_date < CURDATE()";
        break;
    case 'today':
        $date_condition = "b.booking_date = CURDATE()";
        break;
    case 'all':
        $date_condition = "1=1";
        break;
    case 'upcoming':
    default:
        $date_condition = "b.booking_date >= CURDATE()";
        break;
}

// Add search condition if search is provided
$search_condition = "";
$params = [$selected_dojo];
if ($search) {
    $search_condition = " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

// Fetch bookings for the selected dojo
$bookingStmt = $pdo->prepare("
    SELECT 
        b.book_id,
        b.booking_date,
        u.firstname,
        u.lastname,
        u.email,
        m.amount,
        cm.name as membership_type,
        d.name as dojo_name
    FROM booking b
    JOIN user u ON b.user_id = u.user_id
    JOIN membership m ON b.mem_id = m.mem_id
    JOIN categorymember cm ON m.catmember_id = cm.catmember_id
    JOIN dojo d ON b.dojo_id = d.dojo_id
    WHERE b.dojo_id = ? 
    AND $date_condition
    $search_condition
    ORDER BY b.booking_date ASC
");
$bookingStmt->execute($params);
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_bookings = count($bookings);
$total_revenue = array_sum(array_column($bookings, 'amount'));
$unique_students = count(array_unique(array_map(function($booking) {
    return $booking['email'];
}, $bookings)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Bookings Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .booking-filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .booking-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-past { background-color: #dc3545; }
        .status-today { background-color: #ffc107; }
        .status-upcoming { background-color: #28a745; }
        .stats-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .stats-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../files/nav.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Bookings Management</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Total Bookings</h3>
                    <div class="value"><?= $total_bookings ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Total Revenue</h3>
                    <div class="value">Rs. <?= number_format($total_revenue, 2) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Unique Students</h3>
                    <div class="value"><?= $unique_students ?></div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="booking-filters">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="dojo_id" class="form-label">Select Dojo</label>
                    <select name="dojo_id" id="dojo_id" class="form-select">
                        <?php foreach ($dojos as $dojo): ?>
                            <option value="<?= $dojo['dojo_id'] ?>" 
                                <?= $selected_dojo == $dojo['dojo_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dojo['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_filter" class="form-label">Date Filter</label>
                    <select name="date_filter" id="date_filter" class="form-select">
                        <option value="upcoming" <?= $date_filter == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="past" <?= $date_filter == 'past' ? 'selected' : '' ?>>Past</option>
                        <option value="all" <?= $date_filter == 'all' ? 'selected' : '' ?>>All</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by name or email">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="booking-table">
            <div class="table-responsive">
                <table id="bookingsTable" class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Membership</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No bookings found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                $booking_date = strtotime($booking['booking_date']);
                                $today = strtotime('today');
                                
                                if ($booking_date < $today) {
                                    $status_class = 'status-past';
                                    $status_text = 'Past';
                                } elseif ($booking_date == $today) {
                                    $status_class = 'status-today';
                                    $status_text = 'Today';
                                } else {
                                    $status_class = 'status-upcoming';
                                    $status_text = 'Upcoming';
                                }
                                ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']) ?></td>
                                    <td><?= htmlspecialchars($booking['email']) ?></td>
                                    <td><?= htmlspecialchars($booking['membership_type']) ?></td>
                                    <td>Rs. <?= number_format($booking['amount'], 2) ?></td>
                                    <td>
                                        <span class="status-indicator <?= $status_class ?>"></span>
                                        <?= $status_text ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="viewDetails(<?= $booking['book_id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($status_text === 'Upcoming'): ?>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="cancelBooking(<?= $booking['book_id'] ?>)">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="mt-4">
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </button>
            <button class="btn btn-danger" onclick="exportToPDF()">
                <i class="bi bi-file-earmark-pdf"></i> Export to PDF
            </button>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

    <script>
        function viewDetails(bookingId) {
            // Fetch booking details
            fetch(`get_booking_details.php?id=${bookingId}`)
                .then(response => response.json())
                .then(data => {
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <div class="booking-details">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Student Name</dt>
                                <dd class="col-sm-8">${data.firstname} ${data.lastname}</dd>
                                
                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">${data.email}</dd>
                                
                                <dt class="col-sm-4">Booking Date</dt>
                                <dd class="col-sm-8">${data.booking_date}</dd>
                                
                                <dt class="col-sm-4">Dojo</dt>
                                <dd class="col-sm-8">${data.dojo_name}</dd>
                                
                                <dt class="col-sm-4">Membership</dt>
                                <dd class="col-sm-8">${data.membership_type}</dd>
                                
                                <dt class="col-sm-4">Amount Paid</dt>
                                <dd class="col-sm-8">Rs. ${data.amount}</dd>
                            </dl>
                        </div>
                    `;
                    
                    // Show the modal
                    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load booking details');
                });
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                fetch('cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `booking_id=${bookingId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking cancelled successfully');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to cancel booking');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to cancel booking');
                });
            }
        }

        function exportToExcel() {
            const table = document.querySelector('#bookingsTable');
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Add some styling
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let i = range.s.r; i <= range.e.r; i++) {
                for (let j = range.s.c; j <= range.e.c; j++) {
                    const cell_address = XLSX.utils.encode_cell({ r: i, c: j });
                    if (!ws[cell_address]) continue;
                    
                    // Add cell styling here if needed
                    ws[cell_address].s = {
                        font: { bold: i === 0 },
                        alignment: { horizontal: "left" }
                    };
                }
            }
            
            XLSX.utils.book_append_sheet(wb, ws, "Bookings");
            XLSX.writeFile(wb, `bookings_report_${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(16);
            doc.text('Bookings Report', 14, 20);
            
            // Add timestamp
            doc.setFontSize(10);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 30);
            
            // Add table
            doc.autoTable({
                html: '#bookingsTable',
                startY: 35,
                styles: { fontSize: 8 },
                columnStyles: { 0: { cellWidth: 25 } },
                didDrawPage: function(data) {
                    // Add page numbers
                    doc.text(
                        `Page ${doc.internal.getCurrentPageInfo().pageNumber}/${doc.internal.getNumberOfPages()}`,
                        data.settings.margin.left,
                        doc.internal.pageSize.height - 10
                    );
                }
            });
            
            // Save the PDF
            doc.save(`bookings_report_${new Date().toISOString().split('T')[0]}.pdf`);
        }
    </script>
</body>
</html>