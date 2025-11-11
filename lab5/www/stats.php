<?php
header('Content-Type: text/html; charset=utf-8');
include 'Appointment.php';

try {
    $pdo = new PDO('mysql:host=db;dbname=clinic_db', 'clinic_user', 'clinic_pass');
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $appointment = new Appointment($pdo);
    $stats = $appointment->getStats();
    $appointments = $appointment->getAllAppointments();
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика - Медицинский центр</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📈 Детальная статистика</h1>
            <p>Аналитика работы медицинского центра</p>
        </div>
        
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ Ошибка загрузки данных: <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_doctors']; ?></div>
                        <div class="stat-label">Врачей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                        <div class="stat-label">Всего записей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['today_appointments']; ?></div>
                        <div class="stat-label">На сегодня</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">📊</div>
                        <div class="stat-label">Аналитика</div>
                    </div>
                </div>
                
                <div class="card">
                    <h2>📊 Статистика по статусам записей</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th>Количество</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['appointments_by_status'] as $status): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $status_names = [
                                                'pending' => '⏳ Ожидание',
                                                'confirmed' => '✅ Подтверждено', 
                                                'completed' => '🏁 Завершено',
                                                'cancelled' => '❌ Отменено'
                                            ];
                                            echo $status_names[$status['status']];
                                            ?>
                                        </td>
                                        <td><?php echo $status['count']; ?></td>
                                        <td><?php echo round(($status['count'] / $stats['total_appointments']) * 100, 2); ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card">
                    <h2>👑 Самые популярные врачи</h2>
                    <?php if (count($stats['popular_doctors']) > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Врач</th>
                                        <th>Специализация</th>
                                        <th>Количество записей</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['popular_doctors'] as $doctor): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($doctor['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                            <td><?php echo $doctor['appointments']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Нет данных о популярности врачей.</p>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <h2>📅 Последние записи</h2>
                    <?php if (count($appointments) > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Пациент</th>
                                        <th>Врач</th>
                                        <th>Дата</th>
                                        <th>Время</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentAppointments = array_slice($appointments, 0, 10);
                                    foreach ($recentAppointments as $apt): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                                            <td><?php echo $apt['appointment_date']; ?></td>
                                            <td><?php echo $apt['appointment_time']; ?></td>
                                            <td>
                                                <?php 
                                                $status_names = [
                                                    'pending' => '⏳',
                                                    'confirmed' => '✅',
                                                    'completed' => '🏁',
                                                    'cancelled' => '❌'
                                                ];
                                                echo $status_names[$apt['status']];
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Нет данных о записях.</p>
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
