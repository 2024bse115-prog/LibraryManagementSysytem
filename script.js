const authModel = document.querySelector('.auth-model');
const loginLink = document.querySelector('.login-link');
const registerLink = document.querySelector('.register-link');
const loginBtnModel = document.querySelectorAll('.login-btn-model');
const closeBtnModel = document.querySelector('.close-btn-model');
const profileBox = document.querySelector('.profile-box');
const avatarCircle = document.querySelector('.avatar-circle');
const alertBox = document.querySelector('.alert-box');

// Auth modal handling
if (registerLink) {
    registerLink.addEventListener('click', function(e) {
        e.preventDefault();
        authModel.classList.add('slide');
    });
}

if (loginLink) {
    loginLink.addEventListener('click', function(e) {
        e.preventDefault();
        authModel.classList.remove('slide');
    });
}

if (loginBtnModel) {
    loginBtnModel.forEach(function(btn) {
        btn.addEventListener('click', function() {
            authModel.classList.add('show');
        });
    });
}

if (closeBtnModel) {
    closeBtnModel.addEventListener('click', function() {
        authModel.classList.remove('show', 'slide');
    });
}

// Close modal on outside click
if (authModel) {
    authModel.addEventListener('click', function(e) {
        if (e.target === authModel) {
            authModel.classList.remove('show', 'slide');
        }
    });
}

// Profile dropdown
if (avatarCircle) {
    avatarCircle.addEventListener('click', function() {
        profileBox.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileBox.contains(e.target)) {
            profileBox.classList.remove('show');
        }
    });
}

// Alert box handling
if (alertBox) {
    setTimeout(function() {
        alertBox.classList.add('show');
    }, 0);
    
    setTimeout(function() {
        alertBox.classList.remove('show');
        setTimeout(function() {
            alertBox.remove();
        }, 1000);
    }, 3000);
}

// REMOVED FORM SUBMIT LISTENER - Let forms submit naturally

// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function (e) {
        var href = this.getAttribute('href');
        
        // Skip if it's just '#' (used for modals)
        if (href === '#') {
            return;
        }
        
        e.preventDefault();
        var target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Auto-hide header on scroll
let lastScroll = 0;
const header = document.querySelector('header');

if(header) {
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.style.transform = 'translateY(0)';
            return;
        }
        
        if (currentScroll > lastScroll && currentScroll > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScroll = currentScroll;
    });
}

// Add active class to current page
const currentPage = window.location.pathname.split('/').pop();
document.querySelectorAll('nav a').forEach(function(link) {
    if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
    }
});

// File size validation
const fileInputs = document.querySelectorAll('input[type="file"]');
fileInputs.forEach(function(input) {
    input.addEventListener('change', function() {
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (this.files[0] && this.files[0].size > maxSize) {
            alert('File size must be less than 10MB!');
            this.value = '';
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape to close modals
    if (e.key === 'Escape') {
        if (authModel && authModel.classList.contains('show')) {
            authModel.classList.remove('show', 'slide');
        }
        if (profileBox && profileBox.classList.contains('show')) {
            profileBox.classList.remove('show');
        }
        const modal = document.querySelector('.modal');
        if (modal && modal.style.display === 'flex') {
            modal.style.display = 'none';
        }
    }
});

console.log('System loaded successfully!');