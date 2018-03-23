FROM alexcheng/magento:1.9.3.8

ENV INSTALL_DIR /var/www/html

COPY . /var/www/oklib
COPY entrypoint_install.sh /sbin/entrypoint_install.sh
RUN chmod +x /sbin/entrypoint_install.sh
RUN apt-get install rsync -y && rsync -a /var/www/oklib/ /var/www/html/

WORKDIR $INSTALL_DIR

CMD ["/sbin/entrypoint_install.sh"]