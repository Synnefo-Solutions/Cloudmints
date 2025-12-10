ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata
FROM nginx:alpine

# Copy website files to nginx html directory
COPY . /usr/share/nginx/html/

# Copy custom nginx configuration (optional)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]# === LAB INJECTED ===
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
