<?php
class Appointment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function createTables() {
        // Таблица врачей
        $sql = "CREATE TABLE IF NOT EXISTS doctors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            specialization VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            work_schedule TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive') DEFAULT 'active'
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->pdo->exec($sql);
        
        // Таблица записей
        $sql = "CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_name VARCHAR(100) NOT NULL,
            patient_phone VARCHAR(20) NOT NULL,
            doctor_id INT NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            symptoms TEXT,
            status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->pdo->exec($sql);
        
        // Добавляем тестовых врачей если таблица пустая
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM doctors");
        if ($stmt->fetch()['count'] == 0) {
            $doctors = [
                ['Иванова Анна Петровна', 'Терапевт', '+7-999-123-45-67', 'ivanova@clinic.ru', 'Пн-Пт 9:00-18:00'],
                ['Петров Сергей Михайлович', 'Стоматолог', '+7-999-123-45-68', 'petrov@clinic.ru', 'Вт-Сб 10:00-19:00'],
                ['Сидорова Елена Владимировна', 'Офтальмолог', '+7-999-123-45-69', 'sidorova@clinic.ru', 'Пн-Ср 8:00-17:00'],
                ['Козлов Дмитрий Александрович', 'Хирург', '+7-999-123-45-70', 'kozlov@clinic.ru', 'Пн-Чт 9:00-16:00'],
                ['Николаева Мария Сергеевна', 'Невролог', '+7-999-123-45-71', 'nikolaeva@clinic.ru', 'Пн-Пт 10:00-17:00']
            ];
            
            $sql = "INSERT INTO doctors (name, specialization, phone, email, work_schedule) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            foreach ($doctors as $doctor) {
                $stmt->execute($doctor);
            }
        }
    }
    
    public function addAppointment($patient_name, $patient_phone, $doctor_id, $appointment_date, $appointment_time, $symptoms) {
        $sql = "INSERT INTO appointments (patient_name, patient_phone, doctor_id, appointment_date, appointment_time, symptoms) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$patient_name, $patient_phone, $doctor_id, $appointment_date, $appointment_time, $symptoms]);
    }
    
    public function getAllAppointments() {
        $sql = "SELECT a.*, d.name as doctor_name, d.specialization 
                FROM appointments a 
                JOIN doctors d ON a.doctor_id = d.id 
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateAppointmentStatus($id, $status) {
        $sql = "UPDATE appointments SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    public function deleteAppointment($id) {
        $sql = "DELETE FROM appointments WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getAllDoctors() {
        $sql = "SELECT * FROM doctors WHERE status = 'active' ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDoctorById($id) {
        $sql = "SELECT * FROM doctors WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addDoctor($name, $specialization, $phone, $email, $work_schedule) {
        $sql = "INSERT INTO doctors (name, specialization, phone, email, work_schedule) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $specialization, $phone, $email, $work_schedule]);
    }
    
    public function updateDoctor($id, $name, $specialization, $phone, $email, $work_schedule) {
        $sql = "UPDATE doctors SET name = ?, specialization = ?, phone = ?, email = ?, work_schedule = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $specialization, $phone, $email, $work_schedule, $id]);
    }
    
    public function deleteDoctor($id) {
        $sql = "UPDATE doctors SET status = 'inactive' WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getStats() {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total_appointments FROM appointments";
        $stmt = $this->pdo->query($sql);
        $stats['total_appointments'] = $stmt->fetch()['total_appointments'];
        
        $sql = "SELECT COUNT(*) as total_doctors FROM doctors WHERE status = 'active'";
        $stmt = $this->pdo->query($sql);
        $stats['total_doctors'] = $stmt->fetch()['total_doctors'];
        
        $sql = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
        $stmt = $this->pdo->query($sql);
        $stats['appointments_by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "SELECT d.name, d.specialization, COUNT(a.id) as appointments 
                FROM doctors d 
                LEFT JOIN appointments a ON d.id = a.doctor_id 
                WHERE d.status = 'active'
                GROUP BY d.id 
                ORDER BY appointments DESC 
                LIMIT 5";
        $stmt = $this->pdo->query($sql);
        $stats['popular_doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as today_appointments FROM appointments WHERE appointment_date = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$today]);
        $stats['today_appointments'] = $stmt->fetch()['today_appointments'];
        
        return $stats;
    }
    
    public function searchAppointments($search_term) {
        $sql = "SELECT a.*, d.name as doctor_name, d.specialization 
                FROM appointments a 
                JOIN doctors d ON a.doctor_id = d.id 
                WHERE a.patient_name LIKE ? OR a.patient_phone LIKE ? OR d.name LIKE ?
                ORDER BY a.appointment_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $search_pattern = "%$search_term%";
        $stmt->execute([$search_pattern, $search_pattern, $search_pattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
