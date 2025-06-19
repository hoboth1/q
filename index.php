<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Генератор Кросвордів</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Генератор Кросвордів</h1>
    <p>Створено кросвордів: <span id="progressCount">0</span>/1000</p>
    
    <!-- Кнопка для генерації кросворду -->
    <button id="generateButton">Згенерувати Кросворд</button>

    <div id="crosswordResult" style="margin-top: 20px;">
        <!-- Тут будемо відображати згенерований кросворд -->
        <h3>Згенерований Кросворд</h3>
        <pre id="crosswordJson"></pre>
    </div>

    <script>
        $(document).ready(function () {
            $('#generateButton').click(function () {
                // Відправляємо AJAX запит для генерації одного кросворду
                $.ajax({
                    url: 'generate.php',
                    type: 'POST',
                    data: { generate: true },
                    success: function (response) {
                        // Виводимо номер кросворду та JSON
                        let jsonResponse = JSON.parse(response);
                        $('#crosswordJson').text(JSON.stringify(jsonResponse.crossword, null, 2));

                        // Оновлюємо кількість створених кросвордів
                        $('#progressCount').text(jsonResponse.progress);

                        // Оновлюємо інтерфейс
                        $('#generateButton').prop('disabled', true); // Дезактивуємо кнопку поки йде генерація
                        setTimeout(function() {
                            $('#generateButton').prop('disabled', false); // Активуємо кнопку знову після 5 секунд
                        }, 5000);
                    },
                    error: function () {
                        alert('Помилка при генерації кросворду');
                    }
                });
            });
        });
    </script>
</body>
</html>
