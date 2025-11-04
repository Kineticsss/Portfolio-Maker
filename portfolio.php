<?php
session_start();
require __DIR__ . '/dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$user = null;
$education  = [];
$experience = [];
$projects   = [];
$skills     = [];

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            first_name,
            last_name,
            email,
            profile_pic,
            about_me,
            phone,
            address,
            github,
            linkedin
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, degree, school, start_date, end_date, description
                           FROM education
                           WHERE user_id = :id
                           ORDER BY start_date DESC NULLS LAST");
    $stmt->execute([':id' => $user_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, title, company, start_date, end_date, description
                           FROM experience
                           WHERE user_id = :id
                           ORDER BY start_date DESC NULLS LAST");
    $stmt->execute([':id' => $user_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    $stmt = $pdo->prepare("SELECT id, title, description, start_date, end_date, link
                           FROM projects
                           WHERE user_id = :id
                           ORDER BY start_date DESC NULLS LAST");
    $stmt->execute([':id' => $user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $stmt = $pdo->prepare("SELECT id, skill_name, proficiency FROM skills WHERE user_id = :id ORDER BY skill_name ASC");
    $stmt->execute([':id' => $user_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error. Please check logs.");
}

$avatar_src = '';
if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
    $avatar_src = $user['profile_pic'];
} else {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300">
      <rect fill="#e9e9e9" width="100%" height="100%"/>
      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666" font-family="Georgia, serif" font-size="20">No Image</text>
    </svg>';
    $avatar_src = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function esc($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo esc($user['first_name'] . ' ' . $user['last_name']); ?> - Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { display:flex; max-width:1000px;margin:40px auto; }
        .sidebar { width:260px;padding:20px;border-right:1px solid #000;background:#fafafa; }
        .main { flex:1;padding:20px; }
        .avatar { width:100%; border-radius:4px; margin-bottom:12px; }
        .logout { display:block;margin-top:12px; color:#000; text-decoration:underline; }
        h2 { font-size:18px; border-bottom:1px solid #000; margin-top:24px; margin-bottom:10px; text-transform:uppercase; }
        .item { margin-bottom:14px; }
        .item-title{ font-weight:bold; }
        .item-subtitle{ font-style:italic; }
        .dates{ float:right; color:#333; font-size:13px; }
        .clear{ clear:both; }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="profile-photo-section" style="text-align:center; margin-bottom:20px;">
            <img class="avatar" src="<?php echo esc($avatar_src); ?>" alt="Profile picture" style="width:180px; height:180px; border-radius:50%; object-fit:cover; border:2px solid #ccc;">

            <form action="profile_upload.php" method="post" enctype="multipart/form-data" style="margin-top:12px; display:flex; flex-direction:column; align-items:center; gap:6px;">
                <input type="file" name="profile_pic" accept="image/*" style="width:180px;">
                
                <div style="display:flex; justify-content:center; gap:8px;">
                    <button type="submit" name="action" value="upload" style="padding:5px 10px;">Upload</button>
                    <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
                        <button type="submit" name="action" value="remove" style="padding:5px 10px; background:#c33; color:white;">Remove</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    <h2 style="font-size:20px; margin:0; display:flex; align-items:center; gap:10px;">
        <?= esc($user['first_name'] . ' ' . $user['last_name']); ?>
        <button onclick="window.location.href='edit_profile.php'" class="edit-btn-inline">Edit Profile</button>
    </h2>
    <p style="margin-top:6px;"><?= esc($user['email']); ?></p>

    <?php if (!empty($user['phone'])): ?>
        <p><strong>Phone:</strong> <?= esc($user['phone']); ?></p>
    <?php endif; ?>

    <?php if (!empty($user['address'])): ?>
        <p><strong>Address:</strong> <?= esc($user['address']); ?></p>
    <?php endif; ?>

    <?php if (!empty($user['github'])): ?>
        <p><strong>GitHub:</strong> <a href="<?= esc($user['github']); ?>" target="_blank">View</a></p>
    <?php endif; ?>

    <?php if (!empty($user['linkedin'])): ?>
        <p><strong>LinkedIn:</strong> <a href="<?= esc($user['linkedin']); ?>" target="_blank">View</a></p>
    <?php endif; ?>

        <?php if (!empty($user['about_me'])): ?>
            <h2>About Me</h2>
            <p><?= nl2br(htmlspecialchars($user['about_me'])) ?></p>
        <?php endif; ?>

        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="main">
        <h2>Education <a style="font-size:12px;margin-left:10px;" href="education/add.php">[+ Add]</a></h2>
        <?php if (count($education) === 0): ?>
            <p>No education entries yet.</p>
        <?php else: ?>
            <?php foreach ($education as $edu): ?>
            <div class="item">
                <span class="item-title"><?php echo esc($edu['degree']); ?></span>,
                <?php echo esc($edu['school']); ?>
                <span class="dates"><?php echo esc($edu['start_date'] . ( $edu['end_date'] ? " – " . $edu['end_date'] : '' )); ?></span>
                <div class="clear"></div>
                <?php if (!empty($edu['description'])): ?>
                    <div class="item-subtitle"><?php echo esc($edu['description']); ?></div>
                <?php endif; ?>
                <a href="education/edit.php?id=<?php echo $edu['id']; ?>">Edit</a> | 
                <a href="education/delete.php?id=<?php echo $edu['id']; ?>" onclick="return confirm('Delete this education?');">Delete</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Experience <a style="font-size:12px;margin-left:10px;" href="experience/add.php">[+ Add]</a></h2>
        <?php if (count($experience) === 0): ?>
            <p>No experience entries yet.</p>
        <?php else: ?>
            <?php foreach ($experience as $exp): ?>
                <div class="item">
                    <span class="item-title"><?php echo esc($exp['title']); ?></span>,
                    <?php echo esc($exp['company']); ?>
                    <span class="dates"><?php echo esc($exp['start_date'] . ( $exp['end_date'] ? " – " . $exp['end_date'] : '' )); ?></span>
                    <div class="clear"></div>
                    <?php if (!empty($exp['description'])): ?>
                        <div class="item-subtitle"><?php echo esc($exp['description']); ?></div>
                    <?php endif; ?>
                    <a href="experience/edit.php?id=<?php echo $exp['id']; ?>">Edit</a> | 
                    <a href="experience/delete.php?id=<?php echo $exp['id']; ?>" onclick="return confirm('Delete this experience?');">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Projects <a style="font-size:12px;margin-left:10px;" href="project/add.php">[+ Add]</a></h2>
        <?php if (count($projects) === 0): ?>
            <p>No projects yet.</p>
        <?php else: ?>
            <?php foreach ($projects as $proj): ?>
                <div class="item">
                    <span class="item-title"><?php echo esc($proj['title']); ?></span>
                    <span class="dates"><?php echo esc($proj['start_date'] . ( $proj['end_date'] ? " – " . $proj['end_date'] : '' )); ?></span>
                    <div class="clear"></div>
                    <?php if (!empty($proj['description'])): ?>
                        <div><?php echo esc($proj['description']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($proj['link'])): ?>
                        <div><a href="<?php echo esc($proj['link']); ?>" target="_blank">View Project</a></div>
                    <?php endif; ?>
                    <a href="project/edit.php?id=<?php echo $proj['id']; ?>">Edit</a> | 
                    <a href="project/delete.php?id=<?php echo $proj['id']; ?>" onclick="return confirm('Delete this project?');">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <h2>Technical Skills
    <a style="font-size:12px; margin-left:10px;" href="skills/add.php">[+ Add]</a>
    </h2>

    <?php if (count($skills) === 0): ?>
        <p>No technical skills added yet.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($skills as $skill): ?>
            <li>
                <span class="item-title"><?php echo esc($skill['skill_name']); ?></span>
                <?php if (!empty($skill['proficiency'])): ?>
                    – <span class="item-subtitle"><?php echo esc($skill['proficiency']); ?></span>
                <?php endif; ?>
                <a href="skills/edit.php?id=<?php echo $skill['id']; ?>">Edit</a> |
                <a href="skills/delete.php?id=<?php echo $skill['id']; ?>" onclick="return confirm('Delete this skill?');">Delete</a>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    </div>

    <a href="public_resume.php?id=<?= $_SESSION['user_id'] ?>" target="_blank">View Public Resume</a>

</div>
</body>
</html>
