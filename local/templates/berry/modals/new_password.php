<div class="modal modal_sm micromodal-slide" aria-hidden="true" id="modal-new-password">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" data-micromodal-close="data-micromodal-close">
            <div class="modal__inner" role="dialog" aria-modal="true" aria-labelledby="modal-new-password"><button class="modal__close" aria-label="Закрыть окно" data-micromodal-close="data-micromodal-close"></button>
                <header class="modal__header">
                    <h3 class="modal__title" id="#modalmodal-new-password-title">Новый пароль</h3>
                </header>
                <main class="modal__content" role="main">
                    <form class="form form_login" id="form-new-password">
                        <div class="form-group"><label>Новый пароль</label>
                            <input class="form-control" name="PASSWORD" type="password" placeholder="Введите пароль" id="new-password" minlength="6" required>
                            <div class="pass"></div>
                        </div>
                        <div class="form-group form-group_lg"><label>Повторите пароль</label>
                            <input class="form-control" name="CONFIRM_PASSWORD" type="password" placeholder="Повторите пароль" data-bouncer-match="#new-password" required>
                            <div class="pass"></div>
                        </div>
                        <div class="form-submits form-submits_modal"><button class="button button-dark" type="submit">СМЕНИТЬ ПАРОЛЬ</button></div>
                    </form>
                </main>
            </div>
        </div>
    </div>
</div>