<?php
$dataFile = __DIR__ . '/data/sessions.json';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$sessions = json_decode(file_get_contents($dataFile), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $subject = trim($_POST['subject'] ?? '');
        $minutes = (int)($_POST['minutes'] ?? 25);

        if ($subject !== '' && $minutes > 0) {
            $sessions[] = [
                'id' => uniqid(),
                'subject' => htmlspecialchars($subject),
                'minutes' => $minutes,
                'date' => date('d M Y, h:i A')
            ];
            file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT));
        }
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        $sessions = array_values(array_filter($sessions, fn($s) => $s['id'] !== $id));
        file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT));
    }

    header('Location: index.php');
    exit;
}

$totalMinutes = array_sum(array_column($sessions, 'minutes'));
$totalSessions = count($sessions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FocusFlow - Study Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <div>
            <h1>FocusFlow</h1>
            <p>Simple Study Focus Session Tracker</p>
        </div>
        <div class="badge">PHP Mini Project</div>
    </header>

    <section class="stats">
        <div class="card"><span>Total Sessions</span><strong><?= $totalSessions ?></strong></div>
        <div class="card"><span>Study Minutes</span><strong><?= $totalMinutes ?></strong></div>
        <div class="card"><span>Hours</span><strong><?= number_format($totalMinutes / 60, 1) ?></strong></div>
    </section>

    <section class="panel">
        <h2>Add Study Session</h2>
        <form method="POST">
            <input type="text" name="subject" placeholder="e.g. Computer Networks" required>
            <select name="minutes">
                <option value="25">25 minutes</option>
                <option value="45">45 minutes</option>
                <option value="60">60 minutes</option>
                <option value="90">90 minutes</option>
            </select>
            <button name="add" value="1">+ Add Session</button>
        </form>
    </section>

    <section class="panel">
        <h2>Recent Sessions</h2>
        <?php if (!$sessions): ?>
            <div class="empty">No sessions yet. Add your first study session!</div>
        <?php else: ?>
            <div class="session-list">
            <?php foreach (array_reverse($sessions) as $session): ?>
                <div class="session">
                    <div>
                        <strong><?= $session['subject'] ?></strong>
                        <small><?= $session['date'] ?></small>
                    </div>
                    <div class="right">
                        <span><?= $session['minutes'] ?> min</span>
                        <form method="POST">
                            <button class="delete" name="delete" value="<?= $session['id'] ?>">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
