BX.bind(BX('form-recover'), 'submit', function()
{
    let $this = this;
    let email_input = BX.findChild($this, {'tag' : 'input','attribute' : {name: 'EMAIL'}},true);
    let email = email_input.value;
    if(email.length>0){
        BX.ajax.runComponentAction('natix:password.recovery',
            'SendCode', { // Вызывается без постфикса Action
                mode: 'class',
                data: {email:email}, // ключи объекта data соответствуют параметрам метода
            })
            .then(function(response) {
            if(response.data.STATUS === 'ERROR'){
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Ошибка';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = response.data.MESSAGE;
                MicroModal.show('modal-text')
            }else{
                MicroModal.close('modal-recover');
                MicroModal.show('modal-code');
            }
                BX.toggleClass($this, ['is-loading', '']);
            });
    }
    return false;
});

BX.bind(BX('recovery_button'), 'click', function()
{
   MicroModal.close('modal-login');
});

BX.bind(BX('send_again'), 'click', function()
{
    let $this = this;
    let email_input = BX.findChild(BX('form-recover'), {'tag' : 'input','attribute' : {name: 'EMAIL'}},true);
    let email = email_input.value;
    if(email.length>0){
        BX.ajax.runComponentAction('natix:password.recovery',
            'SendCode', { // Вызывается без постфикса Action
                mode: 'class',
                data: {email:email}, // ключи объекта data соответствуют параметрам метода
            })
            .then(function(response) {
            if(response.data.STATUS === 'ERROR'){
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Ошибка';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = response.data.MESSAGE;

            }else{
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Новый проверочный код отаравлен';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = '';
            }
                MicroModal.show('modal-text');
                BX.removeClass($this, 'is-loading');
            });
    }
    return false;
});
BX.bind(BX('change_email'), 'click', function(e)
{
    e.preventDefault();
    MicroModal.close('modal-code');
    MicroModal.show('modal-recover');
    BX.toggleClass($this, ['is-loading', '']);
});
BX.bind(BX('confirm_code_form'), 'submit', function(e)
{
    e.preventDefault();
    let $this = this;
    let code = '';
    let inputs = BX.findChild($this, {'tag' : 'input'},true,true);
    inputs.forEach(function(element){
        code += element.value;
    });
    if(code.length>0){
        BX.ajax.runComponentAction('natix:password.recovery',
            'CheckCode', { // Вызывается без постфикса Action
                mode: 'class',
                data: {code:code}, // ключи объекта data соответствуют параметрам метода
            })
            .then(function(response) {
            if(response.data.STATUS === 'ERROR'){
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Ошибка';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = response.data.MESSAGE;
                MicroModal.show('modal-text')
            }else{
                MicroModal.close('modal-code');
                MicroModal.show('modal-new-password');
            }
                BX.removeClass($this, 'is-loading');
            });
    }
});
BX.bind(BX('form-new-password'), 'submit', function(e)
{
    e.preventDefault();
    let $this = this;
        BX.ajax.runComponentAction('natix:password.recovery',
            'NewPassword', { // Вызывается без постфикса Action
                mode: 'class',
                data: {
                    newPassword:BX.findChild($this, {'tag' : 'input','attribute' : {name: 'PASSWORD'}},true).value,
                    newPasswordConfirm:BX.findChild($this, {'tag' : 'input','attribute' : {name: 'CONFIRM_PASSWORD'}},true).value
                }, // ключи объекта data соответствуют параметрам метода
            })
            .then(function(response) {
            if(response.data.STATUS === 'ERROR'){
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Ошибка';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = response.data.MESSAGE;
                MicroModal.show('modal-text')
            }else{
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = response.data.MESSAGE;
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = '';
                MicroModal.close('modal-new-password');
                MicroModal.show('modal-text');
            }
                BX.removeClass($this, 'is-loading');
            });

});
