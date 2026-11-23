<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Bitte alle Felder ausfüllen.";

    } else {

        $sql = "SELECT id, username, password_hash, role
                FROM users
                WHERE email = :email";

        $statement = $pdo->prepare($sql);
        $statement->execute(["email" => $email]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            $message = "Anmeldung erfolgreich!";

        } else {
            $message = "E-Mail-Adresse oder Passwort ist falsch.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

    <header>
        <div class="logo">Campus mit Kind</div>

        <nav>
            <a href="../index.php">Startseite</a>
            <a href="register.php">Registrieren</a>
        </nav>
    </header>

    <main>
        <h1>Anmelden</h1>
        <p>Melde dich mit deinem Benutzerkonto an.</p>

<?php if ($message !== ""): ?>
    <p>
        <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
    </p>
<?php endif; ?>
        <form action="login.php" method="post">

            <label for="email">E-Mail-Adresse:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="password">Passwort:</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Anmelden</button>

        </form>
    </main>

</body>
</html>