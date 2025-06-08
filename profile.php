<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();

// Проверяем авторизацию
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$database = new Database();
$conn = $database->getConnection();

// Получаем информацию о пользователе
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем активные записи пользователя
$query = "SELECT b.*, s.date_time, p.name as program_name, p.duration_minutes, 
                 t.name as trainer_name, b.status
          FROM bookings b
          JOIN schedule s ON b.schedule_id = s.id
          JOIN programs p ON s.program_id = p.id
          JOIN trainers t ON s.trainer_id = t.id
          WHERE b.user_id = :user_id AND s.date_time >= NOW() AND b.status = 'active'
          ORDER BY s.date_time";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$upcomingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем историю тренировок
$query = "SELECT b.*, s.date_time, p.name as program_name, p.duration_minutes, 
                 t.name as trainer_name, b.status
          FROM bookings b
          JOIN schedule s ON b.schedule_id = s.id
          JOIN programs p ON s.program_id = p.id
          JOIN trainers t ON s.trainer_id = t.id
          WHERE b.user_id = :user_id AND (s.date_time < NOW() OR b.status != 'active')
          ORDER BY s.date_time DESC
          LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$pastBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем статистику пользователя
$query = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

$query = "SELECT COUNT(*) as completed_bookings FROM bookings WHERE user_id = :user_id AND status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$completedBookings = $stmt->fetch(PDO::FETCH_ASSOC)['completed_bookings'];

$message = '';
$message_type = '';

// Обработка обновления профиля
if ($_POST && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    if (empty($first_name) || empty($last_name)) {
        $message = 'Имя и фамилия обязательны для заполнения';
        $message_type = 'error';
    } else {
        try {
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':user_id', $user['id']);
            
            if ($stmt->execute()) {
                $message = 'Профиль успешно обновлен';
                $message_type = 'success';
                // Обновляем информацию в сессии
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                // Перезагружаем информацию о пользователе
                $userInfo['first_name'] = $first_name;
                $userInfo['last_name'] = $last_name;
                $userInfo['phone'] = $phone;
            }
        } catch(PDOException $exception) {
            $message = 'Ошибка обновления профиля';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Приветствие -->
        <section class="section" style="padding: 1.5rem 0; background: linear-gradient(135deg, #2980B9, #E74C3C);">
            <div class="container">
                <div style="color: white; text-align: center;">
                    <h1 style="color: white;">Добро пожаловать, <?php echo htmlspecialchars($userInfo['first_name']); ?>!</h1>
                    <p>Управляйте своими тренировками и следите за прогрессом</p>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Статистика пользователя -->
                <div class="grid grid-4" style="margin-bottom: 2rem;">
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--accent-blue), var(--hover-blue));">
                        <div class="stats-number"><?php echo count($upcomingBookings); ?></div>
                        <p class="stats-label">Предстоящих тренировок</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--success-green), #45A049);">
                        <div class="stats-number"><?php echo $completedBookings; ?></div>
                        <p class="stats-label">Завершенных тренировок</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--energy-orange), var(--hover-orange));">
                        <div class="stats-number"><?php echo $totalBookings; ?></div>
                        <p class="stats-label">Всего записей</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                        <div class="stats-number"><?php echo date('d', strtotime($userInfo['registration_date'])); ?></div>
                        <p class="stats-label">Дней с нами</p>
                    </div>
                </div>

                <div class="grid grid-3">
                    <!-- Информация о профиле -->
                    <div class="card">
                        <h3>Мой профиль</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Имя пользователя</label>
                                <input type="text" id="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($userInfo['username']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($userInfo['email']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="first_name">Имя *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($userInfo['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Фамилия *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($userInfo['last_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Телефон</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($userInfo['phone']); ?>">
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">
                                Обновить профиль
                            </button>
                        </form>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #555;">
                            <small style="color: #999;">
                                Регистрация: <?php echo date('d.m.Y', strtotime($userInfo['registration_date'])); ?>
                            </small>
                        </div>
                    </div>

                    <!-- Быстрые действия -->
                    <div class="card">
                        <h3>Быстрые действия</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <a href="schedule.php" class="btn btn-primary">Записаться на тренировку</a>
                            <a href="programs.php" class="btn btn-secondary">Посмотреть программы</a>
                            <a href="trainers.php" class="btn btn-secondary">Наши тренеры</a>
                            <a href="memberships.php" class="btn btn-secondary">Купить абонемент</a>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background-color: rgba(0,0,0,0.2); border-radius: 8px;">
                            <h4 style="font-size: 1.2rem;">Ближайшая тренировка</h4>
                            <?php if (!empty($upcomingBookings)): ?>
                                <?php $nextBooking = $upcomingBookings[0]; ?>
                                <p style="margin: 0.5rem 0;"><strong><?php echo htmlspecialchars($nextBooking['program_name']); ?></strong></p>
                                <p style="margin: 0; color: var(--accent-blue);">
                                    <?php echo date('d.m.Y в H:i', strtotime($nextBooking['date_time'])); ?>
                                </p>
                                <p style="margin: 0; color: var(--medium-gray); font-size: 0.8rem;">
                                    Тренер: <?php echo htmlspecialchars($nextBooking['trainer_name']); ?>
                                </p>
                            <?php else: ?>
                                <p style="color: var(--medium-gray); font-style: italic;">Нет записей</p>
                                <a href="schedule.php" class="btn btn-primary" style="margin-top: 0.5rem; font-size: 0.8rem; padding: 0.5rem 1rem;">Записаться</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Уведомления и советы -->
                    <div class="card">
                        <h3>Уведомления</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php if (count($upcomingBookings) > 0): ?>
                                <div style="padding: 0.8rem; background-color: rgba(76, 175, 80, 0.1); border-radius: 6px; border-left: 4px solid var(--success-green);">
                                    <strong style="color: var(--success-green);">У вас <?php echo count($upcomingBookings); ?> предстоящих тренировок</strong>
                                </div>
                            <?php else: ?>
                                <div style="padding: 0.8rem; background-color: rgba(255, 193, 7, 0.1); border-radius: 6px; border-left: 4px solid #ffc107;">
                                    <strong style="color: #ffc107;">Нет записей на тренировки</strong><br>
                                    <small>Запишитесь на тренировку прямо сейчас!</small>
                                </div>
                            <?php endif; ?>
                            
                            <div style="padding: 0.8rem; background-color: rgba(0, 181, 226, 0.1); border-radius: 6px; border-left: 4px solid var(--accent-blue);">
                                <strong style="color: var(--accent-blue);">Совет дня:</strong><br>
                                <small>Не забывайте пить воду во время тренировок и приходить за 10 минут до начала!</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Предстоящие тренировки -->
                <div style="margin-top: 2rem;">
                    <h2>Предстоящие тренировки</h2>
                    <?php if (empty($upcomingBookings)): ?>
                        <div class="card">
                            <p style="text-align: center; color: var(--medium-gray); font-style: italic;">
                                У вас нет записей на предстоящие тренировки
                            </p>
                            <div style="text-align: center;">
                                <a href="schedule.php" class="btn btn-primary">Записаться на тренировку</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-2">
                            <?php foreach ($upcomingBookings as $booking): ?>
                                <div class="card">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                        <h4><?php echo htmlspecialchars($booking['program_name']); ?></h4>
                                        <span class="badge badge-active">Активна</span>
                                    </div>
                                    
                                    <div style="margin-bottom: 1rem;">
                                        <p><strong>Дата:</strong> <?php echo date('d.m.Y', strtotime($booking['date_time'])); ?></p>
                                        <p><strong>Время:</strong> <?php echo date('H:i', strtotime($booking['date_time'])); ?></p>
                                        <p><strong>Длительность:</strong> <?php echo $booking['duration_minutes']; ?> минут</p>
                                        <p><strong>Тренер:</strong> <?php echo htmlspecialchars($booking['trainer_name']); ?></p>
                                    </div>
                                    
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem 1rem;" 
                                                onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                            Отменить запись
                                        </button>
                                        <small style="color: var(--medium-gray); align-self: center; margin-left: 0.5rem;">
                                            Отмена за 2 часа до начала
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- История тренировок -->
                <div style="margin-top: 2rem;">
                    <h2>История тренировок</h2>
                    <?php if (empty($pastBookings)): ?>
                        <div class="card">
                            <p style="text-align: center; color: var(--medium-gray); font-style: italic;">
                                История тренировок пуста
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div style="overflow-x: auto;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Программа</th>
                                            <th>Тренер</th>
                                            <th>Длительность</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pastBookings as $booking): ?>
                                            <tr>
                                                <td><?php echo date('d.m.Y H:i', strtotime($booking['date_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($booking['program_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                                                <td><?php echo $booking['duration_minutes']; ?> мин</td>
                                                <td>
                                                    <span class="badge badge-<?php echo $booking['status']; ?>">
                                                        <?php 
                                                        $statusNames = [
                                                            'completed' => 'Завершена',
                                                            'cancelled' => 'Отменена',
                                                            'active' => 'Активна'
                                                        ];
                                                        echo $statusNames[$booking['status']] ?? ucfirst($booking['status']); 
                                                        ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        function cancelBooking(bookingId) {
            if (!confirm('Вы уверены, что хотите отменить запись на тренировку?')) {
                return;
            }
            
            fetch('ajax/cancel-booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Произошла ошибка при отмене записи', 'error');
            });
        }
    </script>
</body>
</html>
