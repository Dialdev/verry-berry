<div class="modal modal_sm micromodal-slide" aria-hidden="true" id="modal-register">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" data-micromodal-close="data-micromodal-close">
            <div class="modal__inner" role="dialog" aria-modal="true" aria-labelledby="modal-register"><button class="modal__close" aria-label="Закрыть окно" data-micromodal-close="data-micromodal-close"></button>
                <header class="modal__header">
                    <h3 class="modal__title" id="#modalmodal-register-title">Регистрация</h3>
                </header>
                <main class="modal__content" role="main">
                    <form class="form form_login" id="form-register">
                        <div class="form-group"><label>Ваше имя</label><input class="form-control" type="tel" name="NAME" placeholder="Введите имя" required="required" /></div>
                        <div class="form-group"><label>Номер телефона</label><input class="form-control" type="tel" name="PERSONAL_PHONE" data-field="phone" placeholder="Введите номер телефона" minlength="11" required="required" /></div>
                        <div class="form-group"><label>E-mail</label><input class="form-control" type="email" name="EMAIL" placeholder="Введите почту" required></div>
                        <div class="form-group"><label>Пароль</label><input class="form-control" type="password" name="PASSWORD" placeholder="Введите пароль" id="register-password" minlength="6" required>
                            <div class="pass"></div>
                        </div>
                        <div class="form-group form-group_lg"><label>Повторите пароль</label><input class="form-control" type="password" name="CONFIRM_PASSWORD" placeholder="Повторите пароль" data-bouncer-match="#register-password" required>
                            <div class="pass"></div>
                        </div>
                        <div class="form-submits form-submits_modal"><button class="button button-dark" type="submit">ЗАРЕГИСТРИРОВАТЬСЯ</button>
                            <div class="button button-link" data-micromodal-close><!--data-micromodal-trigger="modal-login"-->
                                У меня уже есть аккаунт
                            </div>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>
</div>