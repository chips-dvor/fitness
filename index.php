<?php
require_once 'includes/auth.php';
$auth = new Auth();
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLab - Фитнес-клуб премиум класса</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Герой секция -->
        <section class="hero">
            <div class="container">
                <h1>Добро пожаловать в FitLab</h1>
                <p>Премиальный фитнес-клуб, где каждая тренировка - это шаг к лучшей версии себя. 
                   Современное оборудование, профессиональные тренеры и индивидуальный подход к каждому клиенту.</p>
                <?php if (!$user): ?>
                    <a href="register.php" class="btn btn-primary">Начать тренировки</a>
                    <a href="about.php" class="btn btn-secondary">Узнать больше</a>
                <?php else: ?>
                    <a href="schedule.php" class="btn btn-primary">Записаться на тренировку</a>
                    <a href="profile.php" class="btn btn-secondary">Мой профиль</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Преимущества -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Почему выбирают FitLab</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <h3>Профессиональные тренеры</h3>
                        <p>Наши сертифицированные тренеры имеют многолетний опыт и помогут достичь ваших целей безопасно и эффективно.</p>
                    </div>
                    <div class="card">
                        <h3>Современное оборудование</h3>
                        <p>Новейшие тренажеры от ведущих мировых производителей обеспечивают комфортные и результативные тренировки.</p>
                    </div>
                    <div class="card">
                        <h3>Разнообразие программ</h3>
                        <p>Йога, пилатес, CrossFit, силовые тренировки, кардио - найдите программу, которая подходит именно вам.</p>
                    </div>
                    <div class="card">
                        <h3>Гибкое расписание</h3>
                        <p>Тренировки с раннего утра до позднего вечера. Выберите удобное время для занятий.</p>
                    </div>
                    <div class="card">
                        <h3>Индивидуальный подход</h3>
                        <p>Персональные программы тренировок, учитывающие ваш уровень подготовки и цели.</p>
                    </div>
                    <div class="card">
                        <h3>Комфортная атмосфера</h3>
                        <p>Просторные залы, раздевалки с душевыми, зона отдыха - все для вашего комфорта.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Статистика -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">FitLab в цифрах</h2>
                <div class="grid grid-4">
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--success-green), #45A049);">
                        <div class="stats-number">500+</div>
                        <p class="stats-label">Довольных клиентов</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--accent-blue), var(--hover-blue));">
                        <div class="stats-number">15</div>
                        <p class="stats-label">Профессиональных тренеров</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, var(--energy-orange), var(--hover-orange));">
                        <div class="stats-number">20+</div>
                        <p class="stats-label">Видов тренировок</p>
                    </div>
                    <div class="card stats-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                        <div class="stats-number">5</div>
                        <p class="stats-label">Лет опыта</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Призыв к действию -->
        <section class="section" style="background: linear-gradient(135deg, var(--bg-dark), var(--accent-blue)); color: var(--text-white);">
            <div class="container" style="text-align: center;">
                <h2 style="color: white;">Готовы начать свой путь к здоровью?</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">
                    Присоединяйтесь к нашему сообществу и получите первую тренировку бесплатно!
                </p>
                <?php if (!$user): ?>
                    <a href="register.php" class="btn btn-primary" style="margin-right: 1rem;">Зарегистрироваться</a>
                    <a href="contacts.php" class="btn btn-secondary">Связаться с нами</a>
                <?php else: ?>
                    <a href="schedule.php" class="btn btn-primary">Записаться на тренировку</a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
