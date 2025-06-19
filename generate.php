<?php
// Підключаємо потрібні файли
require_once 'bd.php';
require_once 'cross.php';

// Генерація одного кросворду
if (isset($_POST['generate'])) {
    // Параметри генерації
    $gridSize = 15; // Можна змінити
    $batchSize = 1; // Створюємо 1 кросворд
    $progressFile = 'count.txt';
    $outputDir = 'output';
    
    // Читання поточного прогресу
    $progress = file_exists($progressFile) ? (int) file_get_contents($progressFile) : 0;
    
    // Ліміт
    $maxCrosswords = 1000;
    if ($progress >= $maxCrosswords) {
        echo json_encode(['error' => 'Ліміт кросвордів досягнуто.']);
        exit;
    }
    
    // Підключення до бази даних та вибірка слів
    $pdo = getDBConnection();
    $allWords = getWordsForCrossword($pdo, 50000); // вибираємо 50к слів (можна змінити)

    // Генерація кросворду
    $crossword = generateCrosswordWithBacktracking($allWords, $gridSize);

    // Оновлення прогресу
    $progress++;
    file_put_contents($progressFile, $progress);

    // Зберігаємо JSON у файл
    $filename = "{$outputDir}/crossword_{$gridSize}_{$progress}.json";
    file_put_contents($filename, json_encode($crossword, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // Відповідь для AJAX
    echo json_encode([
        'progress' => $progress,
        'crossword' => $crossword
    ]);
}
?>
