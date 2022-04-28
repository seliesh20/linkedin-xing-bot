FROM php:8.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www


RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
      curl \
	    lsof \
	    cron \
      zip \
	    unzip \
	    libzip-dev \
	    libnss3-dev \
	    libxcb-xinput0 \
      libmemcached-dev \
      libz-dev \
      libpq-dev \
      libjpeg-dev \
      libpng-dev \
      libfreetype6-dev \
      libssl-dev \
      libwebp-dev \
      libmcrypt-dev \
      libonig-dev \
      fonts-liberation \
	    libasound2 \
	    libatk-bridge2.0-0 \
	    libatk1.0-0 \
	    libatspi2.0-0 \
	    libcairo2 \
	    libcups2 \
	    libdbus-1-3 \
	    libdrm2 \
 	    libexpat1 \
	    libgbm1 \
	    libgtk-3-0 \
	    libpango-1.0-0 \
	    libx11-6 \
	    libxcomposite1 \
      libxdamage1 \
	    libxext6 \
	    libxfixes3 \
	    libxkbcommon0 \
	    libxrandr2 \
	    libxshmfence1 \
	    xdg-utils \
	    libu2f-udev \
	    libvulkan1 \
	    wget; \
    rm -rf /var/lib/apt/lists/*


RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb \
	&& apt install ./google-chrome-stable_current_amd64.deb

RUN set -eux; \
    # Install the PHP pdo_mysql extention
    docker-php-ext-install pdo_mysql; \
    # Install the PHP pdo_pgsql extention
    docker-php-ext-install pdo_pgsql; \
    docker-php-ext-install zip; \
    # Install the PHP gd library
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-freetype; \
    docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

CMD composer install; php-fpm;

EXPOSE 9000 
