<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch existing admin data
$admin_id = $_SESSION['admin_id'];
$fetch_query = "SELECT name, picture FROM admins WHERE admin_id = ?";
$stmt = $conn->prepare($fetch_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $picture = '';

    // Only update password if it's not empty
    $query = "UPDATE admins SET name = ?";
    $types = "s";
    $params = array($name);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $query .= ", password = ?";
        $types .= "s";
        $params[] = $password;
    }

    if (!empty($_FILES['picture']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                $picture = $target_file;
                $query .= ", picture = ?";
                $types .= "s";
                $params[] = $picture;
            }
        }
    }

    $query .= " WHERE admin_id = ?";
    $types .= "i";
    $params[] = $admin_id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile!');</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | ToothRepair</title>
    <!-- Keep your existing CSS links here -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SB Admin 2 Template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php
    include_once('includes/header.php');
    include_once('includes/sidebar.php');
    include_once('includes/topbar.php');
    ?>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 offset-md-3 mt-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Admin Profile</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" action="">
                                <div class="text-center mb-4">
                                    <img id="profilePicture" 
                                        src="<?php echo !empty($admin_data['picture']) ? $admin_data['picture'] : 'uploads/default-profile.png'; ?>" 
                                        alt="Profile Picture" 
                                        class="rounded-circle border"
                                        style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;">
                                    <input type="file" id="pictureInput" name="picture" class="form-control"
                                        accept="image/*" style="display: none;">
                                </div>
                                <div class="mb-3 text-center">
                                    <label class="form-label">Profile Picture</label>
                                    <small class="text-muted">Allowed formats: JPG, JPEG, PNG</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" 
                                        value="<?php echo htmlspecialchars($admin_data['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" 
                                        placeholder="Leave blank to keep current password">
                                    <small class="text-muted">Only fill this if you want to change your password</small>
                                </div>

                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <button class="btn btn-primary" type="submit">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('includes/footer.php'); ?>
    <!-- Keep your existing script tags here -->
    <script>
        document.getElementById('profilePicture').addEventListener('click', function () {
            document.getElementById('pictureInput').click();
        });

        document.getElementById('pictureInput').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('profilePicture').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>