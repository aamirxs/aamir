<?php
require_once 'includes/config.php';
require_once 'includes/telegram_config.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

$message = $update['message'] ?? $update['callback_query']['message'] ?? null;
$callback_query = $update['callback_query'] ?? null;
$chat_id = $message['chat']['id'];
$user_id = $message['from']['id'] ?? $update['callback_query']['from']['id'];
$text = $message['text'] ?? $callback_query['data'] ?? '';

// Check if user is allowed
if (!in_array($user_id, TELEGRAM_ALLOWED_USERS)) {
    sendTelegramMessage($chat_id, "⛔ You are not authorized to use this bot.");
    exit;
}

// Handle callback queries
if ($callback_query) {
    $data = $callback_query['data'];
    if (strpos($data, 'delete_backup_') === 0) {
        $backup_name = substr($data, 14);
        $backup_path = '/var/backups/webpanel/' . $backup_name;
        if (unlink($backup_path)) {
            answerCallbackQuery($callback_query['id'], "Backup deleted successfully!");
            editMessageText($chat_id, $callback_query['message']['message_id'], "✅ Backup deleted: $backup_name");
        }
        exit;
    }
    if (strpos($data, 'service_') === 0) {
        $service = substr($data, 8);
        shell_exec("systemctl restart $service");
        answerCallbackQuery($callback_query['id'], "Service restarted successfully!");
        exit;
    }
}

// Command handler
switch ($text) {
    case '/start':
        $response = "🚀 *Welcome to Web Panel Bot!*\n\n";
        $response .= "*Available commands:*\n\n";
        $response .= "📊 *Monitoring*\n";
        $response .= "/status - Server status\n";
        $response .= "/processes - Show top processes\n";
        $response .= "/disk_usage - Show disk usage\n";
        $response .= "/memory_usage - Show memory usage\n";
        $response .= "/logs - View recent logs\n\n";
        
        $response .= "🔧 *Management*\n";
        $response .= "/services - Manage services\n";
        $response .= "/backup - Create backup\n";
        $response .= "/list_backups - List backups\n";
        $response .= "/users - List system users\n\n";
        
        $response .= "🔒 *Security*\n";
        $response .= "/ssl_status - SSL certificate status\n";
        $response .= "/firewall - Show firewall status\n";
        $response .= "/banned_ips - Show banned IPs\n";
        
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/processes':
        $output = shell_exec('ps aux --sort=-%cpu | head -n 6');
        $response = "💻 *Top Processes:*\n\n`$output`";
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/services':
        $services = ['apache2', 'mysql', 'php-fpm', 'nginx', 'redis'];
        $keyboard = [];
        foreach ($services as $service) {
            $status = shell_exec("systemctl is-active $service");
            $status_emoji = trim($status) === 'active' ? '🟢' : '🔴';
            $keyboard[] = [
                ['text' => "$status_emoji $service", 'callback_data' => "service_$service"]
            ];
        }
        sendTelegramMessageWithKeyboard($chat_id, "🔧 *Select service to restart:*", $keyboard);
        break;

    case '/logs':
        $logs = [
            'Apache Error' => 'tail -n 5 /var/log/apache2/error.log',
            'System' => 'tail -n 5 /var/log/syslog',
            'Auth' => 'tail -n 5 /var/log/auth.log'
        ];
        
        $response = "📜 *Recent Logs:*\n\n";
        foreach ($logs as $name => $command) {
            $output = shell_exec($command);
            $response .= "*$name Log:*\n`$output`\n\n";
        }
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/ssl_status':
        $domain = $_SERVER['HTTP_HOST'];
        $ssl_info = shell_exec("openssl s_client -connect $domain:443 -servername $domain </dev/null 2>/dev/null | openssl x509 -noout -dates");
        $response = "🔒 *SSL Certificate Status:*\n\n`$ssl_info`";
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/firewall':
        $rules = shell_exec('ufw status');
        $response = "🛡️ *Firewall Status:*\n\n`$rules`";
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/banned_ips':
        $banned = shell_exec('fail2ban-client status sshd');
        $response = "🚫 *Banned IPs:*\n\n`$banned`";
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    case '/list_backups':
        $backups = glob('/var/backups/webpanel/*.tar.gz');
        $response = "📦 *Available Backups:*\n\n";
        $keyboard = [];
        
        foreach ($backups as $backup) {
            $name = basename($backup);
            $size = round(filesize($backup) / 1024 / 1024, 2);
            $date = date('Y-m-d H:i:s', filemtime($backup));
            $response .= "`$name` ($size MB) - $date\n";
            $keyboard[] = [
                ['text' => "🗑️ Delete $name", 'callback_data' => "delete_backup_$name"]
            ];
        }
        
        sendTelegramMessageWithKeyboard($chat_id, $response, $keyboard);
        break;

    case '/users':
        $users = shell_exec("cut -d: -f1,3 /etc/passwd | egrep ':[0-9]{4}$' | cut -d: -f1");
        $response = "👥 *System Users:*\n\n`$users`";
        sendTelegramMessageMarkdown($chat_id, $response);
        break;

    default:
        if (strpos($text, '/exec ') === 0) {
            if (!in_array($user_id, TELEGRAM_ADMIN_USERS)) {
                $response = "⛔ You are not authorized to use this command.";
            } else {
                $command = substr($text, 6);
                $output = shell_exec($command);
                $response = "💻 *Command Output:*\n\n`$output`";
                sendTelegramMessageMarkdown($chat_id, $response);
                break;
            }
        }
        $response = "❌ Unknown command. Use /start to see available commands.";
}

if (!empty($response)) {
    sendTelegramMessage($chat_id, $response);
}

// Helper functions
function sendTelegramMessageMarkdown($chat_id, $message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

function sendTelegramMessageWithKeyboard($chat_id, $message, $keyboard) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => $keyboard
        ])
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

function answerCallbackQuery($callback_query_id, $text) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/answerCallbackQuery";
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => true
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

function editMessageText($chat_id, $message_id, $text) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/editMessageText";
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
} 