var fields = BX.findChild(BX('subs'), {tag: 'input'}, true, true);
fields.forEach(function(element){
    BX.bind(element,'change',function () {
        BX.ajax.runComponentAction('natix:personal.settings',
            'ChangeSubs', { // Вызывается без постфикса Action
                mode: 'class',
                data: {fields:{'NAME':element.getAttribute('name'),'VALUE':element.checked}}, // ключи объекта data соответствуют параметрам метода
            })
            .then(function(response) {
            });
    });
});
BX.bind(BX('form-settings'), 'submit', function(e)
{e.preventDefault();
    let $this = this;
    inputs = BX.findChild($this, {
            'tag' : 'input'
        },
        true,
        true
    );
    let fields = {};
    let name;
    inputs.forEach(function(element){
        name = element.getAttribute('name');
        fields[name] = element.value;
    });
    BX.ajax.runComponentAction('natix:personal.settings',
        'UpdateUser', { // Вызывается без постфикса Action
            mode: 'class',
            data: {fields:fields}, // ключи объекта data соответствуют параметрам метода
        })
        .then(function(response) {
            if(response.data.STATUS === 'SUCCESS'){
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = response.data.MESSAGE;
            }else{
                BX.findChild(BX('modal-text'), {tag : 'h3',class:'modal__title'},true).innerHTML = 'Ошибка';
                BX.findChild(BX('modal-text'), {tag : 'p',class:'text-muted'},true).innerHTML = response.data.MESSAGE;
            }


            MicroModal.show('modal-text')
        });
});