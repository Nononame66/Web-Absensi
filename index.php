<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Web Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #0f0f13;
            color: #d1d5db;
        }

        .container {
            text-align: center;
        }

        .container h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .container p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }

        .container a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to Web Absensi</h1>
        <p>This is the root directory of the Web Absensi project.</p>
        <p>Explore the <a href="admin/">Admin</a> or <a href="siswa/">Siswa</a> sections.</p>
    </div>
    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/theme.js"></script>
</body>

</html>