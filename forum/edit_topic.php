<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$topic_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$topic_id || $topic_id < 1) {
    http_response_code(400);
    exit("Ungültige Themen-ID.");
}

$statement = $pdo->prepare(
    "SELECT id, user_id, title FROM topics WHERE id = ?"
);

$statement->execute([$topic_id]);
$topic = $statement->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    http_response_code(404);
    exit("Diskussionsthema nicht gefunden.");
}

if (
    (int) $topic["user_id"] !== (int) $_SESSION["user_id"]
    && ($_SESSION["role"] ?? "") !== "admin"
) {
    http_response_code(403);
    exit("Du darfst dieses Thema nicht bearbeiten.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");

    if ($title === "") {
        $message = "Bitte gib einen Thementitel ein.";
    } elseif (mb_strlen($title) > 200) {
        $message = "Der Titel darf höchstens 200 Zeichen lang sein.";
    } else {
        $statement = $pdo->prepare(
            "UPDATE topics SET title = ? WHERE id = ?"
        );

        $statement->execute([$title, $topic_id]);

        header("Location: topic.php?id=" . $topic_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thema bearbeiten – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
            <a href="topic.php?id=<?php echo (int) $topic['id']; ?>">
                Zurück zum Thema
            </a>
            <a href="../auth/logout.php">Abmelden</a>
        </nav>
    </header>

    <main>
        <h1>Diskussionsthema bearbeiten</h1>

        <?php if ($message !== ""): ?>
            <p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="title">Thementitel</label>

                <input
                type="text"
                id="title"
                name="title"
                maxlength="200"
value="<?php echo htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8'); ?>"
required
>

<button type="submit">Änderungen speichern</button>
</form>
</main>
</body>
</html>