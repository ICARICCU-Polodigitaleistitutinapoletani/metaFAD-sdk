Pinax.oop.declare("pinax.FormEdit.FormEditRepeatMassive", {
    $extends: Pinax.oop.get('pinax.FormEdit.repeat'),


    initialize: function (element, pinaxOpt, form, addBtnId, idParent) {
        
        this.$super(element, pinaxOpt, form, addBtnId, idParent);
        this.initializeEvents();
    },



    initializeEvents: function() {
        

        $('#'+this.addBtnId+'-addRowBtn').off("click").on('click', Pinax.responder(this, this.handleAddRecord));
        $(this.$element).off("click").on('click', '.GFERowDelete', Pinax.responder(this, this.handleDelete));
        
        if( window.location.href.includes("editMassive")){
            
            //da migliorare
            var pulsanteEdit = this.$element[0].lastChild.firstChild.lastChild;
            $(pulsanteEdit).off("click").on('click', Pinax.responder(this, this.handleAddRecord));
        }
        
    },

    handleAddRecord: function(e) {


        this.$super(e);
        if( window.location.href.includes("editMassive")){
         
            var button = $(e.currentTarget);
            var fieldset = $(button).closest("fieldset");

            fieldset.attr("stato",button.attr("value"));

            if( button.attr("value").localeCompare('Edit')!=0 && button.attr("value").localeCompare('Aggiungi un record')!=0)
                return;    

            //da migliorare
            var nextSib = button[0].nextSibling;
            
            
            if( e.currentTarget.id.includes("addBtnId")){

                //da migliorare
                var primoChild = e.currentTarget.parentNode.firstChild;
                
                primoChild.style.display = "none";
                e.currentTarget.style.display = "none";

                fieldset.attr("stato","edit");
                return;
            }
            
            nextSib.style.display = "none";
            fieldset.attr("stato","aggiungi");
            if( !e.currentTarget.id.includes("addBtnId") ){
                
                fieldset.find('.svuota').attr("disabled",true);
                fieldset.find('.trovaesost').attr("disabled",true);
            }
        }
        
    },


    handleDelete: function(e) {

        var $container;
        var $fieldSet;
        var $rows;
        var $container;
        if( window.location.href.includes("editMassive")){
            var $container = $(e.currentTarget).closest('.GFERowContainer');
        $fieldSet = $container.parent();
        $rows = $fieldSet.children('.GFERowContainer');
        id = $container.data('originalId');
        }
        
        this.$super(e);
        
            
        if( window.location.href.includes("editMassive")){
            if ($rows.length <=1) {
                return;
            }

            var $footer = $fieldSet.children('.GFEFooter');
            
            //punto al pulsante edit : da cambiare
            var edit = $footer[0].childNodes[0].childNodes[1];
            edit.style.display = "none";

            var button = $(e.currentTarget);
            var fieldset = $(button).closest("fieldset");
            fieldset.find('.svuota').attr("disabled",true);
            fieldset.find('.trovaesost').attr("disabled",true);
        }
        
    },



    templateDefine: function() {
        
        this.$super();
        if( window.location.href.includes("editMassive")){
         Pinax.template.define('pinaxcms.FormEditRerpeater.footer',
            '<div class="GFEFooter">'+
                '<% if (canAdd) { %>'+
                '<div class="GFEButtonContainer">'+
                    '<input type="button" id="<%= addBtnId %>-addRowBtn" value="<%= label %>" class="btn GFEAddRow">'+
                    '<input type="button" id="<%= "addBtnId3" %>-addRowBtn" value="Edit" class="btn GFEAddRow" style="margin-left:3px">'+
                    '</div>'+
                '<div class="GFEStatusContainer"><%= minRecords %></div>'+
                '<div class="GFESideClearer"></div>'+
                '<% } %>'+
            '</div>'
        );
        }
    }

});