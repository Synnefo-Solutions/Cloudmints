// Admin Panel JavaScript

// File upload drag and drop
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');

if (uploadArea && fileInput) {
    // Click to open file picker
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // Drag and drop handlers
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#667eea';
        uploadArea.style.background = 'rgba(102, 126, 234, 0.05)';
    });

    uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#e2e8f0';
        uploadArea.style.background = 'transparent';
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#e2e8f0';
        uploadArea.style.background = 'transparent';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    // File input change handler
    fileInput.addEventListener('change', handleFileSelect);
}

function handleFileSelect() {
    const file = fileInput.files[0];
    if (file) {
        const uploadText = document.querySelector('.upload-area h3');
        if (uploadText) {
            uploadText.textContent = `Selected: ${file.name}`;
            uploadText.style.color = '#667eea';
        }
    }
}

// Delete confirmation
document.querySelectorAll('.btn-icon.delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this file?')) {
            // In real implementation, this would send delete request
            const row = btn.closest('tr');
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    });
});

// Download button handler
document.querySelectorAll('.btn-icon:not(.delete)').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Download functionality would be implemented here');
    });
});

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

console.log('CloudMints Admin Panel loaded successfully');