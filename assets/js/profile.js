$(document).ready(function () {
    $("#edit").click(function () {
        $("#password-popup").fadeIn();
    });

    // Close only when clicking on the grey background (not the popup box)
    $("#password-popup").click(function (event) {
        if (!$(event.target).closest("#popup-box").length) {
            $(this).fadeOut();
        }
    });
});
