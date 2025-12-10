# CloudMints - Cloud Infrastructure Platform

A modern, elegant static website for CloudMints cloud services with smooth animations and Docker support.

## Features

- 🎨 Modern gradient design inspired by NeoNXT
- ✨ Smooth scroll animations and hover effects
- 📱 Fully responsive design
- 🐳 Docker-ready with nginx
- 🚀 Optimized performance with gzip compression
- 🔒 Security headers included

## Project Structure

```
cloudmints-webapp/
├── index.html          # Main HTML file
├── css/
│   └── style.css      # Styles and animations
├── js/
│   └── script.js      # Interactive features
├── Dockerfile         # Docker configuration
├── docker-compose.yml # Docker Compose setup
├── nginx.conf         # Nginx server config
└── README.md          # This file
```

## Quick Start

### Option 1: Run with Docker Compose (Recommended)

```bash
cd cloudmints-webapp
docker-compose up -d
```

Visit: http://localhost:8080

### Option 2: Build and Run Docker Container

```bash
cd cloudmints-webapp
docker build -t cloudmints-web .
docker run -d -p 8080:80 cloudmints-web
```

Visit: http://localhost:8080

### Option 3: Run Locally (Development)

Simply open `index.html` in your browser or use a local server:

```bash
# Using Python
python -m http.server 8000

# Using Node.js
npx http-server
```

## Docker Commands

**Start the container:**
```bash
docker-compose up -d
```

**Stop the container:**
```bash
docker-compose down
```

**View logs:**
```bash
docker-compose logs -f
```

**Rebuild after changes:**
```bash
docker-compose up -d --build
```

## Customization

### Colors
Edit CSS variables in `css/style.css`:
```css
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --dark: #0f172a;
    --light: #f8fafc;
}
```

### Content
- Main content: `index.html`
- Styles: `css/style.css`
- Interactive features: `js/script.js`

## Key Features Explained

### Animations
- **Hero Section**: Fade-in animations with gradient text
- **Floating Cards**: Continuous floating effect with hover interactions
- **Scroll Animations**: Elements fade in as you scroll
- **Navbar**: Slide-down animation on page load

### Sections
1. **Hero**: Eye-catching header with gradient background
2. **Services**: Grid of cloud service offerings
3. **Solutions**: Numbered solution items with hover effects
4. **About**: Company information with statistics
5. **Contact**: Email and support information
6. **Footer**: Branding and copyright

### Admin Portal Link
The navbar includes an "Admin Portal" button that links to `/admin` - perfect for your lab scenario where you'll demonstrate the admin panel attack.

## For Lab/Demo Purposes

This is the main website that students will discover during reconnaissance phase. The admin portal link (`/admin`) will lead to the vulnerable admin panel where file upload attacks can be demonstrated.

## Performance

- Optimized images and assets
- Gzip compression enabled
- Browser caching configured
- Minimal external dependencies

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Created for CloudMints cybersecurity training lab.
