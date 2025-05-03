document.addEventListener('DOMContentLoaded', function() {
    // Image preview for plant upload
    const imageInput = document.querySelector('input[name="plant_image"]');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.maxWidth = '100%';
                    preview.style.marginTop = '10px';
                    
                    const existingPreview = imageInput.nextElementSibling;
                    if (existingPreview && existingPreview.tagName === 'IMG') {
                        existingPreview.remove();
                    }
                    
                    imageInput.parentNode.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Reminder notifications
    function checkReminders() {
        fetch('check_reminders.php')
            .then(response => response.json())
            .then(data => {
                if (data.reminders && data.reminders.length > 0) {
                    data.reminders.forEach(reminder => {
                        showNotification(reminder);
                    });
                }
            })
            .catch(error => console.error('Error checking reminders:', error));
    }

    // Show notification
    function showNotification(reminder) {
        if (!("Notification" in window)) {
            console.log("This browser does not support desktop notification");
            return;
        }

        if (Notification.permission === "granted") {
            createNotification(reminder);
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    createNotification(reminder);
                }
            });
        }
    }

    function createNotification(reminder) {
        const notification = new Notification("PlantPal Reminder", {
            body: reminder.message,
            icon: "assets/images/logo.png"
        });

        notification.onclick = function() {
            window.focus();
            this.close();
        };
    }

    // Check reminders every 5 minutes
    setInterval(checkReminders, 300000);

    // Plant care tips carousel
    const careTips = [
        "Water your plants in the morning for best results",
        "Check soil moisture before watering",
        "Rotate your plants regularly for even growth",
        "Clean leaves regularly to prevent pests",
        "Use room temperature water for watering"
    ];

    let currentTip = 0;
    const tipElement = document.getElementById('care-tip');
    if (tipElement) {
        setInterval(() => {
            currentTip = (currentTip + 1) % careTips.length;
            tipElement.textContent = careTips[currentTip];
        }, 5000);
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}); 