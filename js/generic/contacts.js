document.addEventListener('DOMContentLoaded', function () {
    var contacts = document.getElementById('contacts');
    var lastScrollAt;
    var hideAfterSecond = function () {
        if (lastScrollAt + 999 < Date.now()) {
            contacts.classList.remove('hover');
        } else {
            setTimeout(hideAfterSecond, 1000);
        }
    };
    window.addEventListener('scroll', function () {
        lastScrollAt = Date.now();
        if (!contacts.classList.contains('hover')) {
            contacts.classList.add('hover');
            setTimeout(hideAfterSecond, 1000);
        }
    });
});