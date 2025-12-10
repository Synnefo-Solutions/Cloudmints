<?php
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';

function scanUploadDirectory() {
    $files = [];
    $uploadDir = __DIR__ . '/uploads';

    if (is_dir($uploadDir)) {
        $scanned = scandir($uploadDir);
        foreach ($scanned as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess') continue;

            $filepath = $uploadDir . '/' . $file;
            if (is_file($filepath)) {
                $ext = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
                $size = filesize($filepath);
                $time = filemtime($filepath);

                $files[] = [
                    'name' => $file,
                    'type' => $ext,
                    'size' => formatFileSize($size),
                    'uploaded' => date('Y-m-d H:i', $time),
                    'status' => 'Active'
                ];
            }
        }
    }

    return $files;
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$files = scanUploadDirectory();

$servers = [
    ['name' => 'web-server-01', 'ip' => '192.168.1.101', 'status' => 'Running', 'cpu' => '45%', 'memory' => '2.4 GB', 'uptime' => '45 days'],
    ['name' => 'web-server-02', 'ip' => '192.168.1.102', 'status' => 'Running', 'cpu' => '32%', 'memory' => '1.8 GB', 'uptime' => '45 days'],
    ['name' => 'db-server-01', 'ip' => '192.168.1.201', 'status' => 'Running', 'cpu' => '78%', 'memory' => '6.2 GB', 'uptime' => '120 days'],
    ['name' => 'api-server-01', 'ip' => '192.168.1.151', 'status' => 'Running', 'cpu' => '56%', 'memory' => '3.1 GB', 'uptime' => '30 days'],
    ['name' => 'backup-server', 'ip' => '192.168.1.250', 'status' => 'Stopped', 'cpu' => '0%', 'memory' => '0 GB', 'uptime' => '0 days'],
];

$storage = [
    ['name' => 'cloudmints-bucket-01', 'type' => 'S3', 'size' => '450 GB', 'files' => '12,450', 'region' => 'us-east-1', 'status' => 'Active'],
    ['name' => 'cloudmints-bucket-02', 'type' => 'S3', 'size' => '280 GB', 'files' => '8,920', 'region' => 'us-west-2', 'status' => 'Active'],
    ['name' => 'backup-storage', 'type' => 'Glacier', 'size' => '1.2 TB', 'files' => '450', 'region' => 'us-east-1', 'status' => 'Active'],
    ['name' => 'cdn-cache', 'type' => 'CloudFront', 'size' => '120 GB', 'files' => '5,600', 'region' => 'Global', 'status' => 'Active'],
];

$databases = [
    ['name' => 'cloudmints-prod-db', 'type' => 'MySQL', 'size' => '24 GB', 'tables' => '145', 'connections' => '50/100', 'status' => 'Healthy'],
    ['name' => 'cloudmints-staging-db', 'type' => 'MySQL', 'size' => '8 GB', 'tables' => '142', 'connections' => '12/50', 'status' => 'Healthy'],
    ['name' => 'analytics-db', 'type' => 'PostgreSQL', 'size' => '56 GB', 'tables' => '89', 'connections' => '25/100', 'status' => 'Healthy'],
    ['name' => 'cache-redis', 'type' => 'Redis', 'size' => '2 GB', 'tables' => 'N/A', 'connections' => '120/200', 'status' => 'Healthy'],
    ['name' => 'logs-db', 'type' => 'MongoDB', 'size' => '42 GB', 'tables' => '28', 'connections' => '8/50', 'status' => 'Warning'],
];

$users = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@cloudmints.in', 'role' => 'Admin', 'last_login' => '10 minutes ago', 'status' => 'Active'],
    ['id' => 2, 'name' => 'Sarah Smith', 'email' => 'sarah.smith@cloudmints.in', 'role' => 'Developer', 'last_login' => '2 hours ago', 'status' => 'Active'],
    ['id' => 3, 'name' => 'Mike Johnson', 'email' => 'mike.j@cloudmints.in', 'role' => 'DevOps', 'last_login' => '1 day ago', 'status' => 'Active'],
    ['id' => 4, 'name' => 'Emily Brown', 'email' => 'emily.b@cloudmints.in', 'role' => 'Developer', 'last_login' => '3 days ago', 'status' => 'Active'],
    ['id' => 5, 'name' => 'David Wilson', 'email' => 'david.w@cloudmints.in', 'role' => 'Viewer', 'last_login' => '1 week ago', 'status' => 'Inactive'],
];

$activities = [
    ['icon' => 'server', 'color' => 'blue', 'message' => 'Server web-server-01 restarted successfully', 'time' => '15 minutes ago'],
    ['icon' => 'upload', 'color' => 'green', 'message' => 'New file uploaded: backup-config.zip (2.4 MB)', 'time' => '1 hour ago'],
    ['icon' => 'user', 'color' => 'purple', 'message' => 'New user registered: john.doe@cloudmints.in', 'time' => '3 hours ago'],
    ['icon' => 'warning', 'color' => 'orange', 'message' => 'High CPU usage detected on db-server-01 (78%)', 'time' => '5 hours ago'],
    ['icon' => 'check', 'color' => 'green', 'message' => 'Automated backup completed successfully', 'time' => '1 day ago'],
    ['icon' => 'database', 'color' => 'blue', 'message' => 'Database backup created: prod-db-20251128.sql', 'time' => '2 days ago'],
];

$stats = ['servers' => 24, 'storage' => '1.2 TB', 'databases' => 18, 'users' => 1542];

$uploadMessage = $_SESSION['upload_message'] ?? null;
$uploadSuccess = $_SESSION['upload_success'] ?? false;
unset($_SESSION['upload_message'], $_SESSION['upload_success']);

$view = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($view); ?> - CloudMints Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="images/logo.png" alt="CloudMints" class="sidebar-logo" onerror="this.style.display='none'">
            <h2>CloudMints</h2>
        </div>

        <nav class="sidebar-nav">
            <a href="?view=dashboard" class="nav-item <?php echo $view === 'dashboard' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a href="?view=servers" class="nav-item <?php echo $view === 'servers' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2"></rect><rect x="2" y="14" width="20" height="8" rx="2"></rect>
                </svg>
                Servers
            </a>
            <a href="?view=storage" class="nav-item <?php echo $view === 'storage' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                Storage
            </a>
            <a href="?view=databases" class="nav-item <?php echo $view === 'databases' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                Databases
            </a>
            <a href="?view=users" class="nav-item <?php echo $view === 'users' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Users
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1><?php echo ucfirst($view); ?></h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                <div class="user-avatar" id="userAvatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                        <div class="dropdown-info">
                            <strong><?php echo htmlspecialchars($username); ?></strong>
                            <span>Administrator</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <?php if ($uploadMessage): ?>
        <div class="alert <?php echo $uploadSuccess ? 'success' : 'error'; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <?php if ($uploadSuccess): ?>
                    <polyline points="20 6 9 17 4 12"></polyline>
                <?php else: ?>
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                <?php endif; ?>
            </svg>
            <?php echo htmlspecialchars($uploadMessage); ?>
        </div>
        <?php endif; ?>

        <?php if ($view === 'dashboard'): ?>
        <!-- Dashboard Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="8" rx="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2"></rect>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Active Servers</h3>
                    <p class="stat-number"><?php echo $stats['servers']; ?></p>
                    <span class="stat-change positive">+12% from last month</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Storage Used</h3>
                    <p class="stat-number"><?php echo $stats['storage']; ?></p>
                    <span class="stat-change">of 5 TB allocated</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Databases</h3>
                    <p class="stat-number"><?php echo $stats['databases']; ?></p>
                    <span class="stat-change positive">+3 new this week</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo number_format($stats['users']); ?></p>
                    <span class="stat-change positive">+18% growth</span>
                </div>
            </div>
        </div>

        <!-- Analytics Row -->
        <div class="dashboard-row">
            <div class="content-section">
                <div class="section-header">
                    <div>
                        <h2>Traffic Overview</h2>
                        <p class="section-subtitle">Last 7 days</p>
                    </div>
                    <span class="badge-live">● Live</span>
                </div>
                <div class="chart-container">
                    <div class="chart-stats">
                        <div class="chart-stat-item">
                            <span class="chart-stat-label">Total Requests</span>
                            <span class="chart-stat-value">245,680</span>
                            <span class="chart-stat-change positive">↑ 24.5%</span>
                        </div>
                        <div class="chart-stat-item">
                            <span class="chart-stat-label">Avg Response Time</span>
                            <span class="chart-stat-value">145ms</span>
                            <span class="chart-stat-change positive">↓ 12%</span>
                        </div>
                    </div>
                    <div class="line-chart">
                        <svg viewBox="0 0 400 150" class="chart-svg">
                            <defs>
                                <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:rgb(102, 126, 234);stop-opacity:0.4" />
                                    <stop offset="100%" style="stop-color:rgb(102, 126, 234);stop-opacity:0" />
                                </linearGradient>
                            </defs>
                            <path class="chart-area" d="M 0 120 L 0 90 L 50 75 L 100 95 L 150 60 L 200 80 L 250 45 L 300 55 L 350 35 L 400 50 L 400 120 Z" 
                                  fill="url(#chartGradient)" />
                            <polyline class="chart-line" points="0,90 50,75 100,95 150,60 200,80 250,45 300,55 350,35 400,50" 
                                      fill="none" stroke="#667eea" stroke-width="3" />
                            <circle class="chart-dot" cx="0" cy="90" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="50" cy="75" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="100" cy="95" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="150" cy="60" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="200" cy="80" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="250" cy="45" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="300" cy="55" r="4" fill="#667eea" />
                            <circle class="chart-dot" cx="350" cy="35" r="5" fill="#667eea" />
                            <circle class="chart-dot" cx="400" cy="50" r="4" fill="#667eea" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <div>
                        <h2>System Resources</h2>
                        <p class="section-subtitle">Current usage</p>
                    </div>
                </div>
                <div class="resource-grid">
                    <div class="resource-card">
                        <div class="resource-header">
                            <span class="resource-label">CPU Usage</span>
                        </div>
                        <div class="circular-progress">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#f1f5f9" stroke-width="8"></circle>
                                <circle class="progress-ring" cx="50" cy="50" r="45" fill="none" stroke="#667eea" stroke-width="8" 
                                        stroke-dasharray="283" stroke-dashoffset="99" 
                                        transform="rotate(-90 50 50)" stroke-linecap="round"></circle>
                            </svg>
                            <div class="progress-center">65%</div>
                        </div>
                    </div>
                    <div class="resource-card">
                        <div class="resource-header">
                            <span class="resource-label">Memory</span>
                        </div>
                        <div class="circular-progress">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#f1f5f9" stroke-width="8"></circle>
                                <circle class="progress-ring" cx="50" cy="50" r="45" fill="none" stroke="#f59e0b" stroke-width="8" 
                                        stroke-dasharray="283" stroke-dashoffset="62" 
                                        transform="rotate(-90 50 50)" stroke-linecap="round"></circle>
                            </svg>
                            <div class="progress-center">78%</div>
                        </div>
                    </div>
                    <div class="resource-card">
                        <div class="resource-header">
                            <span class="resource-label">Disk Space</span>
                        </div>
                        <div class="circular-progress">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#f1f5f9" stroke-width="8"></circle>
                                <circle class="progress-ring" cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="8" 
                                        stroke-dasharray="283" stroke-dashoffset="164" 
                                        transform="rotate(-90 50 50)" stroke-linecap="round"></circle>
                            </svg>
                            <div class="progress-center">42%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="content-section">
            <div class="section-header">
                <h2>Recent Activity</h2>
                <a href="#" class="view-all-link">View all →</a>
            </div>
            <div class="activity-timeline">
                <?php foreach ($activities as $activity): ?>
                <div class="timeline-item">
                    <div class="timeline-marker <?php echo $activity['color']; ?>">
                        <?php if ($activity['icon'] === 'server'): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="8" rx="2"></rect>
                            </svg>
                        <?php elseif ($activity['icon'] === 'upload'): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        <?php elseif ($activity['icon'] === 'user'): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        <?php elseif ($activity['icon'] === 'warning'): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            </svg>
                        <?php elseif ($activity['icon'] === 'database'): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                            </svg>
                        <?php else: ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <p class="timeline-message"><?php echo $activity['message']; ?></p>
                        <span class="timeline-time"><?php echo $activity['time']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php elseif ($view === 'storage'): ?>
        <!-- File Upload Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>File Upload Manager</h2>
                <p class="section-subtitle">Upload and manage your files</p>
            </div>
            <div class="upload-container-centered">
                <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="upload-area" id="uploadArea">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <h3>Drop files here or click to browse</h3>
                        <p>Supported: Images, Documents, Archives (Max 10MB)</p>
                        <input type="file" name="file" id="fileInput" class="file-input">
                    </div>
                    <button type="submit" class="btn-upload">Upload File</button>
                </form>
            </div>
        </div>

        <!-- Storage Buckets -->
        <div class="content-section">
            <div class="section-header">
                <h2>Storage Buckets</h2>
                <p class="section-subtitle">Cloud storage overview</p>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bucket Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Files</th>
                            <th>Region</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storage as $item): ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><span class="file-type"><?php echo $item['type']; ?></span></td>
                            <td><?php echo $item['size']; ?></td>
                            <td><?php echo $item['files']; ?></td>
                            <td><?php echo $item['region']; ?></td>
                            <td><span class="badge success"><?php echo $item['status']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Uploaded Files with VIEW and DOWNLOAD -->
        <div class="content-section">
            <div class="section-header">
                <h2>Uploaded Files (<?php echo count($files); ?>)</h2>
                <p class="section-subtitle">Manage your uploaded files</p>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($files)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px; display: block; opacity: 0.5;">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <strong>No files uploaded yet</strong><br>
                                Use the upload form above to add files.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                            </td>
                            <td><span class="file-type"><?php echo $file['type']; ?></span></td>
                            <td><?php echo $file['size']; ?></td>
                            <td><?php echo $file['uploaded']; ?></td>
                            <td><span class="badge success"><?php echo $file['status']; ?></span></td>
                            <td>
                                <a href="view.php?file=<?php echo urlencode($file['name']); ?>" 
                                   class="btn-icon" title="View" target="_blank">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="download.php?file=<?php echo urlencode($file['name']); ?>" 
                                   class="btn-icon" title="Download">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>
                                <button class="btn-icon delete" onclick="deleteFile('<?php echo addslashes($file['name']); ?>')" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($view === 'users'): ?>
        <div class="content-section">
            <div class="section-header">
                <h2>User Management</h2>
                <p class="section-subtitle">Manage system users and permissions</p>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo $user['name']; ?></strong></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><span class="file-type"><?php echo $user['role']; ?></span></td>
                            <td><?php echo $user['last_login']; ?></td>
                            <td><span class="badge <?php echo $user['status'] === 'Active' ? 'success' : 'warning'; ?>"><?php echo $user['status']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <div class="content-section">
            <div class="section-header">
                <h2><?php 
                    switch($view) {
                        case 'servers':
                            echo 'Server Management';
                            break;
                        case 'databases':
                            echo 'Database Instances';
                            break;
                        default:
                            echo 'Management';
                    }
                ?></h2>
                <p class="section-subtitle">
                    <?php 
                        switch($view) {
                            case 'servers':
                                echo 'Monitor and manage your servers';
                                break;
                            case 'databases':
                                echo 'Database connections and status';
                                break;
                            default:
                                echo 'System overview';
                        }
                    ?>
                </p>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php if ($view === 'servers'): ?>
                                <th>Server Name</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>CPU Usage</th>
                                <th>Memory</th>
                                <th>Uptime</th>
                            <?php else: ?>
                                <th>Database Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Tables</th>
                                <th>Connections</th>
                                <th>Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $data = $view === 'servers' ? $servers : $databases;
                        foreach ($data as $item): 
                        ?>
                        <tr>
                            <?php if ($view === 'servers'): ?>
                                <td><strong><?php echo $item['name']; ?></strong></td>
                                <td><?php echo $item['ip']; ?></td>
                                <td><span class="badge <?php echo $item['status'] === 'Running' ? 'success' : 'warning'; ?>"><?php echo $item['status']; ?></span></td>
                                <td><?php echo $item['cpu']; ?></td>
                                <td><?php echo $item['memory']; ?></td>
                                <td><?php echo $item['uptime']; ?></td>
                            <?php else: ?>
                                <td><strong><?php echo $item['name']; ?></strong></td>
                                <td><span class="file-type"><?php echo $item['type']; ?></span></td>
                                <td><?php echo $item['size']; ?></td>
                                <td><?php echo $item['tables']; ?></td>
                                <td><?php echo $item['connections']; ?></td>
                                <td><span class="badge <?php echo $item['status'] === 'Healthy' ? 'success' : 'warning'; ?>"><?php echo $item['status']; ?></span></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Profile dropdown toggle
        const userAvatar = document.getElementById('userAvatar');
        const profileDropdown = document.getElementById('profileDropdown');

        userAvatar.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            profileDropdown.classList.remove('show');
        });

        profileDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // File upload drag and drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        if (uploadArea) {
            uploadArea.addEventListener('click', () => fileInput.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#667eea';
                uploadArea.style.background = 'rgba(102, 126, 234, 0.1)';
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '';
                uploadArea.style.background = '';
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '';
                uploadArea.style.background = '';
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    document.getElementById('uploadForm').submit();
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length) {
                    document.getElementById('uploadForm').submit();
                }
            });
        }

        // Delete file function
        function deleteFile(filename) {
            if (confirm('Are you sure you want to delete "' + filename + '"?')) {
                fetch('delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'file=' + encodeURIComponent(filename)
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + d.message);
                    }
                })
                .catch(() => alert('Error deleting file'));
            }
        }
    </script>
</body>
</html>