<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$post_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$post_id || $post_id < 1) {
    http_response_code(400);
    exit("Ungültige Beitrags-ID.");
}

$statement = $pdo->prepare(
    "SELECT id, topic_id, user_id, content
     FROM posts
     WHERE id = ?"
);

$statement->execute([$post_id]);
$post = $statement->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    exit("Beitrag nicht gefunden.");
}

if (
    (int) $post["user_id"] !== (int) $_SESSION["user_id"]
    && ($_SESSION["role"] ?? "") !== "admin"
) {
    http_response_code(403);
    exit("Du darfst diesen Beitrag nicht löschen.");
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

    $statement = $pdo->prepare(
        "DELETE FROM posts WHERE id = ?"
    );

    $statement->execute([$post_id]);

    header("Location: topic.php?id=" . $post["topic_id"]);
    exit;
}
?> 
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beitrag löschen – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
            <a href="topic.php?id=<?php echo (int) $post['topic_id']; ?>">
                Zurück zum Thema
            </a>
        </nav>
    </header>

    <main>
        <h1>Beitrag löschen</h1>

        <p>Möchtest du diesen Beitrag wirklich löschen?</p>

        <p>
            <?php echo nl2br(htmlspecialchars($post["content"], ENT_QUOTES, "UTF-8")); ?>
        </p>

        <form method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"); ?>">

            <button type="submit">Beitrag endgültig löschen</button>
        </form>

        <p>
            <a href="topic.php?id=<?php echo (int) $post['topic_id']; ?>">
                Abbrechen
            </a>
        </p>
    </main>
</body>
</html>