<?php
header('Content-Type: text/html; charset=utf-8');
include 'Appointment.php';

try {
    $pdo = new PDO('mysql:host=db;dbname=clinic_db', 'clinic_user', 'clinic_pass');
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $appointment = new Appointment($pdo);
    $appointment->createTables();
    $stats = $appointment->getStats();
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Медицинский центр - Панель управления</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Медицинский центр "Здоровье"</h1>
            <p>Панель управления записями пациентов</p>
        </div>
        
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ Ошибка подключения к БД: <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>📊 Общая статистика</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_doctors']; ?></div>
                            <div class="stat-label">Врачей в штате</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                            <div class="stat-label">Всего записей</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['today_appointments']; ?></div>
                            <div class="stat-label">Записей на сегодня</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">📈</div>
                            <div class="stat-label">Система активна</div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2>👨‍⚕️ Популярные врачи</h2>
                    <?php if (count($stats['popular_doctors']) > 0): ?>
                        <?php foreach ($stats['popular_doctors'] as $doctor): ?>
                            <div class="doctor-card">
                                <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                <p><strong>Специализация:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                <p><strong>Записей:</strong> <?php echo $doctor['appointments']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Нет данных о популярности врачей.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="nav-links">
                <a href="appointment.html" class="btn">📅 Новая запись</a>
                <a href="appointments.php" class="btn btn-secondary">📋 Все записи</a>
                <a href="doctors.php" class="btn">👨‍⚕️ Управление врачами</a>
                <a href="stats.php" class="btn btn-success">📈 Детальная статистика</a>
                <a href="search.php" class="btn">🔍 Поиск записей</a>
            </div>
        </div>
    </div>
</body>
</html>
