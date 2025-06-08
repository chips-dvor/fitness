<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Получаем всех активных тренеров
$query = "SELECT * FROM trainers WHERE is_active = 1 ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Наши тренеры - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Герой секция -->
        <section class="hero" style="padding: 4rem 0;">
            <div class="container">
                <h1>Наши тренеры</h1>
                <p>Команда профессионалов, которые помогут вам достичь ваших фитнес-целей</p>
            </div>
        </section>

        <!-- Тренеры -->
        <section class="section">
            <div class="container">
                <div class="grid grid-2">
                    <?php foreach ($trainers as $trainer): ?>
                        <div class="card trainer-card">
                            <div style="display: flex; gap: 2rem;">
                                <div style="flex-shrink: 0;">
                                    <img src="<?php echo $trainer['photo'] ? htmlspecialchars($trainer['photo']) : '/placeholder.svg?height=150&width=150'; ?>" 
                                         alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                                </div>
                                <div style="flex: 1;">
                                    <h3><?php echo htmlspecialchars($trainer['name']); ?></h3>
                                    <p style="color: #E74C3C; font-weight: 500; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars($trainer['specialization']); ?>
                                    </p>
                                    <p style="margin-bottom: 1rem;">
                                        <strong>Опыт:</strong> <?php echo $trainer['experience_years']; ?> лет
                                    </p>
                                    <p style="margin-bottom: 1.5rem;">
                                        <?php echo htmlspecialchars($trainer['description']); ?>
                                    </p>
                                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                        <?php if ($trainer['email']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($trainer['email']); ?>" class="btn btn-secondary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                                Написать
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($trainer['phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($trainer['phone']); ?>" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                                Позвонить
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;" 
                                                onclick="showTrainerSchedule(<?php echo $trainer['id']; ?>)">
                                            Расписание
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Как работают наши тренеры -->
        <section class="section" style="background-color: #f8f9fa;">
            <div class="container">
                <h2 class="section-title">Как работают наши тренеры</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <h3>Индивидуальный подход</h3>
                        <p>Каждый тренер разрабатывает персональную программу, учитывая ваши цели, уровень подготовки и особенности здоровья.</p>
                    </div>
                    <div class="card">
                        <h3>Постоянное развитие</h3>
                        <p>Наши тренеры регулярно проходят курсы повышения квалификации и изучают новые методики тренировок.</p>
                    </div>
                    <div class="card">
                        <h3>Мотивация и поддержка</h3>
                        <p>Тренеры не только показывают упражнения, но и мотивируют, поддерживают и помогают преодолевать трудности.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Сертификации и достижения -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Сертификации и достижения</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <h3>Профессиональные сертификаты</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Международные сертификаты ACSM, NASM</li>
                            <li>Сертификаты по специализированным программам</li>
                            <li>Медицинские допуски к работе</li>
                            <li>Сертификаты по оказанию первой помощи</li>
                        </ul>
                    </div>
                    <div class="card">
                        <h3>Спортивные достижения</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Мастера спорта и кандидаты в мастера спорта</li>
                            <li>Призеры региональных и всероссийских соревнований</li>
                            <li>Участники международных турниров</li>
                            <li>Опыт подготовки спортсменов высокого уровня</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Персональные тренировки -->
        <section class="section" style="background: linear-gradient(135deg, #2980B9, #E74C3C); color: white;">
            <div class="container" style="text-align: center;">
                <h2 style="color: white;">Персональные тренировки</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">
                    Хотите максимально эффективные тренировки? Закажите персональную тренировку с одним из наших тренеров!
                </p>
                <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                    <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 10px;">
                        <h4 style="color: white;">Разовая тренировка</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: white;">2500 ₽</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 10px;">
                        <h4 style="color: white;">Пакет 5 тренировок</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: white;">11000 ₽</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 10px;">
                        <h4 style="color: white;">Пакет 10 тренировок</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: white;">20000 ₽</p>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="contacts.php" class="btn btn-primary">Записаться на персональную тренировку</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Модальное окно расписания тренера -->
    <div id="trainerScheduleModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Расписание тренера</h3>
            <div id="trainerScheduleContent">
                <!-- Содержимое загружается через AJAX -->
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function showTrainerSchedule(trainerId) {
            const modal = document.getElementById('trainerScheduleModal');
            const modalContent = document.getElementById('trainerScheduleContent');
            
            modalContent.innerHTML = '<p>Загрузка...</p>';
            modal.style.display = 'block';
            
            fetch(`ajax/get-trainer-schedule.php?trainer_id=${trainerId}`)
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
        .trainer-card {
            transition: transform 0.3s ease;
        }
        .trainer-card:hover {
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .trainer-card > div {
                flex-direction: column;
                text-align: center;
            }
            .trainer-card img {
                margin: 0 auto 1rem auto;
            }
        }
    </style>
</body>
</html>
