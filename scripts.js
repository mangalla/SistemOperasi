function addProcess() {
    const container = document.getElementById('process-container');
    const group = document.createElement('div');
    group.className = 'input-group';
    group.innerHTML = `
        <input type="number" name="arrival[]" placeholder="Arrival Time" required>
        <input type="number" name="burst[]" placeholder="Burst Time" required>
        <button type="button" onclick="removeProcess(this)">Hapus</button>
    `;
    container.appendChild(group);
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
    clearResults();
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
}

function clearResults() {
    const results = document.getElementById('results');
    results.innerHTML = '';
}

// Clear results on form submission
document.getElementById('process-form').addEventListener('submit', function() {
    clearResults();
});

// Initialize quantum input visibility
toggleQuantumInput();