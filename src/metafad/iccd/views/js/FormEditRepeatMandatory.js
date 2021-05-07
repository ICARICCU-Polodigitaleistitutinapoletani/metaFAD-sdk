Pinax.oop.declare("pinax.FormEdit.FormEditRepeatMandatory", {
    $extends: Pinax.oop.get('pinax.FormEdit.repeat'),
    
    initialize: function (element, pinaxOpt, form, addBtnId, idParent) {
        
        this.$super(element, pinaxOpt, form, addBtnId, idParent);
        
        if ($(this.$element).prop('class').search(/([A-Za-z0-9]+)\-alternative\-mandatory/) > -1)
            $(this.$element).find('legend').addClass('mandatory-element-alternative');
        else if ($(this.$element).prop('class').search(/([A-Za-z0-9]+)\-context\-mandatory/) > -1)
            $(this.$element).find('legend').addClass('mandatory-element-context');
        else if ($(this.$element).prop('class').search(/([A-Za-z0-9]+)\-onlyOne\-mandatory/) > -1)
            $(this.$element).find('legend').addClass('mandatory-element-onlyOne');

        this.initializeEvents();
    },
    



    initializeEvents: function() {
        

       
        $('#'+this.addBtnId+'-addRowBtn').off("click").on('click', Pinax.responder(this, this.handleAddRecord));
        $(this.$element).off("click").on('click', '.GFERowDelete', Pinax.responder(this, this.handleDelete))
        
        if( window.location.href.includes("editMassive")){    
            
            
            //var pulsanteEdit = this.$element[0].lastChild.firstChild.lastChild;
            //debugger;
            var pulsanteEdit = $(this.$element).find('input[value="Edit"]');
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
            //punto al pulsante "edit" : da migliorare
            var nextSib = button[0].nextSibling;
            
            if( e.currentTarget.id.includes("addBtnId")){
                
                //punto al pulsante aggiungi un record: da migliorare
                var primoChild = e.currentTarget.parentNode.firstChild;
                
                primoChild.style.display = "none";
                e.currentTarget.style.display = "none";
                
                //stato edit per il salvataggio
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
        var $rows
        if( window.location.href.includes("editMassive")){
            $container = $(e.currentTarget).closest('.GFERowContainer');
            $fieldSet = $container.parent();
            $rows = $fieldSet.children('.GFERowContainer');
            id = $container.data('originalId');
        }
        this.$super(e);
        
            
        if( window.location.href.includes("editMassive")){
            if ($rows.length <=1)
                return;
            

            var $footer = $fieldSet.children('.GFEFooter');
            //da migliorare
            var edit = $footer[0].childNodes[0].childNodes[1];
            edit.style.display = "none";

            var button = $(e.currentTarget);
            var fieldset = $(button).closest("fieldset");
            fieldset.find('.svuota').attr("disabled",true);
            fieldset.find('.trovaesost').attr("disabled",true);
        }    
        
    },


    getOptions: function () {

        this.$super();
        
        if( window.location.href.includes("editMassive")){
            if( this.customAddRowLabel  && this.customAddRowLabel.localeCompare("Aggiungi")==0 )
             this.customAddRowLabel = "Modifica";
        }
        
    },

    addRow: function (fieldSet, footer, id, justCreated, noVerifySelectWithTarget) {
        var parentContainer = this.$super(fieldSet, footer, id, justCreated, noVerifySelectWithTarget);
        
        return $(parentContainer).prepend('<p><i>' + $(fieldSet).children('legend').html() + '</i></p>');
    },




    isValid: function() {
        var isValid = this.$super();
        
        if (isValid) {
            var currentClasses = $(this.$element).prop('class');
            var classes = currentClasses.split(' ');
            var group = '';
            for (var i = 0; i < classes.length; i++)
                if (classes[i].search(/([A-Za-z0-9]+)\-(alternative|context|onlyOne)\-mandatory/) > -1) {
                    group = classes[i];
                    break;
                }

            if (group != '') {
                var mp = group.match(/([A-Za-z0-9]+)\-(alternative|context|onlyOne)\-mandatory/);
                var groupName = mp[1];
                var mandatoryType = mp[2];
            
                var parent = $(this.$element).parent().closest('fieldset');
            
                var groupFieldsets = $(parent).find('fieldset[class*="' + group + '"]');
                var counter = 0;
                for (var i = 0; i < groupFieldsets.length; i++) {
                    var fs = groupFieldsets[i];
                    var rows = $(fs).find('.GFERowContainer');
                    
                    if (rows.length)
                        counter++;
                
                    /* Il codice commentato permette di verificare i valori dei campi presenti all'interno dei fieldset repeater */
                    /*var fs = groupFieldsets[i];
                    var inputs = $(fs).find('input[class*="form-control"]');
                    
                    if (inputs.length)                    
                        for (var j = 0; j < inputs.length; j++)
                            if ($(inputs[j]).val() != '') {
                                counter++
                                break;
                            }
                    */
                }

                if (mandatoryType == 'alternative') {
                    //console.log('Mandatory: ' + mandatoryType + ' Group: ' + groupName + ' Result: ' + (counter > 0 && groupFieldsets.length > 0));
                    return (counter > 0 && groupFieldsets.length > 0);
                } else if (mandatoryType == 'context') {
                    //console.log('Mandatory: ' + mandatoryType + ' Group: ' + groupName + ' Result: ' + (counter == groupFieldsets.length || counter == 0));
                    return (counter == groupFieldsets.length || counter == 0);
                } else if (mandatoryType == 'onlyOne') {
                    //console.log('Mandatory: ' + mandatoryType + ' Group: ' + groupName + ' Result: ' + (counter == groupFieldsets.length || counter == 0));
                    return (counter == 1 || groupFieldsets.length  == 0);
                }
            } else
                return true;
        }
        
        return false;
    },



     templateDefine: function() {


        this.$super();
        if( window.location.href.includes("editMassive")){
         Pinax.template.define('pinaxcms.FormEditRerpeater.footer',
            '<div class="GFEFooter">'+
                '<% if (canAdd) { %>'+
                    '<% if( label.localeCompare("Modifica")==0){ %>'+

                    '<div class="GFEButtonContainer">'+
                    '<input type="button" id="<%= addBtnId %>-addRowBtn" value="<%= label %>" class="btn GFEAddRow">'+
                    
                    '</div>'+
                    '<% }else{ %>'+

                '<div class="GFEButtonContainer">'+
                    '<input type="button" id="<%= addBtnId %>-addRowBtn" value="<%= label %>" class="btn GFEAddRow">'+
                    '<input type="button" id="<%= "addBtnId3" %>-addRowBtn" value="Edit" class="btn GFEAddRow" style="margin-left:3px">'+
                    '</div>'+
                    '<% } %>'+
                '<div class="GFEStatusContainer"><%= minRecords %></div>'+
                '<div class="GFESideClearer"></div>'+
                '<% } %>'+
            '</div>'
        );
        }
    }
});
