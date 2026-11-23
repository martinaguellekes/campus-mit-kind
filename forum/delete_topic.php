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
    exit("Du darfst dieses Thema nicht löschen.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST["csrf_token"])
        || !hash_equals(
            $_SESSION["csrf_token"],
            $_POST["csrf_token"]
        )
    ) {
        http_response_code(403);
        exit("Ungültige Sicherheitsprüfung.");
    }

    try {
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            "DELETE FROM posts WHERE topic_id = ?"
        );
        $statement->execute([$topic_id]);

        $statement = $pdo->prepare(
            "DELETE FROM topics WHERE id = ?"
        );
        $statement->execute([$topic_id]);

        $pdo->commit();

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        http_response_code(500);
        exit("Das Thema konnte nicht gelöscht werden.");
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thema löschen – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
            <a href="topic.php?id=<?php echo (int) $topic['id']; ?>">
                Zurück zum Thema
            </a>
        </nav>
    </header>

    <main>
        <h1>Diskussionsthema löschen</h1>

        <p>Möchtest du dieses Thema wirklich löschen?</p>

        <p>
            <strong>
                <?php echo htmlspecialchars($topic["title"], ENT_QUOTES, "UTF-8"); ?>
            </strong>
        </p>

        <p>
            Achtung: Beim Löschen des Themas werden auch
            alle zugehörigen Beiträge endgültig gelöscht.
        </p>

        <form method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"); ?>">

            <button type="submit">Thema endgültig löschen</button>
        </form>

        <p>
            <a href="topic.php?id=<?php echo (int) $topic['id']; ?>">
                Abbrechen
            </a>
        </p>
    </main>
</body>
</html>