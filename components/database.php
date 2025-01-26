<?php
if (!defined('ADMIN_USER')) exit;

// Database connection
$db = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Handle database creation
if (isset($_POST['create_db'])) {
    $db_name = mysqli_real_escape_string($db, $_POST['db_name']);
    $db->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
}

// Get list of databases
$result = $db->query("SHOW DATABASES");
$databases = [];
while ($row = $result->fetch_array()) {
    $databases[] = $row[0];
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Database Creation -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">Create Database</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Database Name</label>
                <input type="text" name="db_name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <button type="submit" name="create_db" class="btn-primary">Create Database</button>
        </form>
    </div>

    <!-- Database List -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">Databases</h3>
        <div class="space-y-2">
            <?php foreach ($databases as $database): ?>
                <?php if (!in_array($database, ['information_schema', 'mysql', 'performance_schema'])): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span><?php echo htmlspecialchars($database); ?></span>
                        <div class="space-x-2">
                            <a href="phpmyadmin/index.php?db=<?php echo urlencode($database); ?>" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="delete_db.php?db=<?php echo urlencode($database); ?>" 
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Are you sure you want to delete this database?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div> 