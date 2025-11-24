FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction

EXPOSE 8000

CMD [ "php", "-S", "0.0.0.0:8000", "-t", "public" ]