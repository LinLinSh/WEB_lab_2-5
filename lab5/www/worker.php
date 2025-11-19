<?php
// www/worker.php
// Этот скрипт должен запускаться вручную или через процесс-менеджер (supervisor)
// Он подключается к очереди RabbitMQ и обрабатывает сообщения

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php'; // Предполагаем, что автозагрузчик находится в корне проекта (www/)

use App\Queue\QueueManager;

echo "👷 Рабочий (Consumer) запущен для 'Запись к врачу' (Вариант 12)...\n";

$qm = new QueueManager();

$messageHandler = function($data) {
    // Здесь происходит асинхронная обработка данных
    // Например, сохранение в БД, отправка email/SMS, обновление статуса и т.д.
    // Имитация обработки (например, запись в лог)
    echo "📥 Получена заявка: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

    // Имитация длительной операции
    sleep(2);

    echo "✅ Заявка обработана.\n";

    // Запись в лог-файл (можно в БД)
    $logEntry = date('Y-m-d H:i:s') . " - Processed appointment request: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents(__DIR__ . '/worker_processing.log', $logEntry, FILE_APPEND | LOCK_EX);
};

// Запускаем обработку сообщений
try {
    $qm->consume($messageHandler);
} catch (Exception $e) {
    echo "❌ Ошибка в работе воркера: " . $e->getMessage() . "\n";
    error_log("Worker error: " . $e->getMessage());
} finally {
    $qm->close();
    echo "🚪 Рабочий завершил работу.\n";
}

?>
