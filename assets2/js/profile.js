$(function () {
    const form = $('#profile-form');
    if (!form.length) {
        return;
    }

    const submitButton = form.find('.submitbutton');
    const originalButtonText = submitButton.text();

    function showMessage(icon, message) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: icon,
                title: icon === 'success' ? 'Success!' : 'Unable to save',
                text: message,
            });
            return;
        }
        window.alert(message);
    }

    form.on('submit', function (event) {
        event.preventDefault();

        const formElement = form[0];
        if (!formElement.checkValidity()) {
            formElement.reportValidity();
            return;
        }

        submitButton.prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'ajax.php?action=update_profile',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response && response.result === true) {
                    form.find('[name="password"]').val('');
                    showMessage('success', response.message || 'Profile updated successfully.');
                } else {
                    showMessage('error', (response && response.message) || 'Your profile could not be updated.');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                showMessage('error', (response && response.message) || 'A server error occurred while saving your profile. Please try again.');
            },
            complete: function () {
                submitButton.prop('disabled', false).text(originalButtonText);
            },
        });
    });
});
