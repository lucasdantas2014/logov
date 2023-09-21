FROM rockylinux:8

# Instalação do PHP e suas dependências.
RUN dnf -y update && \
    dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm && \
    dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm && \
    dnf -y install yum-utils && \
    dnf -y install nginx && \
    dnf -y install supervisor && \
    dnf module reset php && \
    dnf -y module install php:remi-8.0 && \
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
