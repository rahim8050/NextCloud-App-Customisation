# Nextcloud Customisation

This repository contains the source code and customizations for a Nextcloud installation.

## Setup Instructions

1. # Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/NextCloud-App-Customisation.git
   cd NextCloud-App-Customisation
2. # Install dependencies (if applicable):
   ```bash
      composer install
3. # Configure Nextcloud:
   - Copy config/config.sample.php to config/config.php
   - Update configuration values to match your environment (database credentials, trusted domains, etc.)
  4. # Set correct file permissions:
     ```bash
        sudo chown -R www-data:www-data /var/www/html/nextcloud/
        sudo find /var/www/html/nextcloud/ -type d -exec chmod 750 {} \;
        sudo find /var/www/html/nextcloud/ -type f -exec chmod 640 {} \;
5. #Complete installation:
     - Access your Nextcloud instance via browser and follow the web installer. 
  ## Important Notes
  - Do not commit user data (data/ directory) or the real config.php with sensitive credentials.
  - Keep sensitive information out of version control.
  - Use environment variables or secrets management in production environments.
