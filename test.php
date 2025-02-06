<!DOCTYPE html>
<html>
<head>
    <title>Email Test Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Test Email Form</h2>
        <form method="POST" action="sendmail.php">
            <div class="form-group">
                <label>Recipient Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Subject:</label>
                <input type="text" name="subject" required>
            </div>
            <div class="form-group">
                <label>Message:</label>
                <textarea name="message" rows="5" required></textarea>
            </div>
            <button type="submit" name="sendmail">Send Email</button>
        </form>
    </div>
</body>
</html>