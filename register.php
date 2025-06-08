<?php
require_once 'includes/auth.php';

$auth = new Auth();
$message = '';
$message_type = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $message = 'Пожалуйста, заполните все обязательные поля';
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Пароли не совпадают';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Пароль должен содержать минимум 6 символов';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Некорректный email адрес';
        $message_type = 'error';
    } else {
        $result = $auth->register($username, $email, $password, $first_name, $last_name, $phone);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - FitLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <section class="section">
            <div class="container">
                <div style="max-width: 500px; margin: 0 auto;">
                    <div class="card">
                        <h2 style="text-align: center; margin-bottom: 2rem;">Регистрация</h2>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Имя пользователя *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="first_name">Имя *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Фамилия *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Телефон</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Пароль *</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Подтвердите пароль *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
                        </form>
                        
                        <p style="text-align: center; margin-top: 1rem;">
                            Уже есть аккаунт? <a href="login.php" style="color: var(--accent-blue);">Войти</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
