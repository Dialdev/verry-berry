<div class="modal modal_sm micromodal-slide" aria-hidden="true" id="modal-code">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" data-micromodal-close="data-micromodal-close">
            <div class="modal__inner" role="dialog" aria-modal="true" aria-labelledby="modal-code"><button class="modal__close" aria-label="Закрыть окно" data-micromodal-close="data-micromodal-close"></button>
                <header class="modal__header">
                    <h3 class="modal__title" id="#modalmodal-code-title">Введите код из письма</h3>
                </header>
                <main class="modal__content" role="main">
                    <form class="form form_login" id="confirm_code_form">
                        <div class="form-group form-group_lg">
                            <div class="group-inputs group-inputs_modal">
                                <input type="text" placeholder="-" required>
                                <input type="text" placeholder="-" required>
                                <input type="text" placeholder="-" required>
                                <input type="text" placeholder="-" required>
                            </div>
                        </div>
                        <div class="form-group form-group_lg">
                            <div class="text-muted text-md text-center">Не пришел код?
                                <a href="javascript:void(0)" id="send_again">Отправить ещё раз</a></div>
                        </div>
                        <div class="form-submits form-submits_modal">
                            <button class="button button-block button-dark" type="submit" >ОТПРАВИТЬ</button>
                            <button class="button button-block button-link" type="button" id="change_email">
                                <div class="text-muted text-md">Изменить e-mail</div>
                            </button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>
</div>