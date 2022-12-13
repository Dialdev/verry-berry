//работа с модальным окном авторизации по номеру телефона

document.getElementById("show-phone-auth-link").addEventListener('click', function (event) {
    event.preventDefault();
    MicroModal.show('phone-auth');
}, false);

document.getElementById("phone-auth-form").addEventListener('submit', function (event) {
    event.preventDefault();

    let login = jQuery('#phone-auth-login');

    let error = jQuery('#phone-auth-error');

    let submitButton = jQuery('#phone-auth-submit');

    error.css('display', 'none');

    submitButton.prop("disabled", true);

    jQuery.post(this.action, jQuery(this).serialize()).done(function (response) {
        submitButton.prop("disabled", false);

        if (typeof response.error == 'undefined' && typeof response.success == 'undefined') {
            error.text('Ошибка обработки запроса сервером');

            error.css('display', 'block');

            return false;
        }

        if (typeof response.error !== 'undefined') {
            error.text(response.message);

            error.css('display', 'block');

            if (response.error === 1) {
                login.empty();

                response.data.forEach(function (user) {
                    login.append(new Option(user.NAME + ' (' + user.LOGIN + ')', user.LOGIN));
                });

                login.css('display', 'initial');

                login.prop("disabled", false);
            }
        } else {
            login.css('display', 'none');

            login.prop("disabled", true);

            login.empty();
        }

        if (typeof response.success !== 'undefined') {
            if (response.success === 301) {
                window.location.reload();

                return true;
            }

            login.append(new Option(response.user.NAME + ' (' + response.user.LOGIN + ')', response.user.LOGIN));

            login.prop("disabled", false);

            jQuery('#phone-auth-phone').prop("disabled", true);

            jQuery('#phone-code').prop("disabled", false);

            jQuery('#phone-auth-submit').val('Отправить код');

            jQuery('#phone-auth-code-block').show(300);
        }


    }).fail(function () {
        submitButton.prop("disabled", false);
        
        login.css('display', 'none');

        login.prop("disabled", true);

        login.empty();

        error.text('Ошибка обработки запроса сервером');

        error.css('display', 'block');
    });
}, false);
