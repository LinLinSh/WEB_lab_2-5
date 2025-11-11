<?php
header(''Content-Type: text/html; charset=utf-8'');
include ''Appointment.php'';

try {
    $pdo = new PDO(
        ''mysql:host=db;dbname=clinic_db'',
        ''clinic_user'',
        ''clinic_pass''
    );
    $pdo->exec("SET NAMES ''utf8mb4''");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $appointment = new Appointment($pdo);
    $appointment->createTables();
    
    $message = '''';
    $messageType = '''';
    
    if ($_POST) {
        $patient_name = $_POST[''patient_name''];
        $patient_phone = $_POST[''patient_phone''];
        $doctor_id = $_POST[''doctor_id''];
        $appointment_date = $_POST[''appointment_date''];
        $appointment_time = $_POST[''appointment_time''];
        $symptoms = $_POST[''symptoms''];
        
        if ($appointment->addAppointment($patient_name, $patient_phone, $doctor_id, $appointment_date, $appointment_time, $symptoms)) {
            $message = ''🎉 Запись на прием успешно создана! Мы свяжемся с вами для подтверждения.'';
            $messageType = ''success'';
        } else {
            $message = ''❌ Ошибка при создании записи. Пожалуйста, попробуйте еще раз.'';
            $messageType = ''error'';
        }
    }
    
    $appointments = $appointment->getAllAppointments();
    
} catch(PDOException $e) {
    $message = ''❌ Ошибка базы данных: '' . $e->getMessage();
    $messageType = ''error'';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат записи - Медицинский центр</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Результат записи</h1>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_POST) && $messageType == ''success''): ?>
                <div class="card">
                    <h2>📄 Детали записи</h2>
                    <p><strong>Пациент:</strong> <?php echo htmlspecialchars($_POST[''patient_name'']); ?></p>
                    <p><strong>Телефон:</strong> <?php echo htmlspecialchars($_POST[''patient_phone'']); ?></p>
                    <p><strong>Дата приема:</strong> <?php echo $_POST[''appointment_date'']; ?></p>
                    <p><strong>Время приема:</strong> <?php echo $_POST[''appointment_time'']; ?></p>
                    <?php if (!empty($_POST[''symptoms''])): ?>
                        <p><strong>Жалобы:</strong> <?php echo htmlspecialchars($_POST[''symptoms'']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (count($appointments) > 0): ?>
                <div class="card">
                    <h2>👥 Последние записи</h2>
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
                                $recentAppointments = array_slice($appointments, 0, 5);
                                foreach ($recentAppointments as $apt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($apt[''patient_name'']); ?></td>
                                        <td><?php echo htmlspecialchars($apt[''doctor_name'']); ?></td>
                                        <td><?php echo $apt[''appointment_date'']; ?></td>
                                        <td><?php echo $apt[''appointment_time'']; ?></td>
                                        <td>
                                            <?php 
                                            $statusText = [
                                                ''pending'' => ''⏳ Ожидание'',
                                                ''confirmed'' => ''✅ Подтверждено'',
                                                ''completed'' => ''🏁 Завершено'',
                                                ''cancelled'' => ''❌ Отменено''
                                            ];
                                            echo $statusText[$apt[''status'']];
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="nav-links">
                <a href="appointment.html" class="btn">📅 Новая запись</a>
                <a href="appointments.php" class="btn btn-secondary">📋 Все записи</a>
                <a href="index.php" class="btn">🏠 На главную</a>
            </div>
        </div>
    </div>
</body>
</html>
