# CloudMints Admin Panel

A clean, professional admin dashboard with login and file upload functionality.

## Features

✅ **Login System**
- Clean login page with demo credentials
- Session-based authentication
- Remember me functionality

✅ **Dashboard**
- 4 stat cards (Servers, Storage, Databases, Users)
- File upload manager with drag & drop
- Recent uploads table with dummy data
- Activity feed
- Professional sidebar navigation

✅ **File Upload**
- Drag and drop support
- File type validation
- 10MB size limit
- Secure upload handling
- Shows recent uploads with download/delete actions

✅ **Navigation**
- Sidebar with 7 menu items (Dashboard, Servers, Storage, Databases, Users, File Manager, Settings)
- Logout functionality
- User avatar in topbar

## Demo Credentials

**Username:** admin
**Password:** admin123

## File Structure

```
cloudmints-admin/
├── index.php           # Login page
├── dashboard.php       # Main dashboard
├── upload.php          # File upload handler
├── logout.php          # Logout handler
├── css/
│   └── admin-style.css # All admin styles
├── js/
│   └── admin-script.js # Admin functionality
├── uploads/            # Uploaded files directory
│   └── .htaccess       # Security rules
└── includes/           # PHP includes (for expansion)
```

## Installation

### Option 1: Add to Existing Docker Setup

1. Copy `cloudmints-admin/` to your project root
2. Update your main `index.html` to link to admin:
   ```html
   <a href="admin/" class="btn-admin">Admin Portal</a>
   ```

### Option 2: Standalone Setup

1. Place in web server directory (Apache/Nginx with PHP)
2. Ensure PHP 7.4+ is installed
3. Set proper permissions:
   ```bash
   chmod 755 cloudmints-admin
   chmod 777 cloudmints-admin/uploads
   ```

## Integration with Main Website

In your main CloudMints website navbar, add:

```html
<li><a href="admin/">Admin Portal</a></li>
```

Or create a button:

```html
<a href="admin/" class="btn btn-primary">Admin Login</a>
```

## Security Notes

⚠️ **This is for educational/demo purposes. For production use:**

- Implement proper password hashing (bcrypt)
- Use prepared statements for database queries
- Add CSRF protection
- Implement proper session management
- Add rate limiting for login attempts
- Use environment variables for sensitive data
- Enable HTTPS
- Implement proper file upload validation

## File Upload Allowed Types

- Images: JPG, JPEG, PNG, GIF
- Documents: PDF, DOC, DOCX, TXT
- Archives: ZIP
- Database: SQL

Max file size: 10MB

## Customization

### Change Colors

Edit `css/admin-style.css`:
```css
:root {
    --primary: #667eea;      /* Main brand color */
    --secondary: #764ba2;    /* Secondary brand color */
    --success: #10b981;      /* Success messages */
    --warning: #f59e0b;      /* Warnings */
    --danger: #ef4444;       /* Errors/delete */
}
```

### Add New Menu Items

Edit `dashboard.php` sidebar section and add:
```html
<a href="your-page.php" class="nav-item">
    <svg><!-- Your icon --></svg>
    Your Menu Item
</a>
```

### Modify Dummy Data

Edit the table section in `dashboard.php` to change:
- File names
- Upload times
- File types
- Status badges

## Vulnerable by Design (For Lab Use)

⚠️ **This admin panel contains intentional vulnerabilities for cybersecurity training:**

1. **Weak Authentication** - Simple username/password check
2. **File Upload Vulnerability** - Minimal validation for demo purposes
3. **No CSRF Protection** - Forms lack CSRF tokens
4. **Direct File Access** - Uploaded files may be directly accessible

**DO NOT use in production!** This is specifically designed for your CloudMints cybersecurity lab/webinar demonstration.

## Lab Attack Scenarios

This admin panel supports the following attack demonstrations:

1. **Credential Exposure** - Demo credentials visible
2. **File Upload Attack** - Upload malicious PHP files
3. **Session Hijacking** - Simple session management
4. **Directory Traversal** - File path manipulation

Perfect for your "Organization Takeover" webinar!

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Created for CloudMints cybersecurity training lab.
