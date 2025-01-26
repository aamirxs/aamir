<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Hosting Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-72 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl">
            <div class="p-6">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-server mr-3"></i>
                    Web Panel
                </h1>
            </div>
            <nav class="mt-6">
                <a href="?page=dashboard" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="?page=filemanager" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'filemanager') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-folder w-5"></i>
                    <span class="ml-3">File Manager</span>
                </a>
                <a href="?page=terminal" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'terminal') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-terminal w-5"></i>
                    <span class="ml-3">Terminal</span>
                </a>
                <a href="?page=settings" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'settings') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">Settings</span>
                </a>
                <a href="?page=backup" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'backup') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-archive w-5"></i>
                    <span class="ml-3">Backups</span>
                </a>
                <a href="?page=database" class="flex items-center px-6 py-4 hover:bg-blue-700 transition-colors duration-200 <?php echo (isset($_GET['page']) && $_GET['page'] == 'database') ? 'bg-blue-700' : ''; ?>">
                    <i class="fas fa-database w-5"></i>
                    <span class="ml-3">Databases</span>
                </a>
            </nav>
            <div class="absolute bottom-0 w-72 p-6">
                <div class="flex items-center">
                    <img src="https://www.gravatar.com/avatar/<?php echo md5($_SESSION['username']); ?>?d=mp" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <a href="logout.php" class="text-sm text-blue-300 hover:text-white transition-colors duration-200">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-8 py-4">
                    <div class="flex items-center">
                        <h2 class="text-2xl font-semibold text-gray-800">
                            <?php
                            $page = $_GET['page'] ?? 'dashboard';
                            echo ucfirst($page);
                            ?>
                        </h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>New Project
                        </button>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-8 overflow-y-auto" style="height: calc(100vh - 73px);">
                <?php
                $allowed_pages = ['dashboard', 'filemanager', 'terminal'];
                
                if (in_array($page, $allowed_pages)) {
                    include "components/$page.php";
                } else {
                    include "components/dashboard.php";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html> 