# 1) Base image with PHP + Nginx (common Laravel setup)
FROM richarvey/nginx-php-fpm:latest

# 2) Install system deps, Python 3 and pip
USER root

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        python3 \
        python3-pip \
        python3-venv \
    && rm -rf /var/lib/apt/lists/*

# 3) Create a Python venv and install your algorithm deps
WORKDIR /var/www/html

# Copy only requirements.txt first (for better Docker caching, if you use it)
# If you don't have this file yet, create it with: pandas, numpy, openpyxl
COPY requirements.txt /var/www/html/requirements.txt

RUN python3 -m venv venv && \
    ./venv/bin/pip install --upgrade pip && \
    ./venv/bin/pip install -r requirements.txt

# 4) Copy the rest of the app code
COPY . /var/www/html

# 5) Ensure proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html && \
    find storage -type d -exec chmod 775 {} \; && \
    find bootstrap/cache -type d -exec chmod 775 {} \;

# 6) Default CMD from base image will run PHP-FPM + Nginx
# (richarvey/nginx-php-fpm handles this already)
