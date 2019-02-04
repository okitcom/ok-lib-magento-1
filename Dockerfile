FROM alexcheng/magento:1.9.3.8

ENV INSTALL_DIR /var/www/html

COPY --chown=www-data:www-data SetupConfig.php /var/www/html/
COPY --chown=www-data:www-data .htaccess /var/www/html/
COPY --chown=www-data:www-data app/ /var/www/html/app/
COPY --chown=www-data:www-data skin/ /var/www/html/skin/

COPY entrypoint_install.sh /sbin/entrypoint_install.sh
RUN chmod +x /sbin/entrypoint_install.sh

ENV TZ=Europe/Amsterdam
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR $INSTALL_DIR

ENTRYPOINT ["/sbin/entrypoint_install.sh"]
CMD ["/sbin/my_init"]
