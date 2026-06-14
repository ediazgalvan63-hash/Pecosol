FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar Apache para permitir .htaccess
RUN sed -ri -e 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf && \
    sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/sites-available/*.conf

# Crear configuración explícita de DocumentRoot
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/pecosol.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/pecosol.conf && \
    echo '    RewriteEngine On' >> /etc/apache2/conf-available/pecosol.conf && \
    echo '    RewriteCond %{REQUEST_FILENAME} !-f' >> /etc/apache2/conf-available/pecosol.conf && \
    echo '    RewriteCond %{REQUEST_FILENAME} !-d' >> /etc/apache2/conf-available/pecosol.conf && \
    echo '    RewriteRule ^(.*)$ index.php [QSA,L]' >> /etc/apache2/conf-available/pecosol.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/pecosol.conf && \
    a2enconf pecosol

EXPOSE 80

CMD ["/bin/bash", "-lc", "PORT_TO_USE=${PORT:-80}; sed -ri \"s/Listen 80/Listen ${PORT_TO_USE}/\" /etc/apache2/ports.conf; sed -ri \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT_TO_USE}>/\" /etc/apache2/sites-available/000-default.conf; a2dismod mpm_event >/dev/null 2>&1 || true; a2dismod mpm_worker >/dev/null 2>&1 || true; a2enmod mpm_prefork >/dev/null 2>&1 || true; apache2-foreground"]
