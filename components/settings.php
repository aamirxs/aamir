<?php
if (!defined('ADMIN_USER')) exit;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    
    if (isset($_POST['update_php'])) {
        shell_exec('sudo update-alternatives --set php /usr/bin/php' . $_POST['php_version']);
        $message = 'PHP version updated successfully!';
    }
    
    if (isset($_POST['update_memory'])) {
        $memory_limit = $_POST['memory_limit'] . 'M';
        shell_exec("sed -i 's/memory_limit = .*/memory_limit = {$memory_limit}/' /etc/php/*/apache2/php.ini");
        $message = 'Memory limit updated successfully!';
    }
}

// Get current PHP version
$current_php = PHP_VERSION;
$available_php_versions = glob('/usr/bin/php[0-9].[0-9]');
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- PHP Settings -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">PHP Configuration</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">PHP Version</label>
                <select name="php_version" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <?php foreach ($available_php_versions as $version): ?>
                        <?php $ver = basename($version); ?>
                        <option value="<?php echo $ver; ?>"><?php echo $ver; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Memory Limit</label>
                <input type="number" name="memory_limit" value="128" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <button type="submit" name="update_php" class="btn-primary">Update PHP Settings</button>
        </form>
    </div>

    <!-- Security Settings -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">Security Settings</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">SSH Access</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="ssh_enabled" class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="ml-2">Enable SSH Access</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Firewall Rules</label>
                <textarea name="firewall_rules" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <button type="submit" name="update_security" class="btn-primary">Update Security Settings</button>
        </form>
    </div>
</div> 