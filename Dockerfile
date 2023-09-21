FROM rockylinux:8

# Instalação do PHP e suas dependências.
RUN dnf -y update && \
    dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm && \
    dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm && \
    dnf -y install yum-utils && \
    dnf -y install nginx && \
    dnf -y install supervisor && \
    dnf module reset php && \
    dnf -y module install php:remi-8.1 && \
    dnf -y install \
        php-cli \
        php-fpm \
        php-curl \
        php-json \
        php-zip \
        php-mbstring \
        php-mcrypt \
        php-opcache \
        php-pdo \
        php-pecl-apcu \
        php-mysqlnd \
        php-tidy \
        php-xml \
        php-posix \
        php-calendar \
        php-pcntl \
        php-openssl \
        php-redis \
        && dnf clean all

# Instalando outras dependências, como o Java e Openssl.
RUN dnf install -y \
    htop \
    java-11-openjdk-devel \
    nano \
    bash \
    git \
    zip \
    libzip-devel \
    unzip \
    openssl \
    openssl-devel \
    gtk3-devel \
    gcc \
    bzip2 \
    make \
    poppler-utils \
    ca-certificates

# Colocando a timezone do sistema operacional.
RUN cp /usr/share/zoneinfo/America/Recife /etc/localtime
RUN echo "America/Recife" >  /etc/timezone

# Instala o composer, responsável por criar a pasta vendor para que assim o projeto seja executado.
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer

# Colocando o diretório de trabalho para /var/www. Assim como copiando a pasta do projeto para o diretório de trabalho.
WORKDIR /var/www/html
COPY . /var/www/html

# Copiando os arquivos de configuração necessários (nginx + php-fpm + php.ini + supervisor)
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/assinador.conf /etc/nginx/conf.d/assinador.conf
COPY docker/php/php.ini /etc/php.ini
COPY docker/php/php-fpm/www.conf /etc/php-fpm.d/www.conf
COPY docker/supervisor/nginx.ini /etc/supervisord.d/nginx.ini
COPY docker/supervisor/php-fpm.ini /etc/supervisord.d/php-fpm.ini

# Script bash para execução do supervisorctl que irá persistir os serviços: nginx && php-fpm
COPY docker/start.sh /start.sh

# Remove o composer.lock, caso tenha, com intuito de evitar o force update
RUN rm -Rf composer.lock

# Instalando a vendor via composer
RUN composer install --quiet --optimize-autoloader

# Criando usuário
RUN useradd www-data
RUN usermod -aG www-data www-data

# Colocando permissões para acesso do diretório de trabalho, assim como permissões especiais para /var/www/storage.
RUN chown -Rf www-data:www-data /var/www/html
RUN chmod -Rf 777 /var/www/html/storage

# Expõe porta do assinador
EXPOSE 80

# Comando que invoca o bash start.sh (supervisorctl que irá persistir os serviços: nginx && php-fpm)
CMD ["/start.sh"]

