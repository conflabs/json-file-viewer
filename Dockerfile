FROM php:8.2-apache
LABEL maintainer="Bobby Hines <bobby@conflabs.com>"
LABEL image="conflabs/php:8.2-apache"

# Set root directory for apache2 web server
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV APACHE_LOG_DIR /var/www/html/storage/logs

# Update repos and install system/security updates
RUN apt-get update && apt-get upgrade -y

# Install required utility programs
RUN apt-get update && apt-get install -y --no-install-recommends \
    apt-utils \
    build-essential \
    cron \
    curl \
    git \
    nano \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Install composer and put binary into $PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set recommended PHP.ini settings (see https://secure.php.net/manual/en/opcache.installation.php)
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'upload_max_filesize=128M'; \
    echo 'post_max_size=128M'; \
    echo 'expose_php=off'; \
    } > /usr/local/etc/php/conf.d/php-recommended.ini

# Install, enable, and configure zip extension
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip4 \
    libzip-dev \
    zlib1g-dev \
    unzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Search and Replace baked in default configuration files with custom root directories
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Pretty URLs
RUN ["a2enmod","rewrite"]

EXPOSE 80

WORKDIR /var/www/html

VOLUME /var/www/html