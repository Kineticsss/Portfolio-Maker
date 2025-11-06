<?php
session_start();
require 'dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ensure public_token exists
$stmt = $pdo->prepare("SELECT public_token FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$token = $stmt->fetchColumn();

if (!$token) {
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET public_token = :token WHERE id = :id");
    $stmt->execute([':token' => $token, ':id' => $user_id]);
}

// Build the public link dynamically based on current request
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$script_path = rtrim($script_path, '/');
$public_link = "$protocol://$host$script_path/public_resume.php?token=$token";

// Fetch user info
$stmt = $pdo->prepare("SELECT first_name, last_name, email, about_me, phone, address, github, linkedin FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

$errors = [];
$success = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $about_me   = trim($_POST['about_me']);
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);
    $github     = trim($_POST['github']);
    $linkedin   = trim($_POST['linkedin']);

    // Validation
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    } elseif (!preg_match("/^[A-Za-z\s'-]+$/", $first_name)) {
        $errors[] = "First name can only contain letters, spaces, apostrophes, or hyphens.";
    }

    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    } elseif (!preg_match("/^[A-Za-z\s'-]+$/", $last_name)) {
        $errors[] = "Last name can only contain letters, spaces, apostrophes, or hyphens.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($address)) {
        $errors[] = "Address is required.";
    }

    if (!empty($phone) && !preg_match("/^[0-9+\-\s()]+$/", $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    if (!empty($github) && !filter_var($github, FILTER_VALIDATE_URL)) {
        $errors[] = "GitHub link must be a valid URL.";
    }

    if (!empty($linkedin) && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
        $errors[] = "LinkedIn link must be a valid URL.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                about_me = :about_me,
                phone = :phone,
                address = :address,
                github = :github,
                linkedin = :linkedin
            WHERE id = :id
        ");
        
        $updated = $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':about_me' => $about_me,
            ':phone' => $phone,
            ':address' => $address,
            ':github' => $github,
            ':linkedin' => $linkedin,
            ':id' => $user_id
        ]);

        if ($updated) {
            $success = "Profile updated successfully!";
            $user = array_merge($user, $_POST);
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 40px;
        }

        .public-link-section {
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 2px solid #c7d2fe;
        }

        .public-link-section h3 {
            color: #4c1d95;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-display {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .link-display input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #c7d2fe;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #4c1d95;
            font-family: 'Courier New', monospace;
        }

        .link-display input:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
        }

        label.optional::after {
            content: " (optional)";
            font-weight: normal;
            color: #6c757d;
            font-size: 12px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
            line-height: 1.6;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-error ul {
            margin: 10px 0 0 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }

        .copy-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: none;
            animation: slideIn 0.3s ease;
            z-index: 1000;
        }

        .copy-notification.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .content {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .form-actions {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>
<body>
    <div id="copyNotification" class="copy-notification">
        ✓ Link copied to clipboard!
    </div>

    <div class="container">
        <div class="header">
            <h1>Edit Your Profile</h1>
            <p>Update your information and manage your public resume</p>
        </div>

        <div class="content">
            <!-- Public Link Section -->
            <div class="public-link-section">
                <h3>🔗 Your Public Resume Link</h3>
                <div class="link-display">
                    <input type="text" id="publicLink" value="<?= htmlspecialchars($public_link) ?>" readonly>
                    <button type="button" class="btn btn-primary" onclick="copyLink()">📋 Copy</button>
                </div>
                <div class="button-group">
                    <a href="<?= htmlspecialchars($public_link) ?>" target="_blank" class="btn btn-primary">👁️ Preview</a>
                    <form action="regenerate_link.php" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-success" onclick="return confirm('This will create a new link and invalidate the old one. Continue?');">
                            🔄 Regenerate Link
                        </button>
                    </form>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✓ <?= htmlspecialchars($success) ?></strong>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form method="POST">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3>👤 Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($user['first_name']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($user['last_name']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                               placeholder="+1 (555) 123-4567" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                               value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                               required maxlength="200"
                               placeholder="City, State, Country">
                    </div>
                </div>

                <!-- About Me -->
                <div class="form-section">
                    <h3>📝 About Me</h3>
                    
                    <div class="form-group">
                        <label for="about_me" class="optional">Tell us about yourself</label>
                        <textarea id="about_me" name="about_me"
                                  rows="6" maxlength="1000"
                                  placeholder="Write a brief introduction about yourself, your skills, and what you're passionate about..."><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>
                        <small style="color: #6c757d; font-size: 12px;">
                            <?= strlen($user['about_me'] ?? '') ?>/1000 characters
                        </small>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="form-section">
                    <h3>🌐 Social & Professional Links</h3>
                    
                    <div class="form-group">
                        <label for="github" class="optional">GitHub Profile</label>
                        <input type="url" id="github" name="github"
                               value="<?= htmlspecialchars($user['github'] ?? '') ?>"
                               placeholder="https://github.com/username" maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="linkedin" class="optional">LinkedIn Profile</label>
                        <input type="url" id="linkedin" name="linkedin"
                               value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>"
                               placeholder="https://linkedin.com/in/username" maxlength="200">
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary"
                            onclick="window.location.href='portfolio.php'">
                        ← Back to Portfolio
                    </button>
                    <button type="submit" name="save_profile" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copyLink() {
            const linkInput = document.getElementById("publicLink");
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(linkInput.value).then(() => {
                const notification = document.getElementById("copyNotification");
                notification.classList.add("show");
                
                setTimeout(() => {
                    notification.classList.remove("show");
                }, 3000);
            }).catch(err => {
                alert("Failed to copy link. Please copy manually.");
                console.error('Copy failed:', err);
            });
        }

        const aboutMeTextarea = document.getElementById('about_me');
        if (aboutMeTextarea) {
            aboutMeTextarea.addEventListener('input', function() {
                const counter = this.nextElementSibling;
                if (counter) {
                    counter.textContent = `${this.value.length}/1000 characters`;
                }
            });
        }

        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>