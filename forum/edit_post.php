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
    "SELECT posts.id, posts.topic_id, posts.user_id, posts.content
    FROM posts
    WHERE posts.id = ?"
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
exit("Du darfst diesen Beitrag nicht bearbeiten.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = trim($_POST["content"] ?? "");

    if ($content === "") {
        $message = "Bitte gib einen Beitragstext ein.";
        } else {
            $statement = $pdo->prepare(
                "UPDATE posts SET content = ? WHERE id = ?"
                );

                $statement->execute([$content, $post_id]);

                header("Location: topic.php?id=" . $post["topic_id"]);
                exit;
                }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beitrag bearbeiten – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
            <a href="topic.php?id=<?php echo (int) $post['topic_id']; ?>">
                Zurück zum Thema
            </a>
            <a href="../auth/logout.php">Abmelden</a>
        </nav>
    </header>

    <main>
        <h1>Beitrag bearbeiten</h1>

        <?php if ($message !== ""): ?>
            <p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="content">Dein Beitrag</label>

            <textarea id="content" name="content" rows="5" required><?php
                echo htmlspecialchars(
                    $post["content"],
                    ENT_QUOTES,
                    "UTF-8"
                );
            ?></textarea>

            <button type="submit">Änderungen speichern</button>
        </form>
    </main>
</body>
</html>