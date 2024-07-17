# Use the official PHP image as the base image
FROM php:8.2-apache

# Arguments and Environment Variables
ARG DEBIAN_VERSION=20.04
ARG APACHE_OPENIDC_VERSION=2.4.10
ARG TZ=America/Vancouver
ARG CA_HOSTS_LIST
ARG USER_ID
ARG DEBIAN_FRONTEND=noninteractive
ARG DEVENV=prod

ENV USER_NAME=${USER_ID}
ENV USER_HOME=/var/www/html
ENV APACHE_REMOTE_IP_HEADER=X-Forwarded-For
ENV APACHE_REMOTE_IP_TRUSTED_PROXY="10.0.0.0/8 172.16.0.0/12 192.168.0.0/16 10.97.6.0/16 10.97.6.1"
ENV APACHE_REMOTE_IP_INTERNAL_PROXY="10.0.0.0/8 172.16.0.0/12 192.168.0.0/16 10.97.6.0/16 10.97.6.1"
ENV TZ=${TZ}
ENV APACHE_SERVER_NAME=__default__

WORKDIR /

# Install System Dependencies and PHP Extensions
RUN apt-get update --fix-missing && \
    apt-get install -y --no-install-recommends apt-utils && \
    apt-get install -y --no-install-recommends \
    libzip-dev libxml2-dev zip unzip zlib1g-dev g++ libicu-dev libpq-dev git nano netcat-traditional curl apache2 dialog locate libcurl4 libcurl3-dev psmisc \
    libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev libaio-dev libonig-dev ca-certificates gnupg && \
    pecl install redis apcu && \
    docker-php-ext-enable redis apcu && \
    docker-php-ext-install pdo_mysql zip bcmath soap pcntl intl opcache pgsql pdo_pgsql curl gd && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-configure zip && \
    docker-php-source delete && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache Configuration
RUN a2enmod rewrite headers remoteip auth_basic authn_file authz_user autoindex deflate filter reqtimeout setenvif lbmethod_byrequests mpm_prefork && \
    a2dismod mpm_event mpm_worker && \
    sed -i 's/%h/%a/g' /etc/apache2/apache2.conf && \
    { \
        echo 'RemoteIPHeader X-Forwarded-For'; \
        echo 'RemoteIPInternalProxy 10.0.0.0/8'; \
        echo 'RemoteIPInternalProxy 172.16.0.0/12'; \
        echo 'RemoteIPInternalProxy 192.168.0.0/16'; \
        echo 'RemoteIPInternalProxy 169.254.0.0/16'; \
        echo 'RemoteIPInternalProxy 127.0.0.0/8'; \
    } | tee "$APACHE_CONFDIR/conf-available/remoteip.conf" && \
    a2enconf remoteip && \
    sed -i -e 's!expose_php = On!expose_php = Off!g' $PHP_INI_DIR/php.ini-production && \
    sed -i -e 's!ServerTokens OS!ServerTokens Prod!g' /etc/apache2/conf-available/security.conf && \
    sed -i -e 's!ServerSignature On!ServerSignature Off!g' /etc/apache2/conf-available/security.conf && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && apt-get install -y nodejs && \
    apt-get autoclean && apt-get autoremove && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set up Apache and PHP environment
RUN mkdir -p /var/log/php && \
    printf 'error_log=/var/log/php/error.log\nlog_errors=1\nerror_reporting=E_ALL\nmemory_limit=450M\n' > /usr/local/etc/php/conf.d/custom.ini && \
    mkdir -p /etc/apache2/sites-enabled && \
    mkdir -p /var/lock/apache2 /var/run/apache2 && \
    chgrp -R 0 /etc/apache2/mods-* /etc/apache2/sites-* /run /var/lib/apache2 /var/run/apache2 /var/lock/apache2 /var/log/apache2 && \
    chmod -R g=u /etc/passwd /etc/apache2/mods-* /etc/apache2/sites-* /run /var/lib/apache2 /var/run/apache2 /var/lock/apache2 /var/log/apache2

# Copy necessary files
COPY openshift/apache-oc/image-files/ /
COPY openshift/apache-oc/image-files/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY entrypoint.sh /sbin/entrypoint.sh
COPY / /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions
RUN mkdir -p storage bootstrap/cache && \
    chmod -R ug+rwx storage bootstrap/cache && \
    chown -R ${USER_ID}:root /var/www/html && chmod -R ug+rw /var/www/html && \
    chmod 764 /var/www/html/artisan && \
    chmod 766 /var/www/html/public/mix-manifest.json && \
    mkdir -p /.npm /.npm/_cache /.config/psysh /.composer && \
    chown -R ${USER_ID}:root /.npm /.config /.composer && \
    chmod -R 755 /.npm /.config /.composer && \
    echo "<?php return ['runtimeDir' => '/tmp'];" > /.config/psysh/config.php && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    chmod +x /sbin/entrypoint.sh

# User permissions
USER ${USER_ID}

# Install Composer and npm dependencies
RUN composer install && npm install --prefix /var/www/html/ && npm run --prefix /var/www/html/ ${DEVENV}

# Expose ports
EXPOSE 8080 8443 2525

# Entry point and command
ENTRYPOINT ["/sbin/entrypoint.sh"]
CMD ["apache2-foreground"]
