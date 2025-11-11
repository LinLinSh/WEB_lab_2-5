<?php
header('Content-Type: text/html; charset=utf-8');
include 'Appointment.php';

$search_results = [];
$search_term = '';

if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    try {
        $pdo = new PDO('mysql:host=db;dbname=clinic_db', 'clinic_user', 'clinic_pass');
        $pdo->exec("SET NAMES 'utf8mb4'");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $appointment = new Appointment($pdo);
        $search_term = $_GET['search_term'];
        $search_results = $appointment->searchAppointments($search_term);
        
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск записей - Медицинский центр</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Поиск записей пациентов</h1>
            <p>Поиск по имени пациента, телефону или врачу</p>
        </div>
        
        <div class="content">
            <!-- Форма поиска -->
            <div class="card">
                <h2>🔎 Поиск</h2>
                <form method="GET">
                    <div class="form-group">
                        <label>Поисковый запрос:</label>
                        <input type="text" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>" 
                               placeholder="Введите имя пациента, телефон или врача..." required>
                    </div>
                    <button type="submit" name="search" class="btn">🔍 Найти</button>
                </form>
            </div>
            
            <!-- Результаты поиска -->
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ Ошибка поиска: <?php echo $error; ?>
                </div>
            <?php elseif (isset($_GET['search'])): ?>
                <div class="card">
                    <h2>📋 Результаты поиска</h2>
                    <?php if (count($search_results) > 0): ?>
                        <p><strong>Найдено записей:</strong> <?php echo count($search_results); ?></p>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Пациент</th>
                                        <th>Телефон</th>
                                        <th>Врач</th>
                                        <th>Дата</th>
                                        <th>Время</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results as $result): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($result['patient_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($result['patient_phone']); ?></td>
                                            <td><?php echo htmlspecialchars($result['doctor_name']); ?></td>
                                            <td><?php echo $result['appointment_date']; ?></td>
                                            <td><?php echo $result['appointment_time']; ?></td>
                                            <td>
                                                <?php 
                                                $status_names = [
                                                    'pending' => '⏳ Ожидание',
                                                    'confirmed' => '✅ Подтверждено',
                                                    'completed' => '🏁 Завершено',
                                                    'cancelled' => '❌ Отменено'
                                                ];
                                                echo $status_names[$result['status']];
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="message info">
                            ℹ️ По вашему запросу "<?php echo htmlspecialchars($search_term); ?>" ничего не найдено.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="nav-links">
                <a href="index.php" class="btn btn-secondary">🏠 На главную</a>
                <a href="appointments.php" class="btn">📋 Все записи</a>
            </div>
        </div>
    </div>
</body>
</html>
