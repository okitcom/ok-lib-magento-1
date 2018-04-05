FROM alexcheng/magento:1.9.3.8

ENV INSTALL_DIR /var/www/html

COPY . /var/www/oklib
COPY entrypoint_install.sh /sbin/entrypoint_install.sh
RUN chmod +x /sbin/entrypoint_install.sh
RUN apt-get install rsync -y && rsync -a /var/www/oklib/app/ /var/www/html/app/ && rsync -a /var/www/oklib/skin/ /var/www/html/skin/
ENV TZ=Europe/Amsterdam
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN add-apt-repository -y ppa:certbot/certbot && apt-get update && apt-get install -y python-certbot-apache

WORKDIR $INSTALL_DIR

CMD ["/sbin/entrypoint_install.sh"]