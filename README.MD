# FileMaker API

A middleware for FileMaker Server 16+ Data API with a javascript library for the browser and node.js

# Table of Contents

1. [Requirements](#requirements)
    - [FileMaker Server 16+](#filemaker-server-16)
    - [Composer](#composer)
    - [PHP 8.1 or higher](#php-81-or-higher)
    - [Apache or Nginx](#apache-or-nginx)
2. [Installation](#installation)
    - [Clone Repository](#clone-repository)
    - [Install Composer Dependencies](#install-composer-dependencies)
    - [Configure FileMaker Server](#configure-filemaker-server)
3. [Apache Configuration](#apache-configuration)
    - [Required modules](#required-modules)
    - [VirtualHost configuration](#virtualhost-configuration)
    - [.htaccess configuration](#htaccess-configuration)
    - [Reloading apache](#reloading-apache)
4. [NGINX Configuration](#nginx-configuration)
    - [Server block configuration](#server-block-configuration)
    - [Reloading nginx](#reloading-nginx)
5. Libraries
    - [PHP Library](inc/README.md)
    - [Javascript Library](js/README.md)

# Requirements

- [FileMaker Server 16+](https://www.filemaker.com/products/filemaker-server/)
- [Composer](https://getcomposer.org/)
- [PHP 8.1 or higher](https://www.php.net/downloads.php)
- [Apache](https://httpd.apache.org/) or [Nginx](https://www.nginx.com/)

# Installation

### Clone Repository

```bash
git clone https://github.com/mardens-inc/filemaker-api.git --recurse-submodules
```

### Install Composer Dependencies

```bash
composer install
```

### Configure FileMaker Server

- Create or update a user account with the [fmrest](https://help.claris.com/en/data-api-guide/content/enable-access.html?Highlight=fmrest) extended privilege
- Make sure that the [Data API](https://help.claris.com/en/data-api-guide/content/prepare-databases-for-access.html?Highlight=Data%20API) is enabled
- Make sure that the network firewall allows HTTPS traffic from the web server to the FileMaker Server

# Apache Configuration

You will need to enable the following Apache modules:

- mod_rewrite
- mod_proxy
- mod_proxy_fcgi
- actions_module

```apache
<VirtualHost *:80>
    ServerName filemaker-api.local
    DocumentRoot "/path/to/cloned-project"
    <Directory "/path/to/cloned-project">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Configure your `.htaccess` file to redirect all requests to `index.php`, this should be placed next to your index.php file in the root of your project.
Like this:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

Then reload apache

```bash
sudo service apache2 reload
```

For more information, take a look at the SLIM Framework [documentation](https://www.slimframework.com/docs/v4/start/web-servers.html#apache-configuration)

# NGINX Configuration

Add this to your server block inside the `http` block in your `nginx.conf` file:

```nginx
server {
    // ...
    location / {
        try_files $uri /index.php$is_args$args;
    }
    // ...
}
```

Then reload nginx:

```bash
sudo nginx -s reload
```

For more information, take a look at the SLIM Framework [documentation](https://www.slimframework.com/docs/v4/start/web-servers.html#nginx-configuration)