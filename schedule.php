<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Получаем расписание на неделю
$query = "SELECT s.*, p.name as program_name, p.duration_minutes, p.max_participants, 
                 t.name as trainer_name, p.difficulty_level,
                 (s.available_spots - s.booked_spots) as free_spots
          FROM schedule s
          JOIN programs p ON s.program_id = p.id
          JOIN trainers t ON s.trainer_id = t.id
          WHERE s.is_active = 1 AND s.date_time >= NOW()
          ORDER BY s.date_time";
$stmt = $conn->prepare($query);
$stmt->execute();
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем по дням
$scheduleByDay = [];
foreach ($schedule as $session) {
    $date = date('Y-m-d', strtotime($session['date_time']));
    $dayName = date('l', strtotime($session['date_time']));
    $dayNames = [
        'Monday' => 'Понедельник',
        'Tuesday' => 'Вторник', 
        'Wednesday' => 'Среда',
        'Thursday' => 'Четверг',
        'Friday' => 'Пятница',
        'Saturday' => 'Суббота',
        'Sunday' => 'Воскресенье'
    ];
    
    $scheduleByDay[$date] = [
        'day_name' => $dayNames[$dayName],
        'date' => $date,
        'sessions' => []
    ];
}

foreach ($schedule as $session) {
    $date = date('Y-m-d', strtotime($session['date_time']));
    if (isset($scheduleByDay[$date])) {
        $scheduleByDay[$date]['sessions'][] = $session;
    }
}

$levelNames = [
    'beginner' => 'Начинающий',
    'intermediate' => 'Средний',
    'advanced' => 'Продвинутый'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Герой секция -->
        <section class="hero" style="padding: 4rem 0;">
            <div class="container">
                <h1>Расписание тренировок</h1>
                <p>Выберите удобное время для тренировок и записывайтесь онлайн</p>
            </div>
        </section>

        <!-- Фильтры -->
        <section class="section" style="padding: 2rem 0; background-color: #f8f9fa;">
            <div class="container">
                <div class="card">
                    <h3>Фильтры</h3>
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: center;">
                        <div>
                            <label for="programFilter">Программа:</label>
                            <select id="programFilter" class="form-control" style="width: auto; display: inline-block; margin-left: 0.5rem;">
                                <option value="">Все программы</option>
                                <?php
                                $programQuery = "SELECT DISTINCT name FROM programs WHERE is_active = 1 ORDER BY name";
                                $programStmt = $conn->prepare($programQuery);
                                $programStmt->execute();
                                $programs = $programStmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($programs as $program):
                                ?>
                                    <option value="<?php echo htmlspecialchars($program['name']); ?>">
                                        <?php echo htmlspecialchars($program['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="trainerFilter">Тренер:</label>
                            <select id="trainerFilter" class="form-control" style="width: auto; display: inline-block; margin-left: 0.5rem;">
                                <option value="">Все тренеры</option>
                                <?php
                                $trainerQuery = "SELECT DISTINCT name FROM trainers WHERE is_active = 1 ORDER BY name";
                                $trainerStmt = $conn->prepare($trainerQuery);
                                $trainerStmt->execute();
                                $trainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($trainers as $trainer):
                                ?>
                                    <option value="<?php echo htmlspecialchars($trainer['name']); ?>">
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button onclick="clearFilters()" class="btn btn-secondary">Сбросить фильтры</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Расписание по дням -->
        <section class="section">
            <div class="container">
                <?php foreach ($scheduleByDay as $dayData): ?>
                    <div class="day-schedule" style="margin-bottom: 3rem;">
                        <h2 style="color: #2980B9; border-bottom: 2px solid #2980B9; padding-bottom: 0.5rem;">
                            <?php echo $dayData['day_name']; ?>, <?php echo date('d.m.Y', strtotime($dayData['date'])); ?>
                        </h2>
                        
                        <?php if (empty($dayData['sessions'])): ?>
                            <p style="color: #666; font-style: italic; margin: 2rem 0;">На этот день тренировки не запланированы</p>
                        <?php else: ?>
                            <div class="grid grid-2" style="margin-top: 1.5rem;">
                                <?php foreach ($dayData['sessions'] as $session): ?>
                                    <div class="card session-card" 
                                         data-program="<?php echo htmlspecialchars($session['program_name']); ?>"
                                         data-trainer="<?php echo htmlspecialchars($session['trainer_name']); ?>">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                            <div>
                                                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($session['program_name']); ?></h3>
                                                <p style="color: #E74C3C; font-weight: 500; margin: 0;">
                                                    <?php echo date('H:i', strtotime($session['date_time'])); ?> 
                                                    (<?php echo $session['duration_minutes']; ?> мин)
                                                </p>
                                            </div>
                                            <span class="badge badge-<?php echo $session['difficulty_level']; ?>">
                                                <?php echo $levelNames[$session['difficulty_level']]; ?>
                                            </span>
                                        </div>
                                        
                                        <p style="margin-bottom: 1rem;">
                                            <strong>Тренер:</strong> <?php echo htmlspecialchars($session['trainer_name']); ?>
                                        </p>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                            <div>
                                                <span style="color: <?php echo $session['free_spots'] > 0 ? '#27ae60' : '#e74c3c'; ?>; font-weight: 500;">
                                                    <?php if ($session['free_spots'] > 0): ?>
                                                        Свободно мест: <?php echo $session['free_spots']; ?>
                                                    <?php else: ?>
                                                        Мест нет
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <small style="color: #666;">
                                                Макс: <?php echo $session['max_participants']; ?> чел.
                                            </small>
                                        </div>
                                        
                                        <div>
                                            <?php if ($user): ?>
                                                <?php if ($session['free_spots'] > 0): ?>
                                                    <button class="btn btn-primary" onclick="bookSession(<?php echo $session['id']; ?>)">
                                                        Записаться
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary" disabled>
                                                        Мест нет
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="login.php" class="btn btn-primary">Войти для записи</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Информация о записи -->
        <section class="section" style="background-color: #f8f9fa;">
            <div class="container">
                <h2 class="section-title">Правила записи</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <h3>Как записаться</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Зарегистрируйтесь на сайте или войдите в личный кабинет</li>
                            <li>Выберите подходящую тренировку в расписании</li>
                            <li>Нажмите кнопку "Записаться"</li>
                            <li>Подтвердите запись</li>
                        </ul>
                    </div>
                    <div class="card">
                        <h3>Важная информация</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Отменить запись можно за 2 часа до начала</li>
                            <li>Приходите за 10-15 минут до начала тренировки</li>
                            <li>При опоздании более чем на 10 минут вход на тренировку не разрешается</li>
                            <li>Обязательно имейте при себе полотенце и воду</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Фильтрация расписания
        function filterSchedule() {
            const programFilter = document.getElementById('programFilter').value.toLowerCase();
            const trainerFilter = document.getElementById('trainerFilter').value.toLowerCase();
            const sessionCards = document.querySelectorAll('.session-card');
            
            sessionCards.forEach(card => {
                const program = card.getAttribute('data-program').toLowerCase();
                const trainer = card.getAttribute('data-trainer').toLowerCase();
                
                const showProgram = !programFilter || program.includes(programFilter);
                const showTrainer = !trainerFilter || trainer.includes(trainerFilter);
                
                if (showProgram && showTrainer) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Скрываем дни без видимых тренировок
            document.querySelectorAll('.day-schedule').forEach(daySchedule => {
                const visibleCards = daySchedule.querySelectorAll('.session-card[style*="block"], .session-card:not([style*="none"])');
                if (visibleCards.length === 0) {
                    daySchedule.style.display = 'none';
                } else {
                    daySchedule.style.display = 'block';
                }
            });
        }
        
        function clearFilters() {
            document.getElementById('programFilter').value = '';
            document.getElementById('trainerFilter').value = '';
            filterSchedule();
        }
        
        // Добавляем обработчики событий для фильтров
        document.getElementById('programFilter').addEventListener('change', filterSchedule);
        document.getElementById('trainerFilter').addEventListener('change', filterSchedule);
        
        // Запись на тренировку
        function bookSession(sessionId) {
            if (!confirm('Вы уверены, что хотите записаться на эту тренировку?')) {
                return;
            }
            
            fetch('ajax/book-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_id: sessionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    FitLab.showNotification(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    FitLab.showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                FitLab.showNotification('Произошла ошибка при записи', 'error');
            });
        }
    </script>

    <style>
        .badge-beginner { background-color: #27ae60; color: white; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; }
        .badge-intermediate { background-color: #f39c12; color: white; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; }
        .badge-advanced { background-color: #e74c3c; color: white; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; }
        
        .session-card {
            border-left: 4px solid #2980B9;
            transition: all 0.3s ease;
        }
        
        .session-card:hover {
            border-left-color: #E74C3C;
            transform: translateY(-2px);
        }
    </style>
</body>
</html>
