FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Laravel dependencies
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --working-dir=/var/www/html

# Laravel / Nginx configuration
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV REAL_IP_HEADER=1

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

CMD ["/start.sh"]