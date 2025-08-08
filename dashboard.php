<?php
session_start();
require_once 'db/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Get current week number
$currentWeek = date('W');
$currentYear = date('Y');

// Get user info
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get current week's test
$testRotation = [
    1 => ['name' => 'Big Five Personality', 'id' => 1],
    2 => ['name' => 'Anxiety Assessment', 'id' => 2],
    3 => ['name' => 'Focus & Attention', 'id' => 3],
    4 => ['name' => 'Stress Level', 'id' => 4],
    5 => ['name' => 'Emotional Intelligence', 'id' => 5]
];

$testIndex = (($currentWeek - 1) % 5) + 1;
$currentTest = $testRotation[$testIndex];

// Check if user has already taken this week's test
$stmt = $pdo->prepare("SELECT id FROM results WHERE user_id = ? AND week = ? AND YEAR(date_taken) = ?");
$stmt->execute([$_SESSION['user_id'], $currentWeek, $currentYear]);
$hasCompletedTest = $stmt->fetch() ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Test Yourself</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Test Yourself</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="result.php" class="nav-link">Results</a>
                    <a href="php/logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="dashboard">
                <div class="welcome-section">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p class="week-info">Current Week: <strong><?php echo $currentWeek; ?></strong></p>
                </div>

                <div class="test-section">
                    <div class="test-card">
                        <h3>This Week's Test</h3>
                        <h4><?php echo $currentTest['name']; ?></h4>
                        <p>Discover insights about your psychological profile with this week's assessment.</p>
                        
                        <?php if ($hasCompletedTest): ?>
                            <div class="completed-badge">
                                <span>✓ Completed this week</span>
                            </div>
                            <a href="result.php" class="btn btn-secondary">View Results</a>
                        <?php else: ?>
                            <a href="test.php?test_id=<?php echo $currentTest['id']; ?>" class="btn btn-primary">Start Test</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stats-section">
                    <h3>Your Progress</h3>
                    <div class="stats-grid">
                        <?php
                        // Get user's test completion stats
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total_tests FROM results WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $totalTests = $stmt->fetch()['total_tests'];

                        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT test_id) as unique_tests FROM results WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $uniqueTests = $stmt->fetch()['unique_tests'];

                        $stmt = $pdo->prepare("SELECT AVG(score) as avg_score FROM results WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $avgScore = $stmt->fetch()['avg_score'];
                        ?>
                        
                        <div class="stat-card">
                            <h4><?php echo $totalTests; ?></h4>
                            <p>Tests Completed</p>
                        </div>
                        <div class="stat-card">
                            <h4><?php echo $uniqueTests; ?></h4>
                            <p>Different Test Types</p>
                        </div>
                        <div class="stat-card">
                            <h4><?php echo $avgScore ? round($avgScore, 1) : '0'; ?>%</h4>
                            <p>Average Score</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2024 Test Yourself. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
