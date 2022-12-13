<div class="modal modal_sm micromodal-slide" aria-hidden="true" id="modal-login">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container">
            <div class="modal__inner" role="dialog" aria-modal="true" aria-labelledby="modal-login">
                <button class="modal__close" aria-label="Закрыть окно"
                        data-micromodal-close="data-micromodal-close"></button>
                <header class="modal__header">
                    <h3 class="modal__title" id="#modal-login-title">Вход в личный кабинет</h3>
                </header>
                <div class="show-phone-auth-link-block">
                    <a href="#" id="show-phone-auth-link">Хочу авторизироваться по номеру телефона</a>
                </div>
                <main class="modal__content" role="main">
                    <form class="form form_login" id="form-login">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input class="form-control" type="email" name="LOGIN" placeholder="Введите почту" required>
                        </div>
                        <div class="form-group">
                            <label>Текущий пароль</label>
                            <input class="form-control" type="password" name="PASSWORD" placeholder="Введите пароль" minlength="6" required>
                            <div class="pass"></div>
                        </div>
                        <div class="help-text server-error-message"></div>
                        <div class="form-submits form-submits_modal">
                            <div class="button button-link" id="recovery_button" data-micromodal-trigger="modal-recover">
                                Я не помню свой пароль
                            </div>
                            <button class="button button-dark" type="submit">ВОЙТИ В КАБИНЕТ</button>
                            <div class="button button-link text-muted text-md" data-micromodal-trigger="modal-register">У меня ещё нет аккаунта
                            </div>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>
</div>
