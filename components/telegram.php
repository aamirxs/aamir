<?php
if (!defined('ADMIN_USER')) exit;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_telegram'])) {
        $bot_token = $_POST['bot_token'];
        $allowed_users = array_filter(explode(',', $_POST['allowed_users']));
        
        // Update telegram_config.php
        $config_content = "<?php\n";
        $config_content .= "define('TELEGRAM_BOT_TOKEN', '$bot_token');\n";
        $config_content .= "define('TELEGRAM_ALLOWED_USERS', [" . implode(',', $allowed_users) . "]);\n\n";
        $config_content .= file_get_contents('includes/telegram_config.php', false, null, strpos(file_get_contents('includes/telegram_config.php'), 'function'));
        
        file_put_contents('includes/telegram_config.php', $config_content);
        
        // Set webhook
        $webhook_url = "https://" . $_SERVER['HTTP_HOST'] . "/telegram_webhook.php";
        $telegram_api = "https://api.telegram.org/bot$bot_token/setWebhook?url=$webhook_url";
        file_get_contents($telegram_api);
    }
}

// Get current settings
$current_token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
$current_users = defined('TELEGRAM_ALLOWED_USERS') ? implode(',', TELEGRAM_ALLOWED_USERS) : '';
?>

<div class="grid grid-cols-1 gap-6">
    <!-- Telegram Settings -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">Telegram Bot Settings</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Bot Token</label>
                <input type="text" name="bot_token" value="<?php echo htmlspecialchars($current_token); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <p class="mt-1 text-sm text-gray-500">Get this from @BotFather on Telegram</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Allowed User IDs</label>
                <input type="text" name="allowed_users" value="<?php echo htmlspecialchars($current_users); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <p class="mt-1 text-sm text-gray-500">Comma-separated Telegram user IDs</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium mb-2">Available Bot Commands:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                    <li>/start - Show available commands</li>
                    <li>/status - Server status</li>
                    <li>/backup - Create backup</li>
                    <li>/list_backups - List backups</li>
                    <li>/disk_usage - Show disk usage</li>
                    <li>/memory_usage - Show memory usage</li>
                    <li>/restart_services - Restart web services</li>
                </ul>
            </div>

            <button type="submit" name="update_telegram" class="btn-primary">
                Update Telegram Settings
            </button>
        </form>
    </div>

    <!-- Setup Instructions -->
    <div class="dashboard-card p-6">
        <h3 class="text-xl font-bold mb-4">Setup Instructions</h3>
        <ol class="list-decimal list-inside space-y-2">
            <li>Go to @BotFather on Telegram</li>
            <li>Send /newbot and follow instructions to create a new bot</li>
            <li>Copy the bot token and paste it above</li>
            <li>Start a conversation with your bot</li>
            <li>Get your Telegram user ID from @userinfobot</li>
            <li>Add your user ID to the allowed users list</li>
            <li>Save the settings</li>
        </ol>
    </div>
</div> 