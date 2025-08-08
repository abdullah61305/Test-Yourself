<?php
session_start();
require_once 'db/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 1;

// Get test information
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->execute([$test_id]);
$test = $stmt->fetch();

if (!$test) {
    header('Location: dashboard.php');
    exit();
}

$questions = json_decode($test['questions_json'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($test['test_name']); ?> - Test Yourself</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Test Yourself</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="result.php" class="nav-link">Results</a>
                    <a href="php/logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="test-container">
                <div class="test-header">
                    <h2><?php echo htmlspecialchars($test['test_name']); ?></h2>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <p class="progress-text">Question <span id="currentQuestion">1</span> of <span id="totalQuestions"><?php echo count($questions); ?></span></p>
                </div>

                <form id="testForm" class="test-form">
                    <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
                    
                    <div id="questionsContainer">
                        <!-- Questions will be loaded here by JavaScript -->
                    </div>

                    <div class="test-navigation">
                        <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">Previous</button>
                        <button type="button" id="nextBtn" class="btn btn-primary">Next</button>
                        <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">Submit Test</button>
                    </div>
                </form>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2024 Test Yourself. All rights reserved.</p>
        </footer>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const testId = <?php echo $test_id; ?>;
    </script>
    <script src="js/test-logic.js"></script>
</body>
</html>
