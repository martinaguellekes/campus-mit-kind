<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$topic_id = filter_input(INPUT_POST, "topic_id", FILTER_VALIDATE_INT);
$content = trim($_POST["content"] ?? "");

if (!$topic_id || $topic_id < 1) {
    exit("Ungültige Themen-ID.");
}

if ($content === "") {
    exit("Bitte gib einen Beitrag ein.");
}

$statement = $pdo->prepare("SELECT id FROM topics WHERE id = ?");
$statement->execute([$topic_id]);

if (!$statement->fetch()) {
    exit("Das Diskussionsthema wurde nicht gefunden.");
}

$statement = $pdo->prepare(
    "INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)"
);

$statement->execute([
    $topic_id,
    $_SESSION["user_id"],
    $content
]);

header("Location: topic.php?id=" . $topic_id);
exit;