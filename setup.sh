#!/bin/bash

# Colors
GREEN='\033[0;32m'
NC='\033[0m'

echo "Starting Web Panel Installation..."

# Check Ubuntu version
version=$(lsb_release -rs)
if [[ "$version" != "22.04" && "$version" != "24.04" ]]; then
    echo "This script requires Ubuntu 22.04 or 24.04"
    exit 1
fi

# Install required packages
apt-get update
apt-get install -y apache2 php php-mysql php-zip php-xml php-curl mysql-server certbot python3-certbot-apache

# Generate random credentials
ADMIN_PASSWORD=$(openssl rand -base64 12)
DB_PASSWORD=$(openssl rand -base64 12)

# Configure MySQL
mysql -e "CREATE DATABASE webpanel;"
mysql -e "CREATE USER 'webpanel'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON webpanel.* TO 'webpanel'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Clone or copy files to web directory
mkdir -p /var/www/webpanel
cp -r * /var/www/webpanel/

# Set permissions
chown -R www-data:www-data /var/www/webpanel
chmod -R 755 /var/www/webpanel

# Create Apache virtual host
cat > /etc/apache2/sites-available/webpanel.conf << EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/webpanel
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Enable the site
a2ensite webpanel.conf
systemctl restart apache2

# Save credentials to a file
echo "Installation completed!"
echo -e "${GREEN}Panel URL: http://$(hostname -I | cut -d' ' -f1)${NC}"
echo -e "${GREEN}Admin Username: admin${NC}"
echo -e "${GREEN}Admin Password: $ADMIN_PASSWORD${NC}"

# Save credentials to config file
cat > /var/www/webpanel/includes/config.php << EOF
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'webpanel');
define('DB_PASS', '$DB_PASSWORD');
define('DB_NAME', 'webpanel');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '$ADMIN_PASSWORD');
?>
EOF

# Get domain name
read -p "Enter your domain name: " domain_name

# Set up SSL
certbot --apache -d $domain_name --non-interactive --agree-tos --email admin@$domain_name

# Create telegram config file
cat > /var/www/webpanel/includes/telegram_config.php << EOF
<?php
define('TELEGRAM_BOT_TOKEN', '');
define('TELEGRAM_ALLOWED_USERS', []);

function sendTelegramMessage(\$chat_id, \$message) {
    \$url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    \$data = [
        'chat_id' => \$chat_id,
        'text' => \$message,
        'parse_mode' => 'HTML'
    ];

    \$options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query(\$data)
        ]
    ];

    \$context = stream_context_create(\$options);
    return file_get_contents(\$url, false, \$context);
}
EOF