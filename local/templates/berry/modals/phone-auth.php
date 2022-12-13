<div class="modal micromodal-slide" aria-hidden="true" id="phone-auth">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container">
            <div class="modal__inner" role="dialog" aria-modal="true" aria-labelledby="modal-location">
                <button class="modal__close" aria-label="Закрыть окно"
                        data-micromodal-close="data-micromodal-close"></button>
                <header class="modal__header">
                    <h3 class="modal__title">Авторизация по номеру телефона</h3>
                </header>
                <div id="phone-auth-error"></div>
                <main class="modal__content" role="main">
                    <div>
                        <form id="phone-auth-form" class="form" action="/ajax/auth-phone-call.php">
                            <div class="form-group">
                                <label for="phone-auth-phone">Ваш номер телефона</label>
                                <input type="number" name="phone" id="phone-auth-phone" min="70000000000" max="79999999999" placeholder="70000000000" maxlength="11" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <select name="login" id="phone-auth-login" class="form-control" disabled></select>
                            </div>

                            <div id="phone-auth-code-block" class="form-group">
                                <label for="phone-code">Ведите последние 3 цифры номера телефона, с которого поступил звонок</label>
                                <input type="number" name="code" id="phone-code" placeholder="000" min="1" max="999" minlength="3" maxlength="3" class="form-control" disabled required>
                            </div>

                            <div class="form-submits form-submits_modal">
                                <input type="submit" id="phone-auth-submit" value="Запросить звонок" class="button button-dark">
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>
