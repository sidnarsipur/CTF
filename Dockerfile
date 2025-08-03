# ubuntu:22.04
FROM ubuntu@sha256:59ccd419c0dc0edf9e3bff1a3b2b073ea15a2ce4bc45ce7c989278b225b09af3

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y curl gnupg apt-transport-https unzip
RUN apt-get install -y apache2
RUN apt-get install -y php libapache2-mod-php php-cli php-bcmath php-mbstring
RUN apt-get install -y python3 python3-pip mitmproxy
RUN apt-get install -y mariadb-server

RUN curl -1sLf "https://keys.openpgp.org/vks/v1/by-fingerprint/0A9AF2115F4687BD29803A206B73A36E6026DFCA" | gpg --dearmor | tee /usr/share/keyrings/com.rabbitmq.team.gpg > /dev/null
RUN curl -1sLf "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xf77f1eda57ebb1cc" | gpg --dearmor | tee /usr/share/keyrings/net.launchpad.ppa.rabbitmq.erlang.gpg > /dev/null
RUN curl -1sLf "https://packagecloud.io/rabbitmq/rabbitmq-server/gpgkey" | gpg --dearmor | tee /usr/share/keyrings/io.packagecloud.rabbitmq.gpg > /dev/null

RUN apt-get update
RUN apt-get install -y erlang-base erlang-asn1 erlang-crypto erlang-eldap erlang-ftp erlang-inets erlang-mnesia \
    erlang-os-mon erlang-parsetools erlang-public-key erlang-runtime-tools erlang-snmp erlang-ssl \
    erlang-syntax-tools erlang-tftp erlang-tools erlang-xmerl
RUN apt-get install -y rabbitmq-server --fix-missing
RUN rabbitmq-plugins enable rabbitmq_management

RUN pip install pika mysql-connector-python pandas
RUN pip install protobuf==3.20.*

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN composer require php-amqplib/php-amqplib --working-dir=/var/www/

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/Options Indexes FollowSymLinks/Options -Indexes/' /etc/apache2/apache2.conf

ENV APACHE_CONFDIR="/etc/apache2/"
ENV APACHE_ENVVARS="/etc/apache2/envvars"
RUN mkdir /run/mysqld
RUN rm -rf /var/www/html/*

COPY ./20-rabbitmq.conf /etc/rabbitmq/conf.d/20-rabbitmq.conf
COPY ./app /app
COPY ./html/ /var/www/html/

RUN ln -sf /proc/self/fd/1 /var/log/apache2/access.log && \
    ln -sf /proc/self/fd/1 /var/log/apache2/error.log

RUN chmod +x /app/start.sh
CMD /app/start.sh
