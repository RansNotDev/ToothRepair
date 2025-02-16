<?php
include_once('database/db_connection.php');

// Fetch feedbacks with user information
$sql = "SELECT f.*, u.fullname 
        FROM feedbacks f 
        JOIN users u ON f.user_id = u.user_id 
        ORDER BY f.created_at DESC
        LIMIT 6";  // Add this line to show only recent 6 feedbacks
$result = mysqli_query($conn, $sql);

// Function to mask name (e.g., "John Doe" becomes "Jo** Do*")
function maskName($name) {
    $parts = explode(' ', $name);
    $maskedParts = array_map(function($part) {
        $length = strlen($part);
        if ($length <= 2) return $part;
        return substr($part, 0, 2) . str_repeat('*', $length - 2);
    }, $parts);
    return implode(' ', $maskedParts);
}
?>
<link rel="stylesheet" href="css/components/feedback.css">
<section class="testimonial-section section-padding section-bg">
    <div class="section-overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-12 text-center">
                <h2 class="text-white mb-4">Happy Customers</h2>
            </div>

            <?php 
            if ($result && mysqli_num_rows($result) > 0) {
                $counter = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($counter % 2 == 0) {
                        echo '<div class="col-lg-4 col-12">';
                    }
            ?>
                    <div class="featured-block <?php echo ($counter % 2 != 0) ? 'mb-lg-0' : ''; ?>">
                        <div class="d-flex align-items-center mb-3">
                            <div class="ms-3">
                                <h4 class="mb-0"><?php echo maskName($row['fullname']); ?></h4>
                                <div class="reviews-icons mb-1">
                                    <?php
                                    // Display stars based on rating
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $row['rating']) {
                                            echo '<i class="bi-star-fill"></i>';
                                        } else {
                                            echo '<i class="bi-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </small>
                            </div>
                        </div>

                        <p class="mb-0"><?php echo htmlspecialchars($row['feedback_text']); ?></p>
                        <span class="badge bg-<?php 
                            echo match($row['satisfaction_level']) {
                                'Very Satisfied' => 'success',
                                'Satisfied' => 'primary',
                                'Neutral' => 'warning',
                                'Dissatisfied' => 'danger',
                                'Very Dissatisfied' => 'dark',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo htmlspecialchars($row['satisfaction_level']); ?>
                        </span>
                    </div>

            <?php
                    if ($counter % 2 != 0) {
                        echo '</div>';
                    }
                    $counter++;
                }
                // Close the last column div if needed
                if ($counter % 2 != 0) {
                    echo '</div>';
                }
            } else {
            ?>
                <div class="col-12 text-center">
                    <p class="text-white">No feedbacks available yet.</p>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</section>