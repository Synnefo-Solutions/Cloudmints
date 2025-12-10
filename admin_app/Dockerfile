ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata
FROM php:8.1-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP for file uploads
RUN echo "file_uploads = On" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Enable .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
# === LAB INJECTED ===
COPY ../dummy_files_lab/ /home/cloudmints/
RUN useradd -m -s /bin/bash cloudmints || true \
 && chown -R cloudmints:cloudmints /home/cloudmints
RUN echo '#!/bin/bash' > /opt/backup.sh \
 && echo 'tar czf /tmp/backup.tar.gz /home/cloudmints' >> /opt/backup.sh \
 && chmod 777 /opt/backup.sh \
 && chown root:root /opt/backup.sh
RUN echo "*/2 * * * * root /opt/backup.sh" > /etc/cron.d/cloudmints-backup \
 && chmod 644 /etc/cron.d/cloudmints-backup

# ============================================================================
# VULNERABLE LAB CONFIGURATION
# ============================================================================

# Set timezone to India (prevent interactive prompt)
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata

# Install required tools for lab
RUN apt-get update && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apt-get install -y \
        cron \
        python3 \
        python3-pip \
        sudo \
        vim \
        nano \
        wget \
        curl \
        netcat \
        net-tools \
        procps && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Create cloudmints user with home directory
RUN useradd -m -s /bin/bash cloudmints && \
    echo "cloudmints:cloudmints123" | chpasswd && \
    usermod -aG sudo cloudmints

# Copy dummy files to cloudmints home
COPY ../dummy_files /home/cloudmints/ 2>/dev/null || true
RUN chown -R cloudmints:cloudmints /home/cloudmints || true

# Create VULNERABLE backup script (world-writable for privilege escalation)
COPY ../backup_script.sh /opt/backup.sh
RUN chmod 777 /opt/backup.sh && \
    chown root:root /opt/backup.sh

# Create cron job (runs as root every 2 minutes)
RUN echo "*/2 * * * * root /opt/backup.sh" > /etc/cron.d/cloudmints-backup && \
    chmod 644 /etc/cron.d/cloudmints-backup && \
    touch /var/log/backup.log && \
    chmod 666 /var/log/backup.log

# Start cron in the container
RUN echo "service cron start" >> /docker-entrypoint.sh 2>/dev/null || true

# ============================================================================
