<?php
header('Content-Type: text/html; charset=utf-8');
include 'Appointment.php';

try {
    $pdo = new PDO('mysql:host=db;dbname=clinic_db', 'clinic_user', 'clinic_pass');
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $appointment = new Appointment($pdo);
    
    // Обработка добавления врача
    if (isset($_POST['add_doctor'])) {
        $appointment->addDoctor($_POST['name'], $_POST['specialization'], $_POST['phone'], $_POST['email'], $_POST['work_schedule']);
    }
    
    // Обработка удаления врача
    if (isset($_POST['delete_doctor'])) {
        $appointment->deleteDoctor($_POST['doctor_id']);
    }
    
    $doctors = $appointment->getAllDoctors();
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление врачами - Медицинский центр</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👨‍⚕️ Управление врачами</h1>
            <p>Добавление и редактирование информации о врачах</p>
        </div>
        
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ Ошибка загрузки данных: <?php echo $error; ?>
                </div>
            <?php else: ?>
                <!-- Форма добавления врача -->
                <div class="card">
                    <h2>➕ Добавить нового врача</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>ФИО врача:</label>
                            <input type="text" name="name" required placeholder="Иванова Анна Петровна">
                        </div>
                        <div class="form-group">
                            <label>Специализация:</label>
                            <input type="text" name="specialization" required placeholder="Терапевт">
                        </div>
                        <div class="form-group">
                            <label>Телефон:</label>
                            <input type="tel" name="phone" placeholder="+7-999-123-45-67">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" placeholder="doctor@clinic.ru">
                        </div>
                        <div class="form-group">
                            <label>График работы:</label>
                            <input type="text" name="work_schedule" placeholder="Пн-Пт 9:00-18:00">
                        </div>
                        <button type="submit" name="add_doctor" class="btn btn-success">✅ Добавить врача</button>
                    </form>
                </div>
                
                <!-- Список врачей -->
                <div class="card">
                    <h2>📋 Список врачей</h2>
                    <?php if (count($doctors) > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ФИО</th>
                                        <th>Специализация</th>
                                        <th>Телефон</th>
                                        <th>Email</th>
                                        <th>График работы</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($doctor['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                            <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                            <td><?php echo htmlspecialchars($doctor['work_schedule']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                                    <button type="submit" name="delete_doctor" class="btn btn-danger" onclick="return confirm('Удалить врача?')">🗑️ Удалить</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p><strong>Всего врачей:</strong> <?php echo count($doctors); ?></p>
                    <?php else: ?>
                        <div class="message info">
                            ℹ️ В системе пока нет зарегистрированных врачей.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="nav-links">
                <a href="index.php" class="btn btn-secondary">🏠 На главную</a>
                <a href="appointments.php" class="btn">📋 Записи пациентов</a>
            </div>
        </div>
    </div>
</body>
</html>
