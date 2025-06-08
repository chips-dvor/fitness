<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Получаем программы с информацией о тренерах
$query = "SELECT p.*, t.name as trainer_name, t.specialization 
          FROM programs p 
          LEFT JOIN trainers t ON p.trainer_id = t.id 
          WHERE p.is_active = 1 
          ORDER BY p.difficulty_level, p.name";
$stmt = $conn->prepare($query);
$stmt->execute();
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем программы по уровню сложности
$programsByLevel = [
    'beginner' => [],
    'intermediate' => [],
    'advanced' => []
];

foreach ($programs as $program) {
    $programsByLevel[$program['difficulty_level']][] = $program;
}

$levelNames = [
    'beginner' => 'Для начинающих',
    'intermediate' => 'Средний уровень',
    'advanced' => 'Продвинутый уровень'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Программы тренировок - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Герой секция -->
        <section class="hero" style="padding: 4rem 0;">
            <div class="container">
                <h1>Программы тренировок</h1>
                <p>Разнообразные программы для достижения ваших фитнес-целей под руководством профессиональных тренеров</p>
            </div>
        </section>

        <!-- Программы по уровням -->
        <?php foreach ($programsByLevel as $level => $levelPrograms): ?>
            <?php if (!empty($levelPrograms)): ?>
                <section class="section" id="<?php echo $level; ?>">
                    <div class="container">
                        <h2 class="section-title"><?php echo $levelNames[$level]; ?></h2>
                        <div class="grid grid-2">
                            <?php foreach ($levelPrograms as $program): ?>
                                <div class="card">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                        <h3><?php echo htmlspecialchars($program['name']); ?></h3>
                                        <span class="badge badge-<?php echo $program['difficulty_level']; ?>" style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; font-weight: 500;">
                                            <?php echo $levelNames[$program['difficulty_level']]; ?>
                                        </span>
                                    </div>
                                    
                                    <p style="margin-bottom: 1rem;"><?php echo htmlspecialchars($program['description']); ?></p>
                                    
                                    <div class="program-details" style="margin-bottom: 1rem;">
                                        <div style="display: flex; gap: 2rem; margin-bottom: 0.5rem;">
                                            <span><strong>Длительность:</strong> <?php echo $program['duration_minutes']; ?> мин</span>
                                            <span><strong>Макс. участников:</strong> <?php echo $program['max_participants']; ?></span>
                                        </div>
                                        <?php if ($program['trainer_name']): ?>
                                            <div>
                                                <strong>Тренер:</strong> <?php echo htmlspecialchars($program['trainer_name']); ?>
                                                <?php if ($program['specialization']): ?>
                                                    <br><small style="color: #666;"><?php echo htmlspecialchars($program['specialization']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="display: flex; gap: 1rem;">
                                        <a href="schedule.php?program=<?php echo $program['id']; ?>" class="btn btn-primary">Расписание</a>
                                        <?php if ($user): ?>
                                            <button class="btn btn-secondary" onclick="showProgramModal(<?php echo $program['id']; ?>)">Записаться</button>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-secondary">Войти для записи</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Дополнительная информация -->
        <section class="section" style="background-color: #f8f9fa;">
            <div class="container">
                <h2 class="section-title">Как выбрать программу</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <h3>Для начинающих</h3>
                        <p>Если вы новичок в фитнесе или возвращаетесь после длительного перерыва, начните с программ для начинающих. Они помогут освоить правильную технику и постепенно увеличить нагрузку.</p>
                    </div>
                    <div class="card">
                        <h3>Средний уровень</h3>
                        <p>Для тех, кто уже имеет базовую физическую подготовку и хочет повысить интенсивность тренировок. Программы включают более сложные упражнения и комбинации.</p>
                    </div>
                    <div class="card">
                        <h3>Продвинутый уровень</h3>
                        <p>Высокоинтенсивные программы для опытных спортсменов. Требуют хорошей физической подготовки и знания техники выполнения упражнений.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Специальные предложения -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Специальные предложения</h2>
                <div class="grid grid-2">
                    <div class="card" style="background: linear-gradient(135deg, #E74C3C, #C0392B); color: white;">
                        <h3 style="color: white;">Первая тренировка бесплатно!</h3>
                        <p>Новые клиенты могут посетить любую групповую тренировку абсолютно бесплатно. Это отличная возможность познакомиться с нашими программами и тренерами.</p>
                        <?php if (!$user): ?>
                            <a href="register.php" class="btn btn-secondary">Зарегистрироваться</a>
                        <?php endif; ?>
                    </div>
                    <div class="card" style="background: linear-gradient(135deg, #2980B9, #1F5F8B); color: white;">
                        <h3 style="color: white;">Персональные тренировки</h3>
                        <p>Индивидуальные занятия с тренером для максимально эффективного достижения ваших целей. Программа составляется персонально для вас.</p>
                        <a href="contacts.php" class="btn btn-primary">Узнать подробнее</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Модальное окно записи -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Запись на тренировку</h3>
            <div id="modalContent">
                <!-- Содержимое загружается через AJAX -->
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function showProgramModal(programId) {
            const modal = document.getElementById('bookingModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.innerHTML = '<p>Загрузка...</p>';
            modal.style.display = 'block';
            
            fetch(`ajax/get-program-schedule.php?program_id=${programId}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    modalContent.innerHTML = '<p>Ошибка загрузки данных</p>';
                });
        }
    </script>

    <style>
        .badge-beginner { background-color: #27ae60; color: white; }
        .badge-intermediate { background-color: #f39c12; color: white; }
        .badge-advanced { background-color: #e74c3c; color: white; }
    </style>
</body>
</html>
