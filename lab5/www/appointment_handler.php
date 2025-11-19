<?php
// www/appointment_handler.php
// Этот файл будет принимать POST-запрос от формы "Запись к врачу" и отправлять данные в очередь RabbitMQ

session_start(); // Если нужна сессия

// Подключаем автозагрузчик Composer и QueueManager
require_once __DIR__ . '/vendor/autoload.php';
use App\Queue\QueueManager;

header('Content-Type: application/json; charset=utf-8');

try {
    $qm = new QueueManager();
    // Подготовим данные для отправки (берём из $_POST)
    // Эти ключи должны соответствовать полям формы "Запись к врачу"
    $queueData = [
        'full_name' => $_POST['name'] ?? 'Аноним',
        'age' => $_POST['age'] ?? 'N/A',
        'doctor' => $_POST['doctor'] ?? 'N/A',
        'visit_type' => $_POST['visit_type'] ?? 'N/A',
        'first_visit' => isset($_POST['first_visit']) ? true : false, // checkbox
        'submitted_at' => date('Y-m-d H:i:s'),
        'session_id' => session_id() // Если сессии используются
    ];
    $qm->publish($queueData);
    error_log("Сообщение отправлено в очередь RabbitMQ: " . json_encode($queueData));
    echo json_encode(['status' => 'success', 'message' => 'Заявка успешно отправлена в очередь на асинхронную обработку.']);
} catch (Exception $e) {
    error_log("Ошибка отправки в очередь RabbitMQ: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при отправке заявки в очередь.']);
}

?>
