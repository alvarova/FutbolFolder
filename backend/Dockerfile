FROM php:8.2-apache

WORKDIR /var/www/html

COPY backend/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
