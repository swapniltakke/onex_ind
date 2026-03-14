<?php
SharedManager::checkAuthToModule(12);
// Function to validate session
function validateSession($username, $session_id) {
    $current_date = date('Y-m-d');
    $sql = "SELECT * FROM tbl_user_sessions 
            WHERE username = :username 
            AND session_id = :session_id 
            AND is_active = 1 
            AND DATE(session_expiry) >= :current_date";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [
        ":username" => $username,
        ":session_id" => $session_id,
        ":current_date" => $current_date
    ])["data"];

    return !empty($result);
}

// Check if user is already logged in with a valid session
if (isset($_SESSION['username']) && isset($_SESSION['session_id'])) {
    if (validateSession($_SESSION['username'], $_SESSION['session_id'])) {
        header('Location: /dpm/dwc/material_search.php');
        exit();
    } else {
        session_unset();
        session_destroy();
        header('Location: /dpm/dwc/index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Digital Work Center Login</title>
    <link href="/shared/inspia_gh_assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(180deg, #006F75 0%, #00474B 100%);
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header img {
            width: 180px;
            margin-bottom: 20px;
        }

        .login-header h2 {
            color: #006F75;
            margin: 0;
            font-size: 24px;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }

        .login-option-btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .auto-login-btn {
            background: #ff6b00;
            color: white;
        }

        .auto-login-btn:hover {
            background: #e65100;
        }

        .manual-login-btn {
            background: #2196F3;
            color: white;
        }

        .manual-login-btn:hover {
            background: #1976D2;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .form-group i {
            color: #006F75;
            margin-right: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #006F75;
            box-shadow: 0 0 5px rgba(0, 111, 117, 0.2);
        }

        .login-button {
            background: #006F75;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-button:hover {
            background: #005458;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        #manual-login-form {
            display: none;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: transparent;
            border: none;
            color: #006F75;
            cursor: pointer;
            display: none;
            padding: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            border-radius: 5px;
        }

        .back-button:hover {
            background: rgba(0, 111, 117, 0.1);
        }

        .back-button i {
            margin-right: 5px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(10px); }
        }

        .fade-in { animation: fadeIn 0.3s ease-out; }
        .slide-in { animation: slideIn 0.3s ease-out; }
        .fade-out { animation: fadeOut 0.3s ease-out; }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: #fff;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .form-group input.error {
            border-color: #dc3545;
            background-color: #fff;
        }

        .form-group input.error:focus {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .no-underline {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <button class="back-button" onclick="showLoginOptions()">
            <i class="fa fa-arrow-left"></i> Back
        </button>

        <div class="login-header">
            <a href="/index.php" class="no-underline">
                <img src="/images/onex.png" alt="OneX Logo">
                <h2>Digital Work Center Login</h2>
            </a>
        </div>

        <div class="login-options">
            <button class="login-option-btn auto-login-btn" onclick="handleAutoLogin()">
                <i class="fa fa-check-circle"></i>
                Login with check my id
            </button>
            <button class="login-option-btn manual-login-btn" onclick="showManualLogin()">
                <i class="fa fa-user"></i>
                Manual Login
            </button>
        </div>

        <form id="manual-login-form" method="POST">
            <input type="hidden" id="role_id" name="role_id" value="Panel">
            <div class="form-group">
                <label for="username"><i class="fa fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-button">
                <i class="fa fa-sign-in"></i> Login
            </button>
        </form>
        
        <div class="footer-text">
            © 2025 OneX - Digital Work Center
        </div>
    </div>

    <script>
        const loginOptions = document.querySelector('.login-options');
        const manualLoginForm = document.getElementById('manual-login-form');
        const backButton = document.querySelector('.back-button');

        function showManualLogin() {
            loginOptions.classList.add('fade-out');
            setTimeout(() => {
                loginOptions.style.display = 'none';
                loginOptions.classList.remove('fade-out');
                manualLoginForm.style.display = 'block';
                manualLoginForm.classList.add('fade-in');
                backButton.style.display = 'block';
                backButton.classList.add('slide-in');
            }, 300);
        }

        function showLoginOptions() {
            manualLoginForm.classList.remove('fade-in');
            manualLoginForm.classList.add('fade-out');
            backButton.classList.remove('slide-in');
            backButton.classList.add('fade-out');
            
            setTimeout(() => {
                manualLoginForm.style.display = 'none';
                backButton.style.display = 'none';
                loginOptions.style.display = 'flex';
                loginOptions.classList.add('fade-in');
                document.getElementById('manual-login-form').reset();
                manualLoginForm.classList.remove('fade-out');
                backButton.classList.remove('fade-out');
            }, 300);
        }

        function handleAutoLogin() {
            const autoLoginBtn = document.querySelector('.auto-login-btn');
            const originalText = autoLoginBtn.innerHTML;
            autoLoginBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking...';
            autoLoginBtn.disabled = true;

            // Create FormData object
            const formData = new FormData();
            formData.append('action', 'auto_login');


            fetch('loginAuth.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Login successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/dashboard';
                    }, 1000);
                } else {
                    autoLoginBtn.innerHTML = originalText;
                    autoLoginBtn.disabled = false;
                    showNotification('error', data.message || 'Session expired. Please use manual login.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                autoLoginBtn.innerHTML = originalText;
                autoLoginBtn.disabled = false;
                showNotification('error', 'An error occurred. Please try again.');
            });
        }

        manualLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const role_id = document.getElementById('role_id').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const loginButton = document.querySelector('.login-button');

            resetErrors();

            if (!validateForm(username, password)) {
                return false;
            }

            loginButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Logging in...';
            loginButton.disabled = true;

            const formData = new FormData();
            formData.append('role_id', role_id);
            formData.append('username', username);
            formData.append('password', password);
            formData.append('action', 'manual_login');

            fetch('loginAuth.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Login successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/dashboard';
                    }, 1000);
                } else {
                    showNotification('error', data.message || 'Login failed. Please try again.');
                    loginButton.innerHTML = '<i class="fa fa-sign-in"></i> Login';
                    loginButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'An error occurred. Please try again.');
                loginButton.innerHTML = '<i class="fa fa-sign-in"></i> Login';
                loginButton.disabled = false;
            });
        });

        function validateForm(username, password) {
            let isValid = true;

            if (!username) {
                showError('username', 'Username is required');
                isValid = false;
            } else if (username.length < 3) {
                showError('username', 'Username must be at least 3 characters');
                isValid = false;
            }

            if (!password) {
                showError('password', 'Password is required');
                isValid = false;
            } else if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters');
                isValid = false;
            }

            return isValid;
        }

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = message;
            
            field.classList.add('error');
            
            const existingError = field.parentElement.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            field.parentElement.appendChild(errorDiv);
        }

        function resetErrors() {
            const errorMessages = document.querySelectorAll('.error-message');
            const errorFields = document.querySelectorAll('.error');
            
            errorMessages.forEach(msg => msg.remove());
            errorFields.forEach(field => {
                field.classList.remove('error');
                field.style.borderColor = '#ddd';
            });
        }

        function showNotification(type, message) {
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = message;
            notification.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.5s ease-out';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>