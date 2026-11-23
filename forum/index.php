<?php

session_start();
require_once "../config/database.php";
$statement = $pdo->query("SELECT id, title, created_at FROM topics ORDER BY created_at DESC, id DESC");
$topics = $statement->fetchALL(PDO::FETCH_ASSOC);
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum – Campus mit Kind</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

    <header>
        <div class="logo">Campus mit Kind</div>

        <nav>
            <a href="../index.php">Startseite</a>
            <a href="../auth/logout.php">Abmelden</a>
        </nav>
    </header>

    <main>
        <h1>Forum</h1>
        <p><a href="create_topic.php">Neues Thema erstellen</a></p>

        <p>
            Willkommen,
            <?php echo htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8"); ?>!
        </p>

       <?php if (empty($topics)): ?>
    <p>Noch keine Diskussionsthemen vorhanden.</p>
<?php else: ?>
    <ul>
        <?php foreach ($topics as $topic): ?>
            <li>
                <a href="topic.php?id=<?php echo $topic['id']; ?>">
                <?php echo htmlspecialchars($topic["title"], ENT_QUOTES, "UTF-8"); ?>
        </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
    </main>

</body>
</html>