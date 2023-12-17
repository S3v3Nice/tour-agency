FROM php:8.1-fpm

ARG user=www-data
ARG uid=1000

# Install all necessary packets and php extensions
RUN \
    apt-get update && \
    apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip unzip \
      git && \
    pecl config-set php_ini "${PHP_INI_DIR}/php.ini" && \
    docker-php-ext-install  \
      pdo_mysql \
      bcmath \
      gd \
      zip && \
    pecl install  \
      apfd && \
    docker-php-ext-enable  \
      apfd && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin \

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www

USER $user