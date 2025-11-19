<?php
// www/manual_autoloader.php (простой автозагрузчик для App\Queue)
spl_autoload_register(function ($class) {
    // Определяем пространство имён App\Queue
    $prefix = 'App\\Queue\\';
    // Проверяем, начинается ли имя класса с префикса
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Если нет, автозагрузчик не может обработать этот класс
        return;
    }
    // Получаем относительное имя класса
    $relativeClass = substr($class, $len);
    // Заменяем разделители пространства имён на разделители директорий
    $fileName = __DIR__ . '/src/Queue/' . str_replace('\\', '/', $relativeClass) . '.php';
    // Если файл существует, подключаем его
    if (file_exists($fileName)) {
        require $fileName;
    }
});
?>
