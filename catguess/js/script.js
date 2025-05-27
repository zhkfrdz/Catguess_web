// Cat Guess Game - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alert messages after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 3000);
    });

    // Handle guess form submission
    const guessForm = document.querySelector('form[name="guess-form"]');
    if (guessForm) {
        guessForm.addEventListener('submit', function(e) {
            const guessInput = document.getElementById('guess_number');
            if (!guessInput.value) {
                e.preventDefault();
                alert('Please enter a number');
            }
        });
    }

    // Add sound effects (to be implemented when sound files are available)
    function playSound(soundType) {
        // This function will be implemented when sound files are available
        console.log('Playing sound: ' + soundType);
    }

    // Add animation to hearts
    const hearts = document.querySelectorAll('.heart');
    hearts.forEach(function(heart) {
        heart.addEventListener('mouseover', function() {
            this.style.transform = 'scale(1.2)';
        });
        heart.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(function(button) {
        button.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
        });
        button.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
