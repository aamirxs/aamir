<?php
if (!defined('ADMIN_USER')) exit;
?>

<div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-4">Web Terminal</h2>
    
    <div id="terminal" class="bg-black text-green-400 p-4 rounded-lg h-96 overflow-y-auto font-mono">
        <div id="output"></div>
        <div class="flex items-center">
            <span class="mr-2">$</span>
            <input type="text" id="command" 
                   class="bg-transparent border-none outline-none flex-1 text-green-400" 
                   autocomplete="off">
        </div>
    </div>
</div>

<script>
document.getElementById('command').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const command = this.value;
        this.value = '';
        
        fetch('terminal_exec.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ command: command })
        })
        .then(response => response.text())
        .then(output => {
            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML += `<div>$ ${command}</div>`;
            outputDiv.innerHTML += `<div>${output}</div>`;
            outputDiv.scrollTop = outputDiv.scrollHeight;
        });
    }
});
</script> 