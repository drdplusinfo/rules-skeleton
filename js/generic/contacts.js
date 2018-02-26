document.addEventListener('DOMContentLoaded', function () {
    var contacts = document.getElementById('contacts');
    if (contacts.classList.contains('permanent')) {
        return;
    }
    var timeoutSet = false;
    var hideContactsAt = function (at) {
        if (at <= Date.now()) {
            contacts.classList.remove('visible');
            timeoutSet = false;
        } else if (!timeoutSet && contacts.classList.contains('visible')) {
            timeoutSet = true;
            setTimeout(
                function () {
                    hideContactsAt(at);
                },
                at - Date.now() + 1 /* to get relative time one millisecond after */
            );
        }
    };
    window.addEventListener('scroll', function () {
        if (!contacts.classList.contains('visible')) {
            contacts.classList.add('visible');
            hideContactsAt(Date.now() + 1000);
        }
    });
    hideContactsAt(Date.now() + 1000);
});