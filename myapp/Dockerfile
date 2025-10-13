# -----------------------
# Base image: PHP 8.2 FPM
# -----------------------
FROM php:8.3-fpm
# -----------------------
# Set working directory
# -----------------------
WORKDIR /var/www

# -----------------------
# Install system dependencies
# -----------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    python3 \
    python3-pip \
    procps && \
    \
    # Install Node.js (v20)
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    \
    # Configure and install PHP extensions
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd zip && \
    \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# -----------------------
# Install Composer
# -----------------------
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# -----------------------
# Copy application files
# -----------------------
COPY . /var/www

# -----------------------
# Copy .env if it doesn't exist
# -----------------------
# Copy .env if missing and generate key
RUN if [ ! -f .env ]; then cp .env.example .env; fi && php artisan key:generate

# -----------------------
# Install PHP dependencies
# -----------------------
RUN composer install --no-interaction --optimize-autoloader

# -----------------------
# Install Laravel Livewire
# -----------------------
RUN composer require livewire/livewire --no-interaction --optimize-autoloader

# -----------------------
# Install Node.js dependencies
# -----------------------
RUN npm install && \
    npm run build
# -----------------------
# Install Python packages
# -----------------------
RUN pip3 install --break-system-packages numpy pandas openpyxl ortools pygame

# -----------------------
# Set permissions
# -----------------------
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# -----------------------
# Expose Laravel dev server port
# -----------------------
EXPOSE 8000
EXPOSE 5173

# -----------------------
# Start PHP dev server and Vite using concurrently
# -----------------------
CMD npx concurrently \
    "php artisan serve --host=0.0.0.0 --port=8000" \
    "npm run dev" \
    --names "laravel,vite" \
    --kill-others





