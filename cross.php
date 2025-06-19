<?php

// Основна функція для генерації кросвордів
function generateCrosswords($allWords, $batchSize, &$progress, $outputDir, $gridSize, $progressFile) {
    $generated = 0;

    for ($i = 0; $i < $batchSize; $i++) {
        if ($progress >= 1000) break; // Вихід, якщо досягнуто максимальний ліміт

        // Вибірка слів для одного кросворду
        $numWords = rand(12, min(40, count($allWords)));
        $selected = array_splice($allWords, 0, $numWords);

        // Генерація кросворду
        $crossword = generateCrosswordWithBacktracking($selected, $gridSize);

        // Збереження кросворду у файл
        $filename = "{$outputDir}/crossword_{$gridSize}_{$progress}.json";
        file_put_contents($filename, json_encode($crossword, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // Оновлення статусу в БД (type = 0 після вибору)
        updateWordStatusInDB($selected);

        // Оновлення прогресу
        $progress++;
        file_put_contents($progressFile, $progress);

        $generated++;
    }

    return $generated;
}

// Генерація одного кросворду з використанням пошуку з поверненням (Backtracking)
function generateCrosswordWithBacktracking($words, $gridSize) {
    $grid = array_fill(0, $gridSize, array_fill(0, $gridSize, ' '));
    $wordData = []; // Для зберігання інформації про кожне слово в кросворді

    // Функція для спроби розміщення слів з поверненням
    function placeWordWithBacktracking($words, $index, &$grid, &$wordData) {
        if ($index === count($words)) {
            return true; // Якщо всі слова розміщені
        }

        $word = $words[$index];
        $placed = false;
        
        // Пробуємо різні місця для поточного слова
        for ($x = 0; $x < $gridSize; $x++) {
            for ($y = 0; $y < $gridSize; $y++) {
                // Пробуємо горизонтальне розміщення
                if (canPlaceWord($grid, $word['answer'], $x, $y, 'across')) {
                    placeWord($grid, $word['answer'], $x, $y, 'across');
                    $wordData[] = [
                        'word' => $word['answer'],
                        'question' => $word['question'],
                        'x' => $x,
                        'y' => $y,
                        'direction' => 'across'
                    ];

                    if (placeWordWithBacktracking($words, $index + 1, $grid, $wordData)) {
                        return true; // Якщо слово вдалося розмістити, пробуємо наступне
                    }

                    // Якщо не вдалося — скасовуємо зміни (backtrack)
                    removeWord($grid, $word['answer'], $x, $y, 'across');
                    array_pop($wordData);
                }

                // Пробуємо вертикальне розміщення
                if (canPlaceWord($grid, $word['answer'], $x, $y, 'down')) {
                    placeWord($grid, $word['answer'], $x, $y, 'down');
                    $wordData[] = [
                        'word' => $word['answer'],
                        'question' => $word['question'],
                        'x' => $x,
                        'y' => $y,
                        'direction' => 'down'
                    ];

                    if (placeWordWithBacktracking($words, $index + 1, $grid, $wordData)) {
                        return true; // Якщо слово вдалося розмістити, пробуємо наступне
                    }

                    // Якщо не вдалося — скасовуємо зміни (backtrack)
                    removeWord($grid, $word['answer'], $x, $y, 'down');
                    array_pop($wordData);
                }
            }
        }
        
        return false; // Якщо не вдалося розмістити слово
    }

    // Починаємо розміщення слів з першого
    placeWordWithBacktracking($words, 0, $grid, $wordData);

    return [
        'size' => $gridSize,
        'words' => $wordData,
        'grid' => $grid
    ];
}

// Перевірка, чи можна помістити слово в сітку
function canPlaceWord($grid, $word, $x, $y, $direction) {
    $len = strlen($word);
    
    if ($direction === 'across') {
        if ($x + $len > count($grid)) return false;
        for ($i = 0; $i < $len; $i++) {
            if ($grid[$y][$x + $i] !== ' ' && $grid[$y][$x + $i] !== $word[$i]) return false;
        }
    } else {
        if ($y + $len > count($grid)) return false;
        for ($i = 0; $i < $len; $i++) {
            if ($grid[$y + $i][$x] !== ' ' && $grid[$y + $i][$x] !== $word[$i]) return false;
        }
    }
    
    return true;
}

// Розміщення слова в сітці
function placeWord(&$grid, $word, $x, $y, $direction) {
    $len = strlen($word);
    
    if ($direction === 'across') {
        for ($i = 0; $i < $len; $i++) {
            $grid[$y][$x + $i] = $word[$i];
        }
    } else {
        for ($i = 0; $i < $len; $i++) {
            $grid[$y + $i][$x] = $word[$i];
        }
    }
}

// Видалення слова з сітки (для backtracking)
function removeWord(&$grid, $word, $x, $y, $direction) {
    $len = strlen($word);
    
    if ($direction === 'across') {
        for ($i = 0; $i < $len; $i++) {
            $grid[$y][$x + $i] = ' ';
        }
    } else {
        for ($i = 0; $i < $len; $i++) {
            $grid[$y + $i][$x] = ' ';
        }
    }
}

// Оновлення статусу слів в базі даних
function updateWordStatusInDB($selected) {
    global $pdo;
    
    $ids = array_column($selected, 'id');
    $in = implode(',', array_map('intval', $ids));
    $pdo->exec("UPDATE questions SET type = 0 WHERE id IN ($in)");
}

?>
