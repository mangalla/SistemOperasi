<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling Calculator</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(to right, #141e30, #243b55);
            color: #fff;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background-color: #2a2d3e;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.4);
            padding: 30px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }
        .input-group input, .input-group select {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #555;
            background-color: #1c1f2e;
            color: #fff;
        }
        .input-group input:not(:placeholder-shown) {
            color: white;
        }
        .input-group button {
            padding: 8px 10px;
            background-color: #e63946;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
        }
        button {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
            margin-right: 10px;
        }
        button:hover {
            transform: scale(1.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #353a50;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #444;
        }
        th {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
        }
        .gantt {
            display: flex;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .gantt-bar {
            padding: 10px;
            color: #fff;
            text-align: center;
            margin-right: 4px;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            min-width: 60px;
        }
        .result-summary {
            margin-top: 30px;
            font-size: 18px;
            text-align: center;
        }
        #quantum-input {
            display: none;
        }
        #results {
            margin-top: 30px;
        }
        .priority-input {
            display: none;
        }
        .editable-input {
            background-color: #2a2d3e;
            border: 1px solid #777;
            color: #fff;
        }
        .hidden-column {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Scheduling Calculator</h1>
        <form method="POST" id="process-form">
            <div class="input-group">
                <select name="algorithm" id="algorithm-select" required onchange="toggleQuantumInput()">
                    <option value="fcfs" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'fcfs' ? 'selected' : ''; ?>>FCFS</option>
                    <option value="sjf_preemptive" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'sjf_preemptive' ? 'selected' : ''; ?>>SJF Preemptive</option>
                    <option value="sjf_non_preemptive" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'sjf_non_preemptive' ? 'selected' : ''; ?>>SJF Non-Preemptive</option>
                    <option value="round_robin" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'round_robin' ? 'selected' : ''; ?>>Round Robin</option>
                    <option value="priority_preemptive" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'priority_preemptive' ? 'selected' : ''; ?>>Priority Preemptive</option>
                    <option value="priority_non_preemptive" <?php echo isset($_POST['algorithm']) && $_POST['algorithm'] == 'priority_non_preemptive' ? 'selected' : ''; ?>>Priority Non-Preemptive</option>
                </select>
                <input type="number" name="quantum" id="quantum-input" placeholder="Time Quantum" min="1" value="<?php echo isset($_POST['quantum']) ? $_POST['quantum'] : ''; ?>">
            </div>
            <div id="process-container">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['arrival'])) {
                    $arrival = $_POST['arrival'];
                    $burst = $_POST['burst'];
                    $priority = $_POST['priority'] ?? array_fill(0, count($_POST['arrival']), 0);
                    $n = count($arrival);
                    for ($i = 0; $i < $n; $i++) {
                        $isPriority = isset($_POST['algorithm']) && in_array($_POST['algorithm'], ['priority_preemptive', 'priority_non_preemptive']);
                        echo "<div class='input-group'>";
                        echo "<input type='number' name='arrival[]' placeholder='Arrival Time' required value='{$arrival[$i]}'>";
                        echo "<input type='number' name='burst[]' placeholder='Burst Time' required value='{$burst[$i]}'>";
                        echo "<input type='number' name='priority[]' placeholder='Priority' class='priority-input' value='{$priority[$i]}' " . ($isPriority ? '' : 'style="display: none;"') . ">";
                        echo "<button type='button' onclick='removeProcess(this)'>Hapus</button>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='input-group'>";
                    echo "<input type='number' name='arrival[]' placeholder='Arrival Time' required>";
                    echo "<input type='number' name='burst[]' placeholder='Burst Time' required>";
                    echo "<input type='number' name='priority[]' placeholder='Priority' class='priority-input' style='display: none;'>";
                    echo "<button type='button' onclick='removeProcess(this)'>Hapus</button>";
                    echo "</div>";
                }
                ?>
            </div>
            <button type="button" onclick="addProcess()">+ Tambah Proses</button>
            <button type="submit">Hitung</button>
            <button type="button" onclick="resetForm()">Reset</button>
            <button type="button" onclick="editResults()" id="edit-button" style="display: none;">Edit</button>
        </form>
        <div id="results">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrival']) && isset($_POST['burst']) && isset($_POST['algorithm'])) {
                $arrival = $_POST['arrival'];
                $burst = $_POST['burst'];
                $priority = $_POST['priority'] ?? array_fill(0, count($_POST['arrival']), 0);
                $algorithm = $_POST['algorithm'];
                $quantum = isset($_POST['quantum']) ? (int)$_POST['quantum'] : 1;

                if (is_array($arrival) && is_array($burst) && count($arrival) === count($burst)) {
                    $n = count($arrival);
                    $process = [];
                    for ($i = 0; $i < $n; $i++) {
                        $process[] = [
                            'id' => $i,
                            'arrival' => (int)$arrival[$i],
                            'burst' => (int)$burst[$i],
                            'priority' => (int)($priority[$i] ?? 0),
                            'remaining' => (int)$burst[$i]
                        ];
                    }

                    $completion = array_fill(0, $n, 0);
                    $turnaround = array_fill(0, $n, 0);
                    $waiting = array_fill(0, $n, 0);
                    $ganttChart = [];
                    $colors = ['#f94144', '#f3722c', '#f9c74f', '#90be6d', '#43aa8b', '#577590', '#9b5de5', '#5f0f40', '#00b4d8'];

                    if ($algorithm === 'fcfs') {
                        usort($process, function($a, $b) {
                            return $a['arrival'] - $b['arrival'];
                        });

                        $currentTime = 0;
                        foreach ($process as $p) {
                            if ($currentTime < $p['arrival']) {
                                $currentTime = $p['arrival'];
                            }
                            $start = $currentTime;
                            $completion[$p['id']] = $start + $p['burst'];
                            $turnaround[$p['id']] = $completion[$p['id']] - $p['arrival'];
                            $waiting[$p['id']] = $turnaround[$p['id']] - $p['burst'];
                            $ganttChart[] = [
                                'process' => "P" . ($p['id'] + 1),
                                'start' => $start,
                                'end' => $completion[$p['id']],
                                'color' => $colors[$p['id'] % count($colors)]
                            ];
                            $currentTime = $completion[$p['id']];
                        }
                    } elseif ($algorithm === 'sjf_preemptive') {
                        $currentTime = 0;
                        $completed = 0;
                        $lastProcess = -1;

                        while ($completed < $n) {
                            $minBurst = PHP_INT_MAX;
                            $currentProcess = -1;

                            foreach ($process as $i => $p) {
                                if ($p['arrival'] <= $currentTime && $p['remaining'] > 0 && $p['remaining'] < $minBurst) {
                                    $minBurst = $p['remaining'];
                                    $currentProcess = $i;
                                }
                            }

                            if ($currentProcess === -1) {
                                $currentTime++;
                                continue;
                            }

                            if ($lastProcess !== $currentProcess && $lastProcess !== -1) {
                                $ganttChart[] = [
                                    'process' => "P" . ($process[$lastProcess]['id'] + 1),
                                    'start' => $ganttChart[count($ganttChart) - 1]['end'] ?? min(array_column($process, 'arrival')),
                                    'end' => $currentTime,
                                    'color' => $colors[$process[$lastProcess]['id'] % count($colors)]
                                ];
                            }

                            $process[$currentProcess]['remaining']--;
                            $currentTime++;

                            if ($process[$currentProcess]['remaining'] === 0) {
                                $completed++;
                                $completion[$process[$currentProcess]['id']] = $currentTime;
                                $turnaround[$process[$currentProcess]['id']] = $completion[$process[$currentProcess]['id']] - $process[$currentProcess]['arrival'];
                                $waiting[$process[$currentProcess]['id']] = $turnaround[$process[$currentProcess]['id']] - $process[$currentProcess]['burst'];
                            }
                            if ($lastProcess !== $currentProcess) {
                                $ganttChart[] = [
                                    'process' => "P" . ($process[$currentProcess]['id'] + 1),
                                    'start' => $currentTime - 1,
                                    'end' => $currentTime,
                                    'color' => $colors[$process[$currentProcess]['id'] % count($colors)]
                                ];
                            } else {
                                if (!empty($ganttChart)) {
                                    $ganttChart[count($ganttChart) - 1]['end'] = $currentTime;
                                }
                            }

                            $lastProcess = $currentProcess;
                        }
                    } elseif ($algorithm === 'sjf_non_preemptive') {
                        usort($process, function($a, $b) {
                            return $a['arrival'] - $b['arrival'];
                        });

                        $currentTime = 0;
                        $completed = [];
                        while (count($completed) < $n) {
                            $minBurst = PHP_INT_MAX;
                            $currentProcess = -1;

                            foreach ($process as $i => $p) {
                                if ($p['arrival'] <= $currentTime && !in_array($i, $completed) && $p['burst'] < $minBurst) {
                                    $minBurst = $p['burst'];
                                    $currentProcess = $i;
                                }
                            }

                            if ($currentProcess === -1) {
                                $currentTime++;
                                continue;
                            }

                            $start = $currentTime;
                            $currentTime += $process[$currentProcess]['burst'];
                            $completion[$process[$currentProcess]['id']] = $currentTime;
                            $turnaround[$process[$currentProcess]['id']] = $currentTime - $process[$currentProcess]['arrival'];
                            $waiting[$process[$currentProcess]['id']] = $turnaround[$process[$currentProcess]['id']] - $process[$currentProcess]['burst'];
                            $ganttChart[] = [
                                'process' => "P" . ($process[$currentProcess]['id'] + 1),
                                'start' => $start,
                                'end' => $currentTime,
                                'color' => $colors[$process[$currentProcess]['id'] % count($colors)]
                            ];
                            $completed[] = $currentProcess;
                        }
                    } elseif ($algorithm === 'round_robin') {
                        $queue = [];
                        $currentTime = 0;
                        $completed = 0;
                        $index = 0;

                        while ($completed < $n) {
                            while ($index < $n && $process[$index]['arrival'] <= $currentTime) {
                                $queue[] = $index;
                                $index++;
                            }

                            if (empty($queue)) {
                                $currentTime++;
                                continue;
                            }

                            $currentProcess = array_shift($queue);
                            $start = $currentTime;
                            $executionTime = min($quantum, $process[$currentProcess]['remaining']);
                            $currentTime += $executionTime;
                            $process[$currentProcess]['remaining'] -= $executionTime;

                            $ganttChart[] = [
                                'process' => "P" . ($process[$currentProcess]['id'] + 1),
                                'start' => $start,
                                'end' => $currentTime,
                                'color' => $colors[$process[$currentProcess]['id'] % count($colors)]
                            ];

                            for ($i = $index; $i < $n; $i++) {
                                if ($process[$i]['arrival'] <= $currentTime) {
                                    $queue[] = $i;
                                    $index++;
                                }
                            }

                            if ($process[$currentProcess]['remaining'] > 0) {
                                $queue[] = $currentProcess;
                            } else {
                                $completed++;
                                $completion[$process[$currentProcess]['id']] = $currentTime;
                                $turnaround[$process[$currentProcess]['id']] = $currentTime - $process[$currentProcess]['arrival'];
                                $waiting[$process[$currentProcess]['id']] = $turnaround[$process[$currentProcess]['id']] - $process[$currentProcess]['burst'];
                            }
                        }
                    } elseif ($algorithm === 'priority_preemptive') {
                        $currentTime = 0;
                        $completed = 0;
                        $lastProcess = -1;

                        while ($completed < $n) {
                            $highestPriority = PHP_INT_MAX;
                            $currentProcess = -1;

                            foreach ($process as $i => $p) {
                                if ($p['arrival'] <= $currentTime && $p['remaining'] > 0 && $p['priority'] < $highestPriority) {
                                    $highestPriority = $p['priority'];
                                    $currentProcess = $i;
                                }
                            }

                            if ($currentProcess === -1) {
                                $currentTime++;
                                continue;
                            }

                            if ($lastProcess !== $currentProcess && $lastProcess !== -1) {
                                $ganttChart[] = [
                                    'process' => "P" . ($process[$lastProcess]['id'] + 1),
                                    'start' => $ganttChart[count($ganttChart) - 1]['end'] ?? min(array_column($process, 'arrival')),
                                    'end' => $currentTime,
                                    'color' => $colors[$process[$lastProcess]['id'] % count($colors)]
                                ];
                            }

                            $process[$currentProcess]['remaining']--;
                            $currentTime++;

                            if ($process[$currentProcess]['remaining'] === 0) {
                                $completed++;
                                $completion[$process[$currentProcess]['id']] = $currentTime;
                                $turnaround[$process[$currentProcess]['id']] = $completion[$process[$currentProcess]['id']] - $process[$currentProcess]['arrival'];
                                $waiting[$process[$currentProcess]['id']] = $turnaround[$process[$currentProcess]['id']] - $process[$currentProcess]['burst'];
                            }
                            if ($lastProcess !== $currentProcess) {
                                $ganttChart[] = [
                                    'process' => "P" . ($process[$currentProcess]['id'] + 1),
                                    'start' => $currentTime - 1,
                                    'end' => $currentTime,
                                    'color' => $colors[$process[$currentProcess]['id'] % count($colors)]
                                ];
                            } else {
                                if (!empty($ganttChart)) {
                                    $ganttChart[count($ganttChart) - 1]['end'] = $currentTime;
                                }
                            }

                            $lastProcess = $currentProcess;
                        }
                    } elseif ($algorithm === 'priority_non_preemptive') {
                        usort($process, function($a, $b) {
                            return $a['arrival'] - $b['arrival'];
                        });

                        $currentTime = 0;
                        $completed = [];
                        while (count($completed) < $n) {
                            $highestPriority = PHP_INT_MAX;
                            $currentProcess = -1;

                            foreach ($process as $i => $p) {
                                if ($p['arrival'] <= $currentTime && !in_array($i, $completed) && $p['priority'] < $highestPriority) {
                                    $highestPriority = $p['priority'];
                                    $currentProcess = $i;
                                }
                            }

                            if ($currentProcess === -1) {
                                $currentTime++;
                                continue;
                            }

                            $start = $currentTime;
                            $currentTime += $process[$currentProcess]['burst'];
                            $completion[$process[$currentProcess]['id']] = $currentTime;
                            $turnaround[$process[$currentProcess]['id']] = $currentTime - $process[$currentProcess]['arrival'];
                            $waiting[$process[$currentProcess]['id']] = $turnaround[$process[$currentProcess]['id']] - $process[$currentProcess]['burst'];
                            $ganttChart[] = [
                                'process' => "P" . ($process[$currentProcess]['id'] + 1),
                                'start' => $start,
                                'end' => $currentTime,
                                'color' => $colors[$process[$currentProcess]['id'] % count($colors)]
                            ];
                            $completed[] = $currentProcess;
                        }
                    }

                    $isPriorityAlgorithm = in_array($algorithm, ['priority_preemptive', 'priority_non_preemptive']);
                    echo "<table id='results-table'>";
                    echo "<tr>";
                    echo "<th>ID</th>";
                    echo "<th>Arrival Time</th>";
                    echo "<th>Burst Time</th>";
                    echo "<th class='" . ($isPriorityAlgorithm ? '' : 'hidden-column') . "'>Priority</th>";
                    echo "<th>Completion Time</th>";
                    echo "<th>Turnaround Time</th>";
                    echo "<th>Waiting Time</th>";
                    echo "</tr>";

                    for ($i = 0; $i < $n; $i++) {
                        $priorityValue = $isPriorityAlgorithm ? $priority[$i] : '-';
                        echo "<tr>";
                        echo "<td>P" . ($i+1) . "</td>";
                        echo "<td><input type='number' class='editable-input' name='edit_arrival[]' value='{$arrival[$i]}' readonly></td>";
                        echo "<td><input type='number' class='editable-input' name='edit_burst[]' value='{$burst[$i]}' readonly></td>";
                        echo "<td class='" . ($isPriorityAlgorithm ? '' : 'hidden-column') . "'><input type='number' class='editable-input priority-input' name='edit_priority[]' value='{$priorityValue}' readonly></td>";
                        echo "<td>{$completion[$i]}</td>";
                        echo "<td>{$turnaround[$i]}</td>";
                        echo "<td>{$waiting[$i]}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";

                    echo "<div class='gantt'>";
                    foreach ($ganttChart as $bar) {
                        $width = ($bar['end'] - $bar['start']) * 30;
                        echo "<div class='gantt-bar' style='width: {$width}px; background-color: {$bar['color']};'>{$bar['process']}<br><small>{$bar['start']} - {$bar['end']}</small></div>";
                    }
                    echo "</div>";

                    $avgWT = array_sum($waiting) / $n;
                    $avgTAT = array_sum($turnaround) / $n;
                    echo "<div class='result-summary'>";
                    echo "<p>Rata-rata Waiting Time (WT): <strong>" . number_format($avgWT, 2) . "</strong></p>";
                    echo "<p>Rata-rata Turnaround Time (TAT): <strong>" . number_format($avgTAT, 2) . "</strong></p>";
                    echo "</div>";
                } else {
                    echo "<p>Data input tidak valid.</p>";
                }
            }
            ?>
        </div>
    </div>

    <script>
        function addProcess() {
            const container = document.getElementById('process-container');
            const group = document.createElement('div');
            group.className = 'input-group';
            const isPriorityAlgorithm = ['priority_preemptive', 'priority_non_preemptive'].includes(document.getElementById('algorithm-select').value);
            group.innerHTML = `
                <input type="number" name="arrival[]" placeholder="Arrival Time" required>
                <input type="number" name="burst[]" placeholder="Burst Time" required>
                <input type="number" name="priority[]" placeholder="Priority" class="priority-input" ${isPriorityAlgorithm ? '' : 'style="display: none;"'}>
                <button type="button" onclick="removeProcess(this)">Hapus</button>
            `;
            container.appendChild(group);
            togglePriorityInputs();
        }

        function removeProcess(button) {
            const group = button.parentElement;
            group.remove();
        }

        function resetForm() {
            const container = document.getElementById('process-container');
            container.innerHTML = '';
            addProcess();
            document.getElementById('process-form').reset();
            toggleQuantumInput();
            togglePriorityInputs();
            document.getElementById('results').innerHTML = '';
            document.getElementById('edit-button').style.display = 'none';
        }

        function toggleQuantumInput() {
            const algorithm = document.getElementById('algorithm-select').value;
            const quantumInput = document.getElementById('quantum-input');
            if (algorithm === 'round_robin') {
                quantumInput.style.display = 'block';
                quantumInput.required = true;
            } else {
                quantumInput.style.display = 'none';
                quantumInput.required = false;
            }
            togglePriorityInputs();
        }

        function togglePriorityInputs() {
            const algorithm = document.getElementById('algorithm-select').value;
            const priorityInputs = document.querySelectorAll('.priority-input');
            const isPriorityAlgorithm = ['priority_preemptive', 'priority_non_preemptive'].includes(algorithm);
            priorityInputs.forEach(input => {
                input.style.display = isPriorityAlgorithm ? 'block' : 'none';
                input.required = isPriorityAlgorithm;
            });
        }

        function editResults() {
            const inputs = document.querySelectorAll('#results-table .editable-input');
            const isEditing = inputs[0]?.hasAttribute('readonly');
            
            if (isEditing) {
                inputs.forEach(input => input.removeAttribute('readonly'));
                document.getElementById('edit-button').textContent = 'Save';
            } else {
                const form = document.getElementById('process-container');
                form.innerHTML = '';
                
                const rows = document.querySelectorAll('#results-table tr');
                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].querySelectorAll('.editable-input');
                    const group = document.createElement('div');
                    group.className = 'input-group';
                    group.innerHTML = `
                        <input type="number" name="arrival[]" value="${cells[0].value}" placeholder="Arrival Time" required>
                        <input type="number" name="burst[]" value="${cells[1].value}" placeholder="Burst Time" required>
                        <input type="number" name="priority[]" value="${cells[2].value}" placeholder="Priority" class="priority-input">
                        <button type="button" onclick="removeProcess(this)">Hapus</button>
                    `;
                    form.appendChild(group);
                }
                
                inputs.forEach(input => input.setAttribute('readonly', true));
                document.getElementById('edit-button').textContent = 'Edit';
                togglePriorityInputs();
                document.getElementById('results').innerHTML = '';
                document.getElementById('edit-button').style.display = 'none';
            }
        }

        document.getElementById('process-form').addEventListener('submit', function() {
            document.getElementById('edit-button').style.display = 'block';
        });

        toggleQuantumInput();
        togglePriorityInputs();
    </script>
</body>
</html>