document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('profile_image_input');
    var preview = document.getElementById('profile_image_preview');

    if (!input) {
        input = document.querySelector('input[type="file"][name="profile_image"]');
    }
    if (!preview) {
        preview = document.querySelector('img[data-profile-preview]');
    }

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        if (!file.type || !file.type.startsWith('image/')) return;
        preview.src = URL.createObjectURL(file);
    });
});

