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

/**
 * Helper to safely query database
 */
function safeQuery($pdo, $sql, $params) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

try {
    // Fetch user info
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
            linkedin,
            public_token
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

    // GENERATE PUBLIC TOKEN IF IT DOESN'T EXIST
    if (empty($user['public_token'])) {
        $new_token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET public_token = :token WHERE id = :id");
        $stmt->execute([':token' => $new_token, ':id' => $user_id]);
        $user['public_token'] = $new_token; // Update in memory
    }

    // Fetch education (with possible new columns)
    $education = safeQuery($pdo, "
        SELECT id, degree, field_of_study, school_name, start_date, end_date, description
        FROM education
        WHERE user_id = :id
        ORDER BY start_date DESC NULLS LAST
    ", [':id' => $user_id]);


    // Fetch experience (supporting potential new columns)
    $experience = safeQuery($pdo, "
        SELECT id, job_title, company_name, location, start_date, end_date, description
        FROM experience
        WHERE user_id = :id
        ORDER BY start_date DESC NULLS LAST
    ", [':id' => $user_id]);

    // Fetch projects (with image)
    $projects = safeQuery($pdo, "
        SELECT id, project_name, description, technologies, project_link, image_path
        FROM projects
        WHERE user_id = :id
        ORDER BY id DESC
    ", [':id' => $user_id]);



    // Fetch skills
    $skills = safeQuery($pdo, "
        SELECT id, skill_name, proficiency, category
        FROM skills
        WHERE user_id = :id
        ORDER BY skill_name ASC
    ", [':id' => $user_id]);

} catch (PDOException $e) {
    die("Database error. Please check logs. " . htmlspecialchars($e->getMessage()));
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
    <title><?= esc($user['first_name'] . ' ' . $user['last_name']); ?> - Portfolio</title>
    <link rel="stylesheet" href="form_style.css">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f5f6f7;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            background: #fafafa;
            padding: 25px;
            border-right: 1px solid #ddd;
        }

        .main {
            flex: 1;
            padding: 30px 40px;
            background: #fff;
        }

        .avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            margin-bottom: 15px;
        }

        h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 6px;
            color: #333;
        }

        .section {
            margin-bottom: 35px;
        }

        .item {
            margin-bottom: 16px;
            padding: 12px;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .item-title {
            font-weight: 600;
            font-size: 16px;
        }

        .item-subtitle {
            font-style: italic;
            color: #555;
        }

        .dates {
            float: right;
            font-size: 13px;
            color: #777;
        }

        .clear {
            clear: both;
        }

        /* Sidebar details */
        .sidebar strong {
            display: inline-block;
            width: 70px;
            color: #444;
        }

        .about {
            margin-top: 20px;
        }

        .logout {
            display: inline-block;
            margin-top: 15px;
            color: #b00;
            text-decoration: none;
        }

        .logout:hover {
            text-decoration: underline;
        }

        /* Buttons (Add/Edit/Delete) */
        a.add-link,
        .btn-edit,
        .btn-delete,
        .btn {
            display: inline-block;
            font-size: 13px;
            margin-left: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        a.add-link {
            background-color: #007bff;
        }

        .btn-edit {
            background-color: #17a2b8;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        a.add-link:hover {
            background-color: #0056b3;
        }

        .btn-edit:hover {
            background-color: #0f778d;
        }

        .btn-delete:hover {
            background-color: #b02a37;
        }

        /* Section headers */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header h2 {
            border: none;
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        /* Top link */
        .top-links {
            text-align: right;
            margin-bottom: 20px;
        }

        .top-links a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }

        .top-links a:hover {
            text-decoration: underline;
        }

        /* Language section fix */
        .languages {
            margin-bottom: 35px;
        }

        .language-item {
            margin-bottom: 8px;
            padding: 8px 12px;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 6px;
        }

        .edit-btn-inline {
            font-size: 11px;
            padding: 4px 8px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .edit-btn-inline:hover {
            background: #0056b3;
        }

        .actions {
            margin-top: 8px;
        }

        .public-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            text-align: center;
        }

        .public-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .public-resume-section {
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
            border: 2px solid #c7d2fe;
        }

        .public-resume-section h3 {
            color: #4c1d95;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .public-resume-section p {
            color: #6d28d9;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="profile-photo-section" style="text-align:center; margin-bottom:20px;">
            <img class="avatar" src="<?= esc($avatar_src); ?>" alt="Profile picture" style="width:180px; height:180px; border-radius:50%; object-fit:cover; border:2px solid #ccc;">

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
            <p><?= nl2br(esc($user['about_me'])); ?></p>
        <?php endif; ?>

        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="main">

    <!-- EDUCATION -->
    <div class="section">
        <div class="section-header">
            <h2>Education</h2>
            <a href="education/add.php" class="add-link">+ Add</a>
    </div>
    <?php if (empty($education)): ?>
        <p>No education entries yet.</p>
    <?php else: ?>
        <?php foreach ($education as $edu): ?>
            <div class="item">
                <span class="item-title"><?= htmlspecialchars($edu['degree']); ?></span>,
                <?= htmlspecialchars($edu['school_name']); ?>
                <span class="dates">
                    <?= htmlspecialchars($edu['start_date']); ?>
                    <?php if (!empty($edu['end_date'])): ?> – <?= htmlspecialchars($edu['end_date']); ?><?php endif; ?>
                </span>
                <div class="clear"></div>

                <?php if (!empty($edu['field_of_study'])): ?>
                    <div class="item-subtitle">Field: <?= htmlspecialchars($edu['field_of_study']); ?></div>
                <?php endif; ?>

                <?php if (!empty($edu['description'])): ?>
                    <div><?= nl2br(htmlspecialchars($edu['description'])); ?></div>
                <?php endif; ?>

                <div class="actions" style="margin-top:5px;">
                    <a href="education/edit.php?id=<?= $edu['id']; ?>" class="btn btn-edit">Edit</a>
                    <a href="education/delete.php?id=<?= $edu['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this education record?');">Delete</a>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- EXPERIENCE -->
    <div class="section">
        <div class="section-header">
            <h2>Experience</h2>
            <a href="experience/add.php" class="add-link">+ Add</a>
    </div>
    <?php if (empty($experience)): ?>
        <p>No experience entries yet.</p>
    <?php else: ?>
        <?php foreach ($experience as $exp): ?>
            <div class="item">
                <span class="item-title"><?= esc($exp['job_title']); ?></span>,
                <?= esc($exp['company_name']); ?>
                <span class="dates">
                    <?= esc($exp['start_date']); ?>
                    <?php if (!empty($exp['end_date'])): ?> – <?= esc($exp['end_date']); ?><?php endif; ?>
                </span>
                <div class="clear"></div>

                <?php if (!empty($exp['location'])): ?>
                    <div class="item-subtitle">Location: <?= esc($exp['location']); ?></div>
                <?php endif; ?>

                <?php if (!empty($exp['description'])): ?>
                    <div><?= nl2br(esc($exp['description'])); ?></div>
                <?php endif; ?>

                <div class="actions" style="margin-top:5px;">
                    <a href="experience/edit.php?id=<?= $exp['id']; ?>" class="btn btn-edit">Edit</a>
                    <a href="experience/delete.php?id=<?= $exp['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this experience?');">Delete</a>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- PROJECTS -->
    <div class="section">
        <div class="section-header">
            <h2>Projects</h2>
            <a href="project/add.php" class="add-link">+ Add</a>
        </div>

        <?php
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $user_id]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($projects)): ?>
            <p>No projects added yet.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="item" style="margin-bottom:20px; border-bottom:1px solid #ddd; padding-bottom:15px;">
                    <h3 style="margin:0;"><?= htmlspecialchars($project['project_name']); ?></h3>
                    <p><?= nl2br(htmlspecialchars($project['description'])); ?></p>

                    <?php if (!empty($project['technologies'])): ?>
                        <p><strong>Technologies:</strong> <?= htmlspecialchars($project['technologies']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($project['project_link'])): ?>
                        <p><a href="<?= htmlspecialchars($project['project_link']); ?>" target="_blank" style="color:#007BFF;">View Project</a></p>
                    <?php endif; ?>

                    <?php if (!empty($project['image_path'])): ?>
                        <div style="margin-top:10px;">
                            <?php
                            $imgData = $project['image_path'];

                            // If it's a resource (PostgreSQL bytea stream), read it
                            if (is_resource($imgData)) {
                                $imgData = stream_get_contents($imgData);
                            }

                            // If it's binary data, base64 encode it
                            if (!empty($imgData)) {
                                $imgSrc = 'data:image/jpeg;base64,' . base64_encode($imgData);
                                echo '<img src="' . $imgSrc . '" alt="Project Image" style="max-width:100%; height:auto; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="actions" style="margin-top:10px;">
                        <a href="project/edit.php?id=<?= $project['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="project/delete.php?id=<?= $project['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this project?');">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- CERTIFICATIONS -->
    <div class="section">
        <div class="section-header">
            <h2>Certifications</h2>
            <a href="certifications/add.php" class="add-link">+ Add</a>
        </div>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = :uid ORDER BY date_received DESC NULLS LAST");
        $stmt->execute([':uid' => $user_id]);
        $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($certifications)): ?>
            <p>No certifications yet.</p>
        <?php else: ?>
            <?php foreach ($certifications as $cert): ?>
                <div class="item">
                    <strong><?= esc($cert['title']); ?></strong>
                    <?php if (!empty($cert['issuer'])): ?>
                        — <?= esc($cert['issuer']); ?>
                    <?php endif; ?>
                    <?php if (!empty($cert['date_received'])): ?>
                        <span class="dates"><?= esc($cert['date_received']); ?></span>
                    <?php endif; ?>
                    <div>
                        <?php if (!empty($cert['description'])): ?>
                            <p><?= nl2br(esc($cert['description'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cert['credential_url'])): ?>
                            <p><a href="<?= esc($cert['credential_url']); ?>" target="_blank">View Credential</a></p>
                        <?php endif; ?>
                    </div>
                    <div class="actions" style="margin-top:5px;">
                        <a href="certifications/edit.php?id=<?= $cert['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="certifications/delete.php?id=<?= $cert['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this certification?');">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

        <!-- TECHNICAL SKILLS -->
        <div class="section-header">
            <h2>Technical Skills</h2>
            <a href="skills/add.php" class="add-link">+ Add</a>
        </div>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT * FROM technical_skills WHERE user_id = :user_id ORDER BY category, skill_name");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $technical_skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $technical_skills = [];
        }
        ?>

        <?php if (count($technical_skills) === 0): ?>
            <p>No technical skills added yet.</p>
        <?php else: ?>
        <?php foreach ($technical_skills as $skill): ?>
            <div class="item" style="margin-bottom:10px;">
                <span class="item-title" style="font-weight:bold;"><?= htmlspecialchars($skill['skill_name']); ?></span>
                <span style="font-style:italic;">(<?= htmlspecialchars($skill['category']); ?>)</span>
                <span class="dates" style="float:right;"><?= htmlspecialchars($skill['proficiency']); ?></span>
                <div class="clear"></div>

                <div class="actions" style="margin-top:5px;">
                    <a href="skills/edit.php?id=<?= $skill['id']; ?>" class="btn btn-edit">Edit</a>
                    <a href="skills/delete.php?id=<?= $skill['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this skill?');">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- LANGUAGES -->
        <div class="section-header">
            <h2>Languages</h2>
            <a href="languages/add.php" class="add-link">+ Add</a>
        </div>
        <?php
        // Fetch languages for current user
        $languages = safeQuery($pdo, "
            SELECT id, language_name, proficiency
            FROM languages
            WHERE user_id = :id
            ORDER BY language_name ASC
        ", [':id' => $user_id]);
        ?>

        <?php if (empty($languages)): ?>
            <p>No languages added yet.</p>
        <?php else: ?>
            <?php foreach ($languages as $lang): ?>
                <div class="item">
                    <span class="item-title"><?= htmlspecialchars($lang['language_name']); ?></span>
                    <span class="dates"><?= htmlspecialchars($lang['proficiency']); ?></span>
                    <div class="clear"></div>

                    <div class="actions" style="margin-top:5px;">
                        <a href="languages/edit.php?id=<?= $lang['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="languages/delete.php?id=<?= $lang['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this language?');">Delete</a>
                    </div>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>


    <!-- PUBLIC RESUME SECTION -->
    <div class="public-resume-section">
        <h3>🌐 Your Public Resume is Ready!</h3>
        <p>Share your portfolio with employers, clients, or anyone else</p>
        <a href="public_resume.php?token=<?= htmlspecialchars($user['public_token']); ?>"
           target="_blank"
           class="public-btn">
           👁️ View Public Resume
        </a>
        <div style="margin-top: 15px;">
            <a href="edit_profile.php" style="color: #6d28d9; text-decoration: none; font-size: 14px;">
                ⚙️ Manage Public Link
            </a>
        </div>
    </div>
    </div>
</div>
</body>
</html>