Pinax.oop.declare("pinax.FormEdit.FormEditSelectMassive", {
    $extends: Pinax.oop.get('pinax.FormEdit.selectfrom'),

    initialize: function (element) {
        this.$super(element);

        if( window.location.href.includes("editMassive")){
         
            element.prev().hide();
            //si potrebbe cambiare
            element[0].style.display = "none";    
                
        }
    },

    
});