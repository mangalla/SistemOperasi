<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrival']) && isset($_POST['burst']) && isset($_POST['algorithm'])) {
    $arrival = $_POST['arrival'] ?? [];
    $burst = $_POST['burst'] ?? [];
    $algorithm = $_POST['algorithm'] ?? 'fcfs';
    $quantum = isset($_POST['quantum']) ? (int)$_POST['quantum'] : 1;

    if (is_array($arrival) && is_array($burst) && count($arrival) === count($burst)) {
        $n = count($arrival);
        $process = [];
        for ($i = 0; $i < $n; $i++) {
            $process[] = [
                'id' => $i,
                'arrival' => (int)$arrival[$i],
                'burst' => (int)$burst[$i],
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
        }

        ob_start();
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Arrival Time</th>
                    <th>Burst Time</th>
                    <th>Completion Time</th>
                    <th>Turnaround Time</th>
                    <th>Waiting Time</th>
                </tr>";

        for ($i = 0; $i < $n; $i++) {
            echo "<tr>
                    <td>P" . ($i+1) . "</td>
                    <td>{$arrival[$i]}</td>
                    <td>{$burst[$i]}</td>
                    <td>{$completion[$i]}</td>
                    <td>{$turnaround[$i]}</td>
                    <td>{$waiting[$i]}</td>
                  </tr>";
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
        echo "<div class='result-summary'>
                <p>Rata-rata Waiting Time (WT): <strong>" . number_format($avgWT, 2) . "</strong></p>
                <p>Rata-rata Turnaround Time (TAT): <strong>" . number_format($avgTAT, 2) . "</strong></p>
              </div>";

        $_SESSION['results'] = ob_get_clean();
    } else {
        $_SESSION['results'] = "<p>Data input tidak valid.</p>";
    }
}

header('Location: index.php');
exit;
?>