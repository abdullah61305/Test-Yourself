<?php
session_start();
require_once 'db/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Get user's test results
$stmt = $pdo->prepare("
    SELECT r.*, t.test_name 
    FROM results r 
    JOIN tests t ON r.test_id = t.id 
    WHERE r.user_id = ? 
    ORDER BY r.date_taken DESC
");
$stmt->execute([$_SESSION['user_id']]);
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Test Yourself</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Test Yourself</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="result.php" class="nav-link active">Results</a>
                    <a href="php/logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="results-container">
                <h2>Your Test Results</h2>

                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <p>You haven't completed any tests yet.</p>
                        <a href="dashboard.php" class="btn btn-primary">Take Your First Test</a>
                    </div>
                <?php else: ?>
                    <div class="chart-section">
                        <h3>Progress Over Time</h3>
                        <canvas id="progressChart"></canvas>
                    </div>

                    <div class="results-list">
                        <h3>Recent Results</h3>
                        <?php foreach ($results as $result): ?>
                            <div class="result-card">
                                <div class="result-header">
                                    <h4><?php echo htmlspecialchars($result['test_name']); ?></h4>
                                    <span class="result-score"><?php echo $result['score']; ?>%</span>
                                </div>
                                <div class="result-details">
                                    <p>Week <?php echo $result['week']; ?> • <?php echo date('M j, Y', strtotime($result['date_taken'])); ?></p>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $result['score']; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2024 Test Yourself. All rights reserved.</p>
        </footer>
    </div>

    <?php if (!empty($results)): ?>
    <script>
        // Load chart data
        fetch('php/getGraphData.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('progressChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Test Scores Over Time'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Score (%)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Week'
                                }
                            }
                        }
                    }
                });
            });
    </script>
    <?php endif; ?>
</body>
</html>
