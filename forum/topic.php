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
    "SELECT topics.id, topics.user_id, topics.title, topics.created_at, users.username
    FROM topics
    JOIN users ON topics.user_id = users.id
    WHERE topics.id = ?"
);

$statement->execute([$topic_id]);
$topic = $statement->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    http_response_code(404);
exit("Dieses Diskussionsthema wurde nicht gefunden.");
}
$postStatement = $pdo->prepare(
    "SELECT posts.id, posts.user_id, posts.content, posts.created_at, users.username
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.topic_id = ?
    ORDER BY posts.created_at ASC, posts.id ASC"
);

$postStatement->execute([$topic_id]);
$posts = $postStatement->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thema – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="logo">Campus mit Kind</div>
        <nav>
            <a href="index.php">Zurück zum Forum</a>
            <a href="../auth/logout.php">Abmelden</a>
        </nav>
    </header>

    <main>
        <h1>
            <?php echo htmlspecialchars($topic["title"], ENT_QUOTES, "UTF-8"); ?>
        </h1>

        <p>
            Erstellt von:
            <?php echo htmlspecialchars($topic["username"], ENT_QUOTES, "UTF-8"); ?>
        </p>
<?php if (
    (int) $topic["user_id"] === (int) $_SESSION["user_id"]
    || ($_SESSION["role"] ?? "") === "admin"
): ?>
    <p>
        <a href="edit_topic.php?id=<?php echo (int) $topic['id']; ?>">
            Thema bearbeiten
        </a>
    </p>
    <p>
    <a href="delete_topic.php?id=<?php echo (int) $topic['id']; ?>">
        Thema löschen
    </a>
</p>
<?php endif; ?>
        <h2>Beiträge</h2>
        <?php if (empty($posts)): ?>
    <p>Zu diesem Thema sind noch keine Beiträge vorhanden.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <p>
                <strong>
                    <?php echo htmlspecialchars($post["username"], ENT_QUOTES, "UTF-8"); ?>
                </strong>
            </p>
            <p>
                <?php echo nl2br(htmlspecialchars($post["content"], ENT_QUOTES, "UTF-8")); ?>
            </p>
            <?php if (
    (int) $post["user_id"] === (int) $_SESSION["user_id"]
    || ($_SESSION["role"] ?? "") === "admin"
): ?>
    <p>
        <a href="edit_post.php?id=<?php echo (int) $post['id']; ?>">
            Beitrag bearbeiten
        </a>
    </p>
    <p>
    <a href="delete_post.php?id=<?php echo (int) $post['id']; ?>">Beitrag löschen</a>
</p>
<?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<h2>Neuen Beitrag schreiben</h2>

<form action="create_post.php" method="POST">
    <input type="hidden" name="topic_id"
           value="<?php echo (int) $topic['id']; ?>">

    <label for="content">Dein Beitrag</label>

    <textarea id="content" name="content"
              rows="5" required></textarea>

    <button type="submit">Beitrag veröffentlichen</button>
    </main>
</body>
</html>