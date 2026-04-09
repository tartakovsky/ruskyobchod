FROM gitlab.jagu.cz:8081/infrastructure/nginx-unit:php7.4-4

ENV APP_TEMPLATE=wordpress
ARG WP_INSTALL_FILENAME="wordpress-5.8.2.zip"

RUN cd /tmp \
 && curl https://wordpress.org/${WP_INSTALL_FILENAME} > ${WP_INSTALL_FILENAME} \
 && unzip ${WP_INSTALL_FILENAME} \
 && mv wordpress/* /var/www/public \
 && rm -rf wordpress ${WP_INSTALL_FILENAME} \
 && chown -R web:web /var/www/public

COPY --chown=web:web wp-config.php /var/www/public/
