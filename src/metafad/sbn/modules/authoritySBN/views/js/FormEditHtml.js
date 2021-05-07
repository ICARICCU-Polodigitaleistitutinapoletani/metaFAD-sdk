Pinax.oop.declare("html", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    
    initialize: function (element) {
        element.data('instance', this);
        element.hide();
        this.$element = element;
    },
    
    setValue: function (value) {
        this.$element.parent().html(value);
    },
});