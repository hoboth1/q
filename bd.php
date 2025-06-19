<?php

// Підключення до бази даних
function getDBConnection() {
    $pdo = new PDO('mysql:host=localhost;dbname=crossword;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Вибірка слів для генерації кросворду
function getWordsForCrossword($pdo, $limit) {
    $stmt = $pdo->prepare("SELECT id, question, answer FROM questions WHERE type = 1 ORDER BY RAND() LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
