FROM ubuntu:noble

ARG DEBIAN_FRONTEND=noninteractive

# Environment variables
ENV APP_ENV='prod'
ENV PUID='1001'
ENV PGID='1001'
ENV USER='koillection'
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY ./ /var/www/koillection


# Install some basics dependencies
RUN apt-get update && \
    apt-get install -y curl wget lsb-release software-properties-common gnupg2 && \
# Add User and Group
    addgroup --gid "$PGID" "$USER" && \
    adduser --gecos '' --no-create-home --disabled-password --uid "$PUID" --gid "$PGID" "$USER" && \
# PHP
    add-apt-repository ppa:ondrej/php && \
# Nodejs
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    NODE_MAJOR=21 && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
# Install packages
    apt-get update && \
    apt-get install -y \
    ca-certificates \
    apt-transport-https \
    git \
    unzip \
    nginx-light \
    openssl \
    php8.3 \
    php8.3-apcu \
    php8.3-curl \
    php8.3-pgsql \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-gd \
    php8.3-xml \
    php8.3-zip \
    php8.3-fpm \
    php8.3-intl \
    nodejs && \
#Install composer dependencies
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/koillection && \
    composer install --classmap-authoritative && \
    composer clearcache && \
# Dump translation files for javascript
    cd /var/www/koillection/ && \
    php bin/console app:translations:dump && \
# Install javascript dependencies and build assets \
    corepack enable && \
    cd /var/www/koillection/assets && \
    yarn --version && \
    yarn install && \
    yarn build && \
# Clean up
    yarn cache clean --all && \
    rm -rf /var/www/koillection/assets/.yarn/cache && \
    rm -rf /var/www/koillection/assets/.yarn/install-state.gz && \
    rm -rf /var/www/koillection/assets/node_modules && \
    apt-get purge -y wget lsb-release software-properties-common git nodejs apt-transport-https ca-certificates gnupg2 unzip && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/local/bin/composer && \
# Set permissions \
    sed -i "s/user = www-data/user = $USER/g" /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i "s/group = www-data/group = $USER/g" /etc/php/8.3/fpm/pool.d/www.conf && \
    chown -R "$USER":"$USER" /var/www/koillection && \
    chmod +x /var/www/koillection/docker/entrypoint.sh && \
# Add nginx and PHP config files
    cp /var/www/koillection/docker/default.conf /etc/nginx/nginx.conf && \
    cp /var/www/koillection/docker/php.ini /etc/php/8.3/fpm/conf.d/php.ini

EXPOSE 80

VOLUME /uploads

WORKDIR /var/www/koillection

HEALTHCHECK CMD curl --fail http://localhost:80/ || exit 1

ENTRYPOINT ["sh", "/var/www/koillection/docker/entrypoint.sh" ]

CMD [ "nginx" ]
