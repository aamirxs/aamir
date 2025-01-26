<?php
if (!defined('ADMIN_USER')) exit;

// Handle backup creation
if (isset($_POST['create_backup'])) {
    $backup_name = date('Y-m-d_H-i-s') . '_backup.tar.gz';
    $backup_path = '/var/backups/webpanel/' . $backup_name;
    
    if (!is_dir('/var/backups/webpanel')) {
        mkdir('/var/backups/webpanel', 0755, true);
    }
    
    $command = "tar -czf $backup_path /var/www/webpanel";
    shell_exec($command);
}

// Get list of existing backups
$backups = glob('/var/backups/webpanel/*.tar.gz');
?>

<div class="grid grid-cols-1 gap-6">
    <!-- Backup Creation -->
    <div class="dashboard-card p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Backup Management</h3>
            <form method="POST" class="inline">
                <button type="submit" name="create_backup" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>Create New Backup
                </button>
            </form>
        </div>

        <!-- Backup List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Backup Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo basename($backup); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo round(filesize($backup) / 1024 / 1024, 2); ?> MB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('Y-m-d H:i:s', filemtime($backup)); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="download_backup.php?file=<?php echo urlencode(basename($backup)); ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="delete_backup.php?file=<?php echo urlencode(basename($backup)); ?>" 
                                   class="text-red-600 hover:text-red-900"
                                   onclick="return confirm('Are you sure you want to delete this backup?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 