<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling Calculator</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Scheduling Calculator</h1>
        <form method="POST" id="process-form" action="process.php">
            <div class="input-group">
                <select name="algorithm" id="algorithm-select" required onchange="toggleQuantumInput()">
                    <option value="fcfs">FCFS</option>
                    <option value="sjf_preemptive">SJF Preemptive</option>
                    <option value="sjf_non_preemptive">SJF Non-Preemptive</option>
                    <option value="round_robin">Round Robin</option>
                </select>
                <input type="number" name="quantum" id="quantum-input" placeholder="Time Quantum" min="1">
            </div>
            <div id="process-container">
                <div class="input-group">
                    <input type="number" name="arrival[]" placeholder="Arrival Time" required>
                    <input type="number" name="burst[]" placeholder="Burst Time" required>
                    <button type="button" onclick="removeProcess(this)">Hapus</button>
                </div>
            </div>
            <button type="button" onclick="addProcess()">+ Tambah Proses</button>
            <button type="submit">Hitung</button>
            <button type="button" onclick="resetForm()">Reset</button>
            <button type="button" onclick="clearResults()">Clear Results</button>
        </form>
        <div id="results">
            <?php
            // Display results if available (from process.php)
            if (isset($_SESSION['results'])) {
                echo $_SESSION['results'];
                unset($_SESSION['results']); // Clear results after displaying
            }
            ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
<?php
session_start(); // Start session to access results
?>