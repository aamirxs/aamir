<?php
if (!defined('ADMIN_USER')) exit;

$current_dir = $_GET['dir'] ?? '/var/www';
$current_dir = realpath($current_dir);

// Security check
if (strpos($current_dir, '/var/www') !== 0) {
    $current_dir = '/var/www';
}

$files = scandir($current_dir);
?>

<div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-4">File Manager</h2>
    
    <div class="mb-4">
        <span class="text-gray-600">Current Directory: <?php echo htmlspecialchars($current_dir); ?></span>
    </div>

    <div class="grid grid-cols-1 gap-2">
        <?php foreach ($files as $file): ?>
            <?php if ($file != '.' && $file != '..'): ?>
                <?php
                $full_path = $current_dir . '/' . $file;
                $is_dir = is_dir($full_path);
                ?>
                <div class="flex items-center justify-between p-2 hover:bg-gray-100 rounded">
                    <div class="flex items-center">
                        <span class="<?php echo $is_dir ? 'text-blue-500' : 'text-gray-600'; ?>">
                            <?php echo htmlspecialchars($file); ?>
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($is_dir): ?>
                            <a href="?page=filemanager&dir=<?php echo urlencode($full_path); ?>" 
                               class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Open
                            </a>
                        <?php else: ?>
                            <button onclick="editFile('<?php echo htmlspecialchars($full_path); ?>')"
                                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                Edit
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div> 