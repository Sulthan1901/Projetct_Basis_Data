<?php
require 'db.php';

$showModal = false; // Flag to check if modal should be shown
$message = '';      // Message to show in the modal

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the POST data from the form
    $name = $_POST['name'];      // Capture username
    $address = $_POST['address'];  // Capture address
    $phone = $_POST['phone'];     // Capture phone number
    $email = $_POST['email'];     // Capture email
    $password = $_POST['password']; // Capture password

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format. Please enter a valid email address.";
        $showModal = true;
    }

    // Validate username length (at least 8 characters and at most 30 characters)
    if (strlen($name) < 8 || strlen($name) > 30) {
        $message = "Name must be between 8 and 30 characters.";
        $showModal = true;
    }

    // Validate alamat (address) length
    if (strlen($address) < 10) {
        $message = "Address must be at least 10 characters long.";
        $showModal = true;
    }

// Validate password length
    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $showModal = true;
    }

    // Check if the address or phone number already exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peserta WHERE alamat = :address OR no_telepon = :phone");
    $stmt->execute(['address' => $address, 'phone' => $phone]);
    $existingCount = $stmt->fetchColumn();

    // If the address or phone number already exists, show an error
    if ($existingCount > 0) {
        $message = "The address or phone number is already taken. Please choose a different one.";
        $showModal = true;
    }

    // If no errors, proceed with hashing and database insertion
    if (!$showModal) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare and execute the SQL statement to insert the user data
            $stmt = $pdo->prepare("
                INSERT INTO peserta (nama, alamat, no_telepon, email, password) 
                VALUES (:name, :address, :phone, :email, :password)
            ");
            $stmt->execute([
                'name' => $name, 
                'address' => $address, 
                'phone' => $phone, 
                'email' => $email, 
                'password' => $hashedPassword
            ]);

            // If successful, set a success message
            $message = "Registration successful!";
            $showModal = true;
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $showModal = true;
        }
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
