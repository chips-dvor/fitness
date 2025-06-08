<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Получаем все активные абонементы
$query = "SELECT * FROM memberships WHERE is_active = 1 ORDER BY price";
$stmt = $conn->prepare($query);
$stmt->execute();
$memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Абонементы - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Герой секция -->
        <section class="hero" style="padding: 4rem 0;">
            <div class="container">
                <h1>Абонементы FitLab</h1>
                <p>Выберите подходящий абонемент и начните тренироваться уже сегодня</p>
            </div>
        </section>

        <!-- Абонементы -->
        <section class="section">
            <div class="container">
                <div class="grid grid-2">
                    <?php foreach ($memberships as $index => $membership): ?>
                        <div class="card membership-card <?php echo $index === 1 ? 'popular' : ''; ?>">
                            <?php if ($index === 1): ?>
                                <div class="popular-badge">Популярный</div>
                            <?php endif; ?>
                            
                            <div style="text-align: center; margin-bottom: 2rem;">
                                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($membership['name']); ?>
                                </h3>
                                <div style="font-size: 3rem; font-weight: bold; color: #E74C3C; margin-bottom: 0.5rem;">
                                    <?php echo number_format($membership['price'], 0, ',', ' '); ?> ₽
                                </div>
                                <div style="color: #666;">
                                    <?php echo $membership['duration_days']; ?> 
                                    <?php 
                                    if ($membership['duration_days'] == 30) echo 'дней';
                                    elseif ($membership['duration_days'] == 365) echo 'дней (1 год)';
                                    else echo 'дней';
                                    ?>
                                </div>
                            </div>
                            
                            <p style="text-align: center; margin-bottom: 2rem; color: #666;">
                                <?php echo htmlspecialchars($membership['description']); ?>
                            </p>
                            
                            <div style="margin-bottom: 2rem;">
                                <h4 style="margin-bottom: 1rem;">Что включено:</h4>
                                <ul style="list-style: none; padding: 0;">
                                    <?php 
                                    $features = explode('|', $membership['features']);
                                    foreach ($features as $feature): 
                                    ?>
                                        <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee; display: flex; align-items: center;">
                                            <span style="color: #27ae60; margin-right: 0.5rem;">✓</span>
                                            <?php echo htmlspecialchars($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div style="text-align: center;">
                                <?php if ($user): ?>
                                    <button class="btn btn-primary" style="width: 100%;" 
                                            onclick="purchaseMembership(<?php echo $membership['id']; ?>)">
                                        Купить абонемент
                                    </button>
                                <?php else: ?>
                                    <a href="register.php" class="btn btn-primary" style="width: 100%;">
                                        Зарегистрироваться для покупки
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($membership['duration_days'] == 30): ?>
                                <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #666;">
                                    <?php echo round($membership['price'] / 30, 0); ?> ₽ в день
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Сравнение абонементов -->
        <section class="section" style="background-color: #f8f9fa;">
            <div class="container">
                <h2 class="section-title">Сравнение абонементов</h2>
                <div style="overflow-x: auto;">
                    <table class="table" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th>Услуга</th>
                                <?php foreach ($memberships as $membership): ?>
                                    <th style="text-align: center;"><?php echo htmlspecialchars($membership['name']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Тренажерный зал</strong></td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                            </tr>
                            <tr>
                                <td><strong>Групповые занятия</strong></td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #f39c12;">4 в месяц</td>
                                <td style="text-align: center; color: #27ae60;">Безлимит</td>
                                <td style="text-align: center; color: #27ae60;">Безлимит</td>
                            </tr>
                            <tr>
                                <td><strong>Персональные тренировки</strong></td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #f39c12;">1 в месяц</td>
                                <td style="text-align: center; color: #27ae60;">Безлимит</td>
                            </tr>
                            <tr>
                                <td><strong>Сауна</strong></td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                            </tr>
                            <tr>
                                <td><strong>Массаж</strong></td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #f39c12;">1 в месяц</td>
                                <td style="text-align: center; color: #27ae60;">Безлимит</td>
                            </tr>
                            <tr>
                                <td><strong>Гостевые визиты</strong></td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #e74c3c;">✗</td>
                                <td style="text-align: center; color: #27ae60;">✓</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Акции и скидки -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Акции и скидки</h2>
                <div class="grid grid-3">
                    <div class="card" style="background: linear-gradient(135deg, #E74C3C, #C0392B); color: white;">
                        <h3 style="color: white;">Первый месяц -50%</h3>
                        <p>Для новых клиентов действует скидка 50% на первый месяц любого абонемента.</p>
                        <small style="color: rgba(255,255,255,0.8);">*Акция действует до конца месяца</small>
                    </div>
                    <div class="card" style="background: linear-gradient(135deg, #2980B9, #1F5F8B); color: white;">
                        <h3 style="color: white;">Приведи друга</h3>
                        <p>Приведите друга и получите скидку 20% на следующий месяц абонемента.</p>
                        <small style="color: rgba(255,255,255,0.8);">*Друг должен купить абонемент</small>
                    </div>
                    <div class="card" style="background: linear-gradient(135deg, #27AE60, #229954); color: white;">
                        <h3 style="color: white;">Студенческая скидка</h3>
                        <p>Студенты получают постоянную скидку 15% при предъявлении студенческого билета.</p>
                        <small style="color: rgba(255,255,255,0.8);">*Только очная форма обучения</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Условия и правила -->
        <section class="section" style="background-color: #f8f9fa;">
            <div class="container">
                <h2 class="section-title">Условия и правила</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <h3>Условия покупки</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Абонемент активируется с момента первого посещения</li>
                            <li>Возможна оплата наличными или картой</li>
                            <li>Рассрочка доступна для абонементов от 3 месяцев</li>
                            <li>Возврат возможен в течение 14 дней при наличии справки</li>
                        </ul>
                    </div>
                    <div class="card">
                        <h3>Правила посещения</h3>
                        <ul style="list-style-type: disc; margin-left: 2rem;">
                            <li>Обязательно иметь при себе полотенце</li>
                            <li>Спортивная обувь и одежда обязательны</li>
                            <li>Абонемент не передается третьим лицам</li>
                            <li>При утере абонемента взимается плата за восстановление</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Призыв к действию -->
        <section class="section" style="background: linear-gradient(135deg, #2980B9, #E74C3C); color: white;">
            <div class="container" style="text-align: center;">
                <h2 style="color: white;">Готовы начать тренировки?</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">
                    Выберите подходящий абонемент и получите доступ ко всем возможностям FitLab!
                </p>
                <?php if (!$user): ?>
                    <a href="register.php" class="btn btn-primary" style="margin-right: 1rem;">Зарегистрироваться</a>
                    <a href="contacts.php" class="btn btn-secondary">Задать вопрос</a>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="window.scrollTo(0, 0)">Выбрать абонемент</button>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        function purchaseMembership(membershipId) {
            if (!confirm('Вы уверены, что хотите купить этот абонемент?')) {
                return;
            }
            
            // В реальном приложении здесь была бы интеграция с платежной системой
            FitLab.showNotification('Функция покупки будет доступна в ближайшее время. Обратитесь к администратору.', 'info');
        }
    </script>

    <style>
        .membership-card {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .membership-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .membership-card.popular {
            border: 3px solid #E74C3C;
            transform: scale(1.05);
        }
        
        .popular-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #E74C3C;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .membership-card.popular:hover {
            transform: scale(1.05) translateY(-10px);
        }
    </style>
</body>
</html>
