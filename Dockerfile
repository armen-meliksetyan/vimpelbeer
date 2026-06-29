FROM php:8.2-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite deflate expires headers \
    && printf '<Directory /var/www/html>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' \
       >> /etc/apache2/apache2.conf

COPY --chown=www-data:www-data . /var/www/html/

RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD php -r '$s=@fsockopen("127.0.0.1",80,$e,$m,2); if(!$s) exit(1); fwrite($s,"GET / HTTP/1.0\r\nHost: localhost\r\n\r\n"); $r=fgets($s); fclose($s); exit(strpos($r,"200")===false);'
