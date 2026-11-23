
<?php

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $email === "" || $password === "") {
        $message = "Bitte alle Felder ausfüllen.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Bitte eine gültige E-Mail-Adresse eingeben.";

            } elseif (strlen($password) < 8) {
                $message = "Das Passwort muss mindestens 8 Zeichen lang sein.";

                } else {

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $sql = "INSERT INTO users
                    (username, email, password_hash)
                    VALUES (:username, :email, :password_hash)";

$statement = $pdo->prepare($sql);
$statement->execute([
    "username" => $username,
    "email" => $email,
    "password_hash" => $passwordHash
    ]);

    $message = "Registrierung erfolgreich!";

    } catch (PDOException $e) {

    if ($e->getCode() === "23000") {
        $message = "Benutzername oder E-Mail-Adresse ist bereits vergeben.";
        } else {
            error_log($e->getMessage());
            $message = "Bei der Registrierung ist ein Fehler aufgetreten.";
            }
            }
            }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header>
    <div class="logo">Campus mit Kind</div>

    <nav>
        <a href="../index.php">Startseite</a>
        <a href="login.php">Anmelden</a>
    </nav>
</header>

<main>
    <h1>Registrieren</h1>
    <p>Erstelle ein Benutzerkonto für Campus mit Kind.</p>
<?php if ($message !== ""): ?>
    <p>
        <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
    <p> 
<?php endif; ?> 
    <form action="register.php" method="post">

        <label for="username">Benutzername:</label>
        <input type="text" id="username"
               name="username" required>

        <label for="email">E-Mail-Adresse:</label>
        <input type="email" id="email"
               name="email" required>

        <label for="password">Passwort:</label>
        <input type="password" id="password"
               name="password" required>

        <button type="submit">Registrieren</button>

    </form>
</main>

</body>
</html>
