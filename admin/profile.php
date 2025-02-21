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
    $picture = null; // Initialize picture as null

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
        // Check image type and size (up to 5MB)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file_extension, $allowed_types) && 
            $_FILES['picture']['size'] <= $max_size) {
            
            // Read the file content
            $picture = file_get_contents($_FILES['picture']['tmp_name']);
            
            // Add the picture to the query
            $query .= ", picture = ?";
            $types .= "b"; // 'b' for blob type
            $params[] = $picture;
        } else {
            echo "<script>alert('Invalid image file. Please upload JPG, PNG, GIF, or WEBP files under 5MB.');</script>";
            exit();
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
    <!-- Add gradient animation library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>

<body class="bg-gradient-primary">
    <?php
    include_once('includes/header.php');
    include_once('includes/sidebar.php');
    include_once('includes/topbar.php');
    ?>

    <!-- Main Content -->
    <div class="content-wrapper animate__animated animate__fadeIn">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10 col-md-12">
                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0">
                            <!-- Nested Row within Card Body -->
                            <div class="row">
                                <!-- Left Column for Image Upload -->
                                <div class="col-lg-5 d-flex align-items-center justify-content-center p-5">
                                    <div class="text-center">
                                        <div class="profile-picture-container mb-3" onclick="document.getElementById('pictureInput').click();">
                                            <img id="profilePicture" 
                                                src="data:image/jpeg;base64,<?php echo !empty($admin_data['picture']) ? base64_encode($admin_data['picture']) : 'path/to/default-image.jpg'; ?>" 
                                                alt="Profile Picture" 
                                                class="rounded-circle shadow-lg"
                                                style="width: 250px; height: 250px; object-fit: cover; cursor: pointer;">
                                            <div class="upload-overlay">
                                                <i class="fas fa-camera fa-2x text-white"></i>
                                            </div>
                                        </div>
                                        <input type="file" id="pictureInput" name="picture" 
                                            class="form-control" accept="image/*" style="display: none;">
                                        <small class="text-muted d-block mt-2">Max 5MB (JPG, PNG, GIF, WEBP)</small>
                                    </div>
                                </div>

                                <!-- Right Column for Form Fields -->
                                <div class="col-lg-7">
                                    <div class="p-5">
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">Admin Profile</h1>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data" action="" class="user">
                                            <div class="form-group">
                                                <input type="text" name="name" class="form-control form-control-user"
                                                    value="<?php echo htmlspecialchars($admin_data['name']); ?>" 
                                                    placeholder="Full Name" required>
                                            </div>

                                            <div class="form-group">
                                                <input type="password" name="password" 
                                                    class="form-control form-control-user"
                                                    placeholder="New Password (leave blank to keep current)">
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                                Update Profile
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('includes/footer.php'); ?>
    
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
    <style>
        .bg-profile-image {
            min-height: 400px;
            border-radius: 0.35rem 0 0 0.35rem;
        }
        .profile-picture-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .profile-picture-container:hover {
            transform: scale(1.05);
        }
        .profile-picture-container img {
            transition: filter 0.3s ease;
        }
        .profile-picture-container:hover img {
            filter: brightness(0.8);
        }
        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .profile-picture-container:hover .upload-overlay {
            opacity: 1;
        }
        .form-control-user {
            border-radius: 10rem;
            padding: 1.5rem 1rem;
        }
        .btn-user {
            border-radius: 10rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 600;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            min-height: 100vh;
        }

        .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .form-control-user:focus {
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
    </style>
</body>
</html>