<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЛР7 - Асинхронная обработка</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .status { padding: 10px; margin: 10px 0; background: #f0f0f0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🚀 Лабораторная работа 7</h1>
    <p>Асинхронная обработка заявок "Регистрация студента" через RabbitMQ</p>
    <div class="status info">
        <h3>ℹ️ Информация:</h3>
        <p>Эта страница не обрабатывает заявки. Заявки отправляются в очередь RabbitMQ и обрабатываются <code>worker.php</code>.</p>
        <p>Для запуска воркера выполните: <code>docker exec -it lab5_php php /var/www/html/worker.php</code></p>
        <p>Проверить статус очереди можно через веб-интерфейс RabbitMQ: <a href="http://localhost:15672" target="_blank">http://localhost:15672</a></p>
    </div>
    <div class="status success">
        <h3>✅ Статус:</h3>
        <p>Интеграция RabbitMQ завершена. Форма "Регистрация студента" теперь отправляет данные в очередь <code>student_registrations</code>.</p>
    </div>
    <a href="/index.html">🏠 На главную</a>
</body>
</html>
