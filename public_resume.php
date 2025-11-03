<?php
session_start();
require 'dbconfig.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
    die("Invalid user.");
}

// Fetch user info
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, address, github, linkedin, about_me, profile_pic FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fetch other data
$tables = ['education', 'experience', 'projects', 'skills'];
$data = [];

foreach ($tables as $table) {
    $orderColumn = match ($table) {
        'skills' => 'skill_name',
        'education', 'experience', 'projects' => 'start_date',
        default => 'id'
    };

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = :id ORDER BY $orderColumn DESC NULLS LAST");
    $stmt->execute([':id' => $user_id]);
    $data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Avatar handling
$avatar_src = !empty($user['profile_pic']) && file_exists($user['profile_pic'])
    ? $user['profile_pic']
    : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect fill="#e9e9e9" width="100%" height="100%"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666" font-family="Georgia, serif" font-size="20">No Image</text></svg>');

function esc($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= esc($user['first_name'] . ' ' . $user['last_name']) ?> – Resume</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9f9f9; color: #333; }
    .resume-container { max-width: 900px; margin: 40px auto; background: #fff; padding: 40px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    header { text-align: center; margin-bottom: 30px; }
    header img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
    h1 { margin: 0; font-size: 28px; }
    .contact { font-size: 14px; color: #555; margin-top: 10px; }
    .contact a { color: #0073b1; text-decoration: none; }
    section { margin-bottom: 25px; }
    h2 { font-size: 18px; border-bottom: 2px solid #333; padding-bottom: 4px; margin-bottom: 12px; text-transform: uppercase; }
    .item { margin-bottom: 12px; }
    .item-title { font-weight: bold; font-size: 16px; }
    .item-sub { font-style: italic; color: #555; }
    .desc { margin-top: 4px; }
    button.print-btn { display: block; margin: 0 auto 20px; padding: 10px 20px; border: none; background: #333; color: #fff; cursor: pointer; border-radius: 4px; }
    button.print-btn:hover { background: #555; }
    @media print {
        button.print-btn { display: none; }
        body { background: #fff; }
        .resume-container { box-shadow: none; margin: 0; }
    }
</style>
</head>
<body>

<div class="resume-container">
    <button class="print-btn" onclick="window.print()">Download / Print PDF</button>

    <header>
        <img src="<?= esc($avatar_src) ?>" alt="Profile Picture">
        <h1><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        <div class="contact">
            <?= esc($user['email']) ?><br>
            <?= esc($user['phone']) ?><br>
            <?= esc($user['address']) ?><br>
            <?php if (!empty($user['github'])): ?>
                <a href="<?= esc($user['github']) ?>" target="_blank">GitHub</a> |
            <?php endif; ?>
            <?php if (!empty($user['linkedin'])): ?>
                <a href="<?= esc($user['linkedin']) ?>" target="_blank">LinkedIn</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($user['about_me'])): ?>
        <section>
            <h2>About Me</h2>
            <p><?= nl2br(esc($user['about_me'])) ?></p>
        </section>
    <?php endif; ?>

    <section>
        <h2>Education</h2>
        <?php if (empty($data['education'])): ?>
            <p>No education info.</p>
        <?php else: ?>
            <?php foreach ($data['education'] as $edu): ?>
                <div class="item">
                    <div class="item-title"><?= esc($edu['degree']) ?> – <?= esc($edu['school']) ?></div>
                    <div class="item-sub"><?= esc($edu['start_date']) ?> – <?= esc($edu['end_date']) ?></div>
                    <div class="desc"><?= nl2br(esc($edu['description'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <h2>Experience</h2>
        <?php if (empty($data['experience'])): ?>
            <p>No experience info.</p>
        <?php else: ?>
            <?php foreach ($data['experience'] as $exp): ?>
                <div class="item">
                    <div class="item-title"><?= esc($exp['title']) ?> – <?= esc($exp['company']) ?></div>
                    <div class="item-sub"><?= esc($exp['start_date']) ?> – <?= esc($exp['end_date']) ?></div>
                    <div class="desc"><?= nl2br(esc($exp['description'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <h2>Projects</h2>
        <?php if (empty($data['projects'])): ?>
            <p>No projects info.</p>
        <?php else: ?>
            <?php foreach ($data['projects'] as $proj): ?>
                <div class="item">
                    <div class="item-title"><?= esc($proj['title']) ?></div>
                    <div class="item-sub"><?= esc($proj['start_date']) ?> – <?= esc($proj['end_date']) ?></div>
                    <div class="desc"><?= nl2br(esc($proj['description'])) ?></div>
                    <?php if (!empty($proj['link'])): ?>
                        <div><a href="<?= esc($proj['link']) ?>" target="_blank">View Project</a></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <h2>Technical Skills</h2>
        <?php if (empty($data['skills'])): ?>
            <p>No skills info.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($data['skills'] as $skill): ?>
                <li><?= esc($skill['skill_name']) ?><?= $skill['proficiency'] ? ' – ' . esc($skill['proficiency']) : '' ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

</div>
</body>
</html>
