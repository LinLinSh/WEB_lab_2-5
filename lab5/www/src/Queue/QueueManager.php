<?php
// www/src/Queue/QueueManager.php
namespace App\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueManager {
    private $channel;
    // Используем другое имя очереди для "Записи к врачу" (вариант 12)
    private $queueName = 'appointment_requests';

    public function __construct() {
        // Подключение к RabbitMQ через Docker
        // Имя хоста - это имя сервиса из docker-compose.yml
        $connection = new AMQPStreamConnection('lab5_rabbit', 5672, 'guest', 'guest');
        $this->channel = $connection->channel();

        // Объявляем очередь (создастся, если не существует)
        // durable=true означает, что очередь переживёт перезапуск сервера
        $this->channel->queue_declare($this->queueName, false, true, false, false);
    }

    public function publish($data) {
        // Преобразуем данные в JSON
        $msgBody = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Ошибка JSON при публикации: ' . json_last_error_msg());
        }

        // Создаём сообщение с указанием, что оно должно быть сохранено на диск (durable)
        $msg = new AMQPMessage(
            $msgBody,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT] // Убедимся, что сообщение сохраняется
        );

        // Публикуем сообщение в очередь
        $this->channel->basic_publish($msg, '', $this->queueName);
    }

    public function consume(callable $callback) {
        // Регистрируем обработчик сообщений
        $this->channel->basic_consume($this->queueName, '', false, true, false, false, function($msg) use ($callback) {
            // Десериализуем тело сообщения
            $data = json_decode($msg->body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Ошибка JSON в сообщении из очереди: " . $msg->body);
                // Отклоняем сообщение, так как его нельзя обработать
                $msg->nack(true); // requeue = true, чтобы вернуть в очередь
                return;
            }

            try {
                // Вызываем переданный callback для обработки данных
                $callback($data);
                // Подтверждаем получение сообщения
                $msg->ack();
            } catch (\Exception $e) {
                // В случае ошибки обработки, логгируем и отклоняем сообщение
                error_log("Ошибка обработки сообщения из очереди: " . $e->getMessage());
                $msg->nack(true); // requeue = true, чтобы вернуть в очередь
            }
        });

        // Запускаем цикл ожидания сообщений
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    // Закрываем канал
    public function close() {
        if ($this->channel) {
            $this->channel->close();
        }
    }
}
?>
