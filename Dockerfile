FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --working-dir=/var/www/html

ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV REAL_IP_HEADER=1

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

COPY start-render.sh /usr/local/bin/start-render.sh
RUN chmod +x /usr/local/bin/start-render.sh

CMD ["/usr/local/bin/start-render.sh"]