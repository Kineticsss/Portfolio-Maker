<?php
require 'dbconfig.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Invalid or missing token.");
}

$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, email, about_me, phone, address, github, linkedin, profile_pic
    FROM users WHERE public_token = :token
");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Resume not found. This link may have been regenerated or deleted.");
}

$user_id = $user['id'];

function fetchAll($pdo, $table, $user_id, $order = "id DESC") {
    $allowedTables = ['education', 'experience', 'skills', 'projects', 'certifications', 'languages', 'technical_skills'];
    if (!in_array($table, $allowedTables)) return [];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = :uid ORDER BY $order");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

$education      = fetchAll($pdo, 'education', $user_id, 'start_date DESC NULLS LAST');
$experience     = fetchAll($pdo, 'experience', $user_id, 'start_date DESC NULLS LAST');
$skills         = fetchAll($pdo, 'skills', $user_id, 'id ASC');
$technical_skills = fetchAll($pdo, 'technical_skills', $user_id, 'category, skill_name');
$projects       = fetchAll($pdo, 'projects', $user_id, 'id DESC');
$certifications = fetchAll($pdo, 'certifications', $user_id, 'date_received DESC NULLS LAST');
$languages      = fetchAll($pdo, 'languages', $user_id, 'language_name ASC');

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$avatar_src = '';
if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
    $avatar_src = $user['profile_pic'];
} else {
    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
      <rect fill="#007BFF" width="100%" height="100%"/>
      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="80" font-weight="bold">' . $initials . '</text>
    </svg>';
    $avatar_src = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

$grouped_skills = [];
foreach ($technical_skills as $skill) {
    $category = $skill['category'] ?? 'Other';
    if (!isset($grouped_skills[$category])) {
        $grouped_skills[$category] = [];
    }
    $grouped_skills[$category][] = $skill;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($user['first_name'] . ' ' . $user['last_name']) ?> - Resume</title>
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
    line-height: 1.6;
}

.resume {
    background: white;
    max-width: 1000px;
    margin: 0 auto;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    overflow: hidden;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 50px 40px;
    text-align: center;
}

.profile-pic {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    margin: 0 auto 20px;
    display: block;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.header h1 {
    font-size: 36px;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.header .tagline {
    font-size: 18px;
    opacity: 0.95;
    font-weight: 300;
}

.contact-bar {
    background: #f8f9fa;
    padding: 20px 40px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    border-bottom: 1px solid #e9ecef;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #495057;
    font-size: 14px;
}

.contact-item a {
    color: #007BFF;
    text-decoration: none;
    transition: color 0.2s;
}

.contact-item a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.content {
    padding: 40px;
}

.section {
    margin-bottom: 40px;
}

.section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #667eea;
    display: inline-block;
}

.about-text {
    color: #495057;
    font-size: 16px;
    line-height: 1.8;
    text-align: justify;
}

.item {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
    transition: transform 0.2s, box-shadow 0.2s;
}

.item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 10px;
}

.item-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.item-subtitle {
    color: #6c757d;
    font-size: 15px;
    margin-bottom: 5px;
}

.item-date {
    color: #6c757d;
    font-size: 14px;
    font-style: italic;
    white-space: nowrap;
}

.item-description {
    color: #495057;
    margin-top: 10px;
    line-height: 1.6;
}

.skills-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.skill-category {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-top: 3px solid #667eea;
}

.skill-category h3 {
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 12px;
}

.skill-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.skill-tag {
    background: white;
    color: #495057;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    border: 1px solid #dee2e6;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.skill-tag .proficiency {
    background: #667eea;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.languages-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.language-tag {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
}

.project-image {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 8px;
    margin-top: 15px;
    border: 1px solid #dee2e6;
}

.project-link {
    display: inline-block;
    margin-top: 10px;
    color: #007BFF;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.project-link:hover {
    color: #0056b3;
    transform: translateX(3px);
}

.footer {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 14px;
    border-top: 1px solid #e9ecef;
}

.back-btn {
    display: inline-block;
    margin-top: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.technologies {
    margin-top: 10px;
    font-size: 14px;
}

.tech-badge {
    display: inline-block;
    background: #e7f3ff;
    color: #0066cc;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    margin-right: 5px;
    margin-top: 5px;
}

@media (max-width: 768px) {
    body {
        padding: 20px 10px;
    }
    
    .header {
        padding: 30px 20px;
    }
    
    .header h1 {
        font-size: 28px;
    }
    
    .contact-bar {
        padding: 15px 20px;
        flex-direction: column;
        gap: 10px;
    }
    
    .content {
        padding: 20px;
    }
    
    .item-header {
        flex-direction: column;
    }
    
    .skills-grid {
        grid-template-columns: 1fr;
    }
}

@media print {
    body {
        background: white;
        padding: 0;
    }
    
    .resume {
        box-shadow: none;
    }
    
    .back-btn {
        display: none;
    }
    
    .item:hover {
        transform: none;
        box-shadow: none;
    }
}
</style>
</head>
<body>
<div class="resume">
    <!-- Header Section -->
    <div class="header">
        <img src="<?= esc($avatar_src) ?>" alt="Profile Picture" class="profile-pic">
        <h1><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        <?php if (!empty($user['about_me'])): ?>
            <p class="tagline"><?= esc(substr($user['about_me'], 0, 100)) . (strlen($user['about_me']) > 100 ? '...' : '') ?></p>
        <?php endif; ?>
    </div>

    <!-- Contact Bar -->
    <div class="contact-bar">
        <?php if (!empty($user['email'])): ?>
            <div class="contact-item">
                <span>📧</span>
                <a href="mailto:<?= esc($user['email']) ?>"><?= esc($user['email']) ?></a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($user['phone'])): ?>
            <div class="contact-item">
                <span>📱</span>
                <span><?= esc($user['phone']) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($user['address'])): ?>
            <div class="contact-item">
                <span>📍</span>
                <span><?= esc($user['address']) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($user['github'])): ?>
            <div class="contact-item">
                <span>💻</span>
                <a href="<?= esc($user['github']) ?>" target="_blank">GitHub</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($user['linkedin'])): ?>
            <div class="contact-item">
                <span>💼</span>
                <a href="<?= esc($user['linkedin']) ?>" target="_blank">LinkedIn</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- About Me Section -->
        <?php if (!empty($user['about_me'])): ?>
            <div class="section">
                <h2 class="section-title">About Me</h2>
                <p class="about-text"><?= nl2br(esc($user['about_me'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Experience Section -->
        <?php if (!empty($experience)): ?>
            <div class="section">
                <h2 class="section-title">Professional Experience</h2>
                <?php foreach ($experience as $exp): ?>
                    <div class="item">
                        <div class="item-header">
                            <div>
                                <div class="item-title"><?= esc($exp['job_title'] ?? $exp['position'] ?? 'Position') ?></div>
                                <div class="item-subtitle"><?= esc($exp['company_name'] ?? $exp['company'] ?? '') ?></div>
                                <?php if (!empty($exp['location'])): ?>
                                    <div class="item-subtitle">📍 <?= esc($exp['location']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-date">
                                <?= esc($exp['start_date'] ?? '') ?> - <?= esc($exp['end_date'] ?? 'Present') ?>
                            </div>
                        </div>
                        <?php if (!empty($exp['description'])): ?>
                            <div class="item-description"><?= nl2br(esc($exp['description'])) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Education Section -->
        <?php if (!empty($education)): ?>
            <div class="section">
                <h2 class="section-title">Education</h2>
                <?php foreach ($education as $edu): ?>
                    <div class="item">
                        <div class="item-header">
                            <div>
                                <div class="item-title"><?= esc($edu['degree']) ?></div>
                                <div class="item-subtitle"><?= esc($edu['school_name'] ?? $edu['school'] ?? '') ?></div>
                                <?php if (!empty($edu['field_of_study'])): ?>
                                    <div class="item-subtitle">Field: <?= esc($edu['field_of_study']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-date">
                                <?= esc($edu['start_date'] ?? '') ?> - <?= esc($edu['end_date'] ?? 'Present') ?>
                            </div>
                        </div>
                        <?php if (!empty($edu['description'])): ?>
                            <div class="item-description"><?= nl2br(esc($edu['description'])) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Projects Section -->
        <?php if (!empty($projects)): ?>
            <div class="section">
                <h2 class="section-title">Projects</h2>
                <?php foreach ($projects as $proj): ?>
                    <div class="item">
                        <div class="item-title"><?= esc($proj['project_name'] ?? $proj['title'] ?? 'Project') ?></div>
                        <?php if (!empty($proj['description'])): ?>
                            <div class="item-description"><?= nl2br(esc($proj['description'])) ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($proj['technologies'])): ?>
                            <div class="technologies">
                                <strong>Technologies:</strong>
                                <?php
                                $techs = explode(',', $proj['technologies']);
                                foreach ($techs as $tech): ?>
                                    <span class="tech-badge"><?= esc(trim($tech)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($proj['project_link'] ?? $proj['link'])): ?>
                            <a href="<?= esc($proj['project_link'] ?? $proj['link']) ?>" target="_blank" class="project-link">View Project →</a>
                        <?php endif; ?>
                        
                        <?php if (!empty($proj['image_path'])): ?>
                            <?php
                            $imgData = $proj['image_path'];
                            if (is_resource($imgData)) {
                                $imgData = stream_get_contents($imgData);
                            }
                            if (!empty($imgData)) {
                                $imgSrc = 'data:image/jpeg;base64,' . base64_encode($imgData);
                                echo '<img src="' . $imgSrc . '" alt="Project Image" class="project-image">';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Technical Skills Section -->
        <?php if (!empty($grouped_skills)): ?>
            <div class="section">
                <h2 class="section-title">Technical Skills</h2>
                <div class="skills-grid">
                    <?php foreach ($grouped_skills as $category => $categorySkills): ?>
                        <div class="skill-category">
                            <h3><?= esc($category) ?></h3>
                            <div class="skill-list">
                                <?php foreach ($categorySkills as $skill): ?>
                                    <span class="skill-tag">
                                        <?= esc($skill['skill_name']) ?>
                                        <?php if (!empty($skill['proficiency'])): ?>
                                            <span class="proficiency"><?= esc($skill['proficiency']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- General Skills Section (if no technical_skills table) -->
        <?php if (!empty($skills) && empty($grouped_skills)): ?>
            <div class="section">
                <h2 class="section-title">Skills</h2>
                <div class="skill-list">
                    <?php foreach ($skills as $s): ?>
                        <span class="skill-tag"><?= esc($s['skill_name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Certifications Section -->
        <?php if (!empty($certifications)): ?>
            <div class="section">
                <h2 class="section-title">Certifications</h2>
                <?php foreach ($certifications as $cert): ?>
                    <div class="item">
                        <div class="item-header">
                            <div>
                                <div class="item-title"><?= esc($cert['title'] ?? $cert['certificate_name'] ?? 'Certification') ?></div>
                                <div class="item-subtitle"><?= esc($cert['issuer'] ?? $cert['organization'] ?? '') ?></div>
                            </div>
                            <?php if (!empty($cert['date_received'])): ?>
                                <div class="item-date"><?= esc($cert['date_received']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($cert['description'])): ?>
                            <div class="item-description"><?= nl2br(esc($cert['description'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($cert['credential_url'])): ?>
                            <a href="<?= esc($cert['credential_url']) ?>" target="_blank" class="project-link">View Credential →</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Languages Section -->
        <?php if (!empty($languages)): ?>
            <div class="section">
                <h2 class="section-title">Languages</h2>
                <div class="languages-list">
                    <?php foreach ($languages as $lang): ?>
                        <span class="language-tag">
                            <?= esc($lang['language_name']) ?> - <?= esc($lang['proficiency']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This resume was generated using Portfolio Resume System</p>
        <p style="margin-top: 5px; font-size: 12px;">Last updated: <?= date('F j, Y') ?></p>
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
            <button onclick="window.print()" class="back-btn">🖨️ Print Resume</button>
            <a href="login.php" class="back-btn">Create Your Own Portfolio</a>
        </div>
    </div>
</div>
</body>
</html>