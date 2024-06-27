<!DOCTYPE html>
<html>
<head>
    <title>Upload CSV File</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #d4edda;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="file"] {
            width: 100%;
        }
        .form-group button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if (isset($_GET['inserted']) && isset($_GET['duplicates'])) {
            $inserted = intval($_GET['inserted']);
            $duplicates = intval($_GET['duplicates']);
            echo "<div class='message'>Success! $inserted records inserted, $duplicates duplicates found and skipped.</div>";
        }
        ?>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">Choose CSV File:</label>
                <input type="file" name="csv_file" id="csv_file" required>
            </div>
            <div class="form-group">
                <button type="submit">Upload</button>
            </div>
        </form>
    </div>
</body>
</html>
