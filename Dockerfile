# Set master image
FROM php:7.4.29-fpm

# Set working directory
WORKDIR /app
COPY . .

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    vim \
    telnet iputils-ping \
    gnupg

# Copy the PHP extension installer script from the mlocati/php-extension-installer image
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install the sockets extension
RUN install-php-extensions sockets

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install MSSQL ODBC driver
RUN apt-get update && apt-get install -y unixodbc-dev
RUN pecl install sqlsrv-5.10.0 pdo_sqlsrv-5.10.0

# extension=sqlsrv.so
# extension=pdo_sqlsrv.so
# Enable the extensions in php.ini
RUN echo "extension=sqlsrv.so" >> /usr/local/etc/php/conf.d/20-pdo_sqlsrv.ini && \
    echo "extension=pdo_sqlsrv.so" >> /usr/local/etc/php/conf.d/20-pdo_sqlsrv.ini

RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl https://packages.microsoft.com/config/ubuntu/18.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update
RUN ACCEPT_EULA=Y apt-get install -y --allow-unauthenticated msodbcsql17
RUN ACCEPT_EULA=Y apt-get install -y --allow-unauthenticated mssql-tools
RUN echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bash_profile
RUN echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# Install PHP Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Install dependencies
RUN rm -rf vendor composer.lock
# RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction
# RUN composer update
# RUN composer install
# RUN composer install --no-cache --ignore-platform-reqs
RUN composer install --ignore-platform-req=ext-zip

# COPY .env.docker .env

RUN php artisan cache:clear && \
    php artisan route:cache && \
    php artisan config:cache && \
    php artisan view:clear && \
    php artisan optimize && \
    php artisan config:clear && \
    php artisan route:clear && \
    composer dump-autoload
#     # php artisan key:generate  && \
#     # php artisan passport:install  && \
#     # php artisan passport:keys   && \
#     # chmod 775 -R /var/www/html/bootstrap/ && \
#     # chmod 775 -R /var/www/html/public/ && \
#     # chmod 775 -R /var/www/html/storage/ && \
#     chown -R www-data:www-data /var/www/html

RUN rm -rf bootstrap/cache/*.php
# RUN php artisan key:generate
# RUN php artisan passport:install --force
# RUN php artisan passport:keys

# RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

# RUN php artisan cache:clear

# Expose port 9000 and start php-fpm server. is used to inform Docker that the container will listen on a specific network port at runtime. By default, the ports exposed using the EXPOSE instruction are internal to the Docker container network and can only be accessed by other containers within the same Docker network.
EXPOSE 9000
CMD ["php-fpm"]
