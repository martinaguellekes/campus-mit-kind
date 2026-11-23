<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");

    if ($title === "") {
        $message = "Bitte gib einen Thementitel ein.";
    } elseif (mb_strlen($title) > 200) {
        $message = "Der Titel darf höchstens 200 Zeichen lang sein.";
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO topics (user_id, title) VALUES (?, ?)"
        );

        $statement->execute([
            $_SESSION["user_id"],
            $title
        ]);

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Thema – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
    <a href="index.php">Zurück zum Forum</a>
    <a href="../auth/logout.php">Abmelden</a>
</nav>
/header>

<main>
    <h1>Neues Diskussionsthema erstellen</h1>

    <?php if ($message !== ""): ?>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="title">Titel des Themas</label>
            <input type="text" id="title" name="title" maxlength="200" required>

            <button type="submit">Thema erstellen</button>
        </form>
    </main>
</body>
</html>