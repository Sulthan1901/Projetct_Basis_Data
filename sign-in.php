<?php
require 'db.php';
session_start();

// Flag for showing modal (by default, it's false)
$showModal = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Prepare the SQL statement to get user data
        $stmt = $pdo->prepare("SELECT id_peserta, email, password FROM peserta WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Store user ID in session for later use
                $_SESSION['user_id'] = $user['id_peserta'];
                // Set success message and show modal
                $showModal = true;
                $message = "Login successful! Redirecting to exam registration.";
                header("Refresh: 3; url=daftar_ujian.php"); // Redirect after 3 seconds
            } else {
                // Invalid password
                $showModal = true;
                $message = "Invalid credentials! Please try again.";
            }
        } else {
            // User not found
            $showModal = true;
            $message = "User not found! Please try again.";
        }
    } catch (PDOException $e) {
        $showModal = true;
        $message = "Error: " . $e->getMessage();
    }
}
?>

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
