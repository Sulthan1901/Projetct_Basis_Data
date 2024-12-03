<?php
require 'db.php'; // Include database connection

session_start();

$showModal = false; // Flag to show modal
$message = ''; // Message to display in the modal

// Redirect to sign-in page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $message = "You must be logged in to register for an exam.";
    $showModal = true;
    header("Location: sign.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected exam and session ID
    $exam_id = $_POST['exam_id'];
    $sesi_id = $_POST['sesi_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Fetch session details
        $stmt_sesi = $pdo->prepare("SELECT * FROM sesi WHERE id_sesi = :sesi_id");
        $stmt_sesi->execute(['sesi_id' => $sesi_id]);
        $sesi = $stmt_sesi->fetch(PDO::FETCH_ASSOC);

        if ($sesi) {
            // Get the kapasitas (capacity) of the session
            $kapasitas = $sesi['kapasitas'];

            // Count how many users are already registered for this session
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_sesi = :sesi_id");
            $stmt_count->execute(['sesi_id' => $sesi_id]);
            $registered_count = $stmt_count->fetchColumn();

            // Check if the session is full
            if ($registered_count >= $kapasitas) {
                $message = "Sorry, this session is full. Please choose a different session.";
                $showModal = true;
            } else {
                // Proceed with registration if there is space
                $stmt = $pdo->prepare("
                    INSERT INTO pendaftaran (id_peserta, id_ujian, id_sesi)
                    VALUES (:user_id, :exam_id, :sesi_id)
                ");
                $stmt->execute(['user_id' => $user_id, 'exam_id' => $exam_id, 'sesi_id' => $sesi_id]);

                $message = "Registration successful! Please proceed to the payment page.";
                $showModal = true;

                // Redirect to payment page
                header("Location: pembayaran.html");
                exit(); // Ensure no further code is executed after the redirect
            }
        } else {
            $message = "Session not found.";
            $showModal = true;
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $showModal = true;
    }
}

// Fetch data from the ujian table
try {
    $stmt_ujian = $pdo->prepare("SELECT * FROM ujian");
    $stmt_ujian->execute();
    $ujian_results = $stmt_ujian->fetchAll(PDO::FETCH_ASSOC);

    // Fetch data from the sesi table
    $stmt_sesi = $pdo->prepare("SELECT * FROM sesi");
    $stmt_sesi->execute();
    $sesi_results = $stmt_sesi->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">

    <div class="container py-5">
        <h1 class="text-center mb-4">Daftar Ujian</h1>

        <!-- Form for registering for an exam -->
        <form action="daftar_ujian.php" method="POST" class="p-4 bg-secondary rounded">

            <!-- Select Exam -->
            <div class="mb-3">
                <label for="exam_id" class="form-label">Select Exam</label>
                <select name="exam_id" id="exam_id" class="form-select bg-dark text-white">
                    <option value="" disabled selected>Select an exam</option>
                    <?php foreach ($ujian_results as $ujian): ?>
                        <option value="<?php echo $ujian['id_ujian']; ?>">
                            <?php echo htmlspecialchars($ujian['nama_ujian']); ?> - 
                            <?php echo htmlspecialchars($ujian['tanggal_ujian']); ?> - 
                            <?php echo htmlspecialchars($ujian['lokasi_ujian']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Select Session -->
            <div class="mb-3">
                <label for="sesi_id" class="form-label">Select Session</label>
                <select name="sesi_id" id="sesi_id" class="form-select bg-dark text-white">
                    <option value="" disabled selected>Select a session</option>
                    <?php foreach ($sesi_results as $sesi): ?>
                        <option value="<?php echo $sesi['id_sesi']; ?>">
                            <?php 
                                $exam_name = '';
                                // Find the exam name by matching the id_ujian
                                foreach ($ujian_results as $ujian) {
                                    if ($ujian['id_ujian'] == $sesi['id_ujian']) {
                                        $exam_name = $ujian['nama_ujian'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($exam_name) . " - " . 
                                     htmlspecialchars($sesi['nama_sesi']) . " - " . 
                                     htmlspecialchars($sesi['waktu_mulai']) . " - " . 
                                     htmlspecialchars($sesi['waktu_selesai']) . " - " . 
                                     "Capacity: " . htmlspecialchars($sesi['kapasitas']);
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Register button -->
            <button type="submit" class="btn btn-light w-100">Register</button>

        </form>
    </div>

    <!-- Modal HTML Structure -->
    <input type="checkbox" id="modalToggle" class="modal-toggle" <?php echo $showModal ? 'checked' : ''; ?>>
    <div id="modal" class="modal">
        <div class="modal-content">
            <!-- Close button inside the modal to uncheck and redirect to sign.html -->
            <a href="sign.html" class="close-btn">&times;</a>
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    </div>

    <!-- Modal CSS Styling -->
    <style>
        /* Hide modal by default */
        .modal-toggle {
            display: none;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Overlay */
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        /* When checked (when modalToggle is checked), show modal */
        .modal-toggle:checked + .modal {
            display: flex;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
            text-align: center;
            position: relative;
        }

        /* Close button to hide the modal and redirect to sign.html */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            text-decoration: none;
            color: black;
            cursor: pointer;
        }

        .close-btn:hover {
            color: red;
        }
    </style>

</body>
</html>