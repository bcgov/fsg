# Stage 1: Build Environment
FROM php:8.2-apache as builder

# Arguments and environment variables
ARG DEBIAN_FRONTEND=noninteractive
ARG TZ=America/Vancouver

ENV TZ=${TZ}

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and build tools
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    nano \
    netcat-traditional \
    curl \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    ca-certificates \
    gnupg \
    && pecl install zip redis \
    && docker-php-ext-enable zip redis \
    && docker-php-ext-install bcmath soap pcntl pdo pdo_pgsql pgsql intl opcache \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --prefer-dist --no-scripts --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Stage 2: Runtime Environment
FROM php:8.2-apache

# Arguments and environment variables
ARG TZ=America/Vancouver
ENV TZ=${TZ}

# Set working directory
WORKDIR /var/www/html

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    nano \
    netcat-traditional \
    curl \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    ca-certificates \
    gnupg \
    && pecl install zip redis \
    && docker-php-ext-enable zip redis \
    && docker-php-ext-install bcmath soap pcntl pdo pdo_pgsql pgsql intl opcache \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers remoteip \
    && echo 'RemoteIPHeader X-Forwarded-For' >> /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 10.0.0.0/8' >> /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 172.16.0.0/12' >> /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 192.168.0.0/16' >> /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 169.254.0.0/16' >> /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 127.0.0.0/8' >> /etc/apache2/conf-available/remoteip.conf \
    && a2enconf remoteip

# Copy the application from the builder stage
COPY --from=builder /var/www/html /var/www/html

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY entrypoint.sh /sbin/entrypoint.sh
RUN chmod +x /sbin/entrypoint.sh

# Expose the port the service runs on
EXPOSE 8080 8443 2525

# Set entrypoint
ENTRYPOINT ["/sbin/entrypoint.sh"]

# Start Apache
CMD ["apache2-foreground"]
