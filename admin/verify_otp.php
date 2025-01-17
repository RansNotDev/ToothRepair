<?php
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $otp = $_POST["otp"];

    $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE email = ? AND otp_code = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // OTP is valid
        session_start();
        $_SESSION["email"] = $email;
        echo "<script>
                alert('Login successful!');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        // Invalid OTP
        echo "<script>alert('Invalid or expired OTP!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <form method="POST" action="verify_otp.php">
        <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
