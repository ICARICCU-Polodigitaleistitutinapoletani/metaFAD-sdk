Pinax.oop.declare("pinax.FormEditMassive", {
    formId: null,
    $form: null,
    pinaxOpt: null,
    invalidFields: 0,
    customValidationInvalid: false,
    lang: null,
    fields: [],
    formDataJSON: "{}",

    $statics: {
        fieldTypes: [],
        registerType: function (name, object) {
            this.fieldTypes[name] = object;
        }
    },

    getCurrentFormData: function() {
        var formData = {};

        this.fields.forEach(function(field) {
            if (!field.isDisabled()) {
                formData[field.getName()] = field.getValue();
            }
        });

        return formData;
    },

    hasUnmodifiedData: function() {
        return _.isEmpty(this.pinaxOpt.formData) || this.formDataJSON == JSON.stringify(this.getCurrentFormData());
    },

    updateFormData: function () {
        this.formDataJSON = JSON.stringify(this.getCurrentFormData());
    },

    initialize: function(formId, pinaxOpt) {
        var self = this;

        this.formId = formId;
        this.pinaxOpt = pinaxOpt;
        this.$form = $('#'+this.formId);
        this.$form.data('instance', this);
        this.lang = pinaxOpt.lang;
        this.readOnly = pinaxOpt.readOnly;

        $('#'+this.formId+' input[name]:not( [type="button"], [type="submit"], [type="reset"] ), '+
          '#'+this.formId+' textarea[name], '+
          '#'+this.formId+' select[name]').each(function () {
            self.createField(this);
        });

        // non unire alla selezione precedente, altrimenti gli input nel fieldset custom vengono
        // assegnati anche al formedit
        $('#'+this.formId+' fieldset[data-type]').each(function () {
            self.createField(this);
        });

        self.verifySelectWithTarget(this.$form);

        $('.js-pinaxcms-save').click(function (e) {
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, true, $(this));
        });

        $('.js-pinaxcms-cancel').click(function (e) {
            self.setFormButtonStates(false);
            window.onbeforeunload = null;
        });

        $('.js-pinaxcms-save-novalidation').click(function (e) {
            $.each(self.fields, function (index, obj) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            });
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, false, $(this));
        });

        this.initValidator();

        // aggangia anche l'evento submit per permettere la validazione dei campi
        this.$form.submit(function(event){
            if (self.$form.triggerHandler('submitForm') === false && this.invalidFields || this.customValidationInvalid) {
                self.customValidationInvalid = false;
                return false;
            } else {
                return true;
            }
        });

        window.setTimeout( //2s perché ci mette "un po'" ad aggiornare tutti i campi
            function(){
                self.updateFormData();
                window.onbeforeunload = function exitWarning(e) {
                    if (!self.hasUnmodifiedData()) {
                        var msg = PinaxLocale.FormEdit.discardConfirmation;
                        e = e || window.event;
                        // For IE and Firefox prior to version 4
                        if (e) {
                            e.returnValue = msg;
                        }
                        // For Safari
                        return msg;
                    }
                };
            },
            2000
        );

        Pinax.events.broadcast("pinaxcms.formEdit.onReady");
    },

    verifySelectWithTarget: function($container) {
        var self = this;
        $container.find('select').each(function () {
            if (self.isSubComponent($(this))) {
                return;
            }
            var target = $(this).data('target');
            if ( target ) {
                $(this).change(function(e){
                    var sel = this.selectedIndex,
                        states = $(this).data("val_"+sel),
                        stateMap = {};
                    var t = target.split(",");
                    states = states.split(",");

                    $(t).each(function(index, val) {
                        stateMap[val] = states[index]==="1";

                        if (stateMap[val]) {
                            $container.find("#"+val).show().find("[name]").data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else {
                            $container.find("#"+val).hide().find("[name]").data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });

                    $container.find("[name]").each(function(){
                        var $el = $(this);
                        var state = stateMap[$el.attr("name")];
                        if (state===true) {
                            $el.data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else if (state===false) {
                            $el.data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });
                });
                $(this).trigger("change");
            }
        });
    },

    setFormButtonStates: function(state) {
        if (state) {
            $('.js-pinaxcms-save').removeAttr('disabled');
            $('.js-pinaxcms-save-novalidation').removeAttr('disabled');
            $('.js-pinaxcms-cancel').removeAttr('disabled');
            $('.js-pinaxcms-preview').removeAttr('disabled');
        } else {
            $('.js-pinaxcms-save').attr('disabled', 'disabled');
            $('.js-pinaxcms-save-novalidation').attr('disabled', 'disabled');
            $('.js-pinaxcms-cancel').attr('disabled', 'disabled');
            $('.js-pinaxcms-preview').attr('disabled', 'disabled');
        }
    },

    // restituisce true se l'elemento è contenuto in un altro componente
    isSubComponent: function(element) {
        // se l'elemento è contenuto in altri tipi contenitori
        if ($(element).parents('[data-type]').length !== 0) {
            return true;
        } else {
            return false;
        }
    },

    createField: function(element) {
        if (this.isSubComponent(element)) {
            return;
        }

        var type = $(element).data('type') || 'standard';
        var obj = Pinax.oop.create("pinax.FormEdit."+type, $(element), this.pinaxOpt, this.$form);
        if (obj) {
        var value = this.pinaxOpt.formData[obj.getName()];
            if (value !== undefined) {
                obj.setValue(value);
            }

            this.fields.push(obj);
        }
    },

    initValidator: function() {
        var self = this;
        var firstInvalidObj = null;

        function testInvalidation(obj) {
            if (obj && !obj.isValid() && obj.getElement().is(":visible")) {
                obj.addClass('GFEValidationError');
                obj.getElement().closest('.control-group').addClass('GFEValidationError');
                self.invalidFields++;
            }
        }

        function testValidation(obj) {
            if (obj && obj.isValid()) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            }
        }

        self.$form.validVal({
            validate: {
                fields: {
                    hidden: true
                }
            },
            fields: {
                onInvalid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testInvalidation(obj);
                },
                onValid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testValidation(obj);
                    testInvalidation(obj);
                }
            },
            form: {
                onValidate: function () {
                    var error, fieldVals = {};

                    firstInvalidObj = null;

                    $('#'+self.formId+' fieldset[data-type]').each(function () {

                        // se l'elemento è contenuto in altro componente
                        if (self.isSubComponent($(this))) {
                            return;
                        }

                        var obj = $(this).data('instance');
                        if (!obj.isValid()) {
                            obj.addClass('GFEValidationError');
                            obj.getElement().closest('.control-group').addClass('GFEValidationError');
                            if (!self.customValidationInvalid) {
                                firstInvalidObj = obj;
                            }
                            self.customValidationInvalid = true;
                        } else {
                            obj.removeClass('GFEValidationError');
                            obj.getElement().closest('.control-group').removeClass('GFEValidationError');
                        }
                    });

                    if (self.pinaxOpt.customValidation && typeof(window[self.pinaxOpt.customValidation]) == 'function') {
                        jQuery(this).find('input:not( [type="button"], [type="submit"], [type="reset"] ), textarea, select').each(function () {
                            if (this.name) fieldVals[this.name] = jQuery(this).val();
                        });
                        if (error = window[self.pinaxOpt.customValidation](fieldVals)) {
                            alert(error);
                            Pinax.events.broadcast("pinax.message.showError", {"title": error, "message": ""});
                            self.customValidationInvalid = true;
                        }
                    }
                },
                onInvalid: function( field_arr, language ) {
                    var $invalidEl = field_arr.first();
                    if (!$invalidEl.is(":visible")) {
                        return true;
                    }

                    var obj = $invalidEl.data('instance');
                    obj.focus();

                    self.invalidFields = $invalidEl.length;

                    $invalidEl.addClass('GFEValidationError');
                    $invalidEl.closest('.control-group').addClass('GFEValidationError');

                    if (!self.customValidationInvalid) {
                        var inTab = $invalidEl.closest('div.tab-pane');
                        if (inTab.length) {
                            $('a[data-target="#'+inTab.attr('id')+'"]').tab('show');
                        }
                    }
                },

                onValid: function() {
                    if (self.customValidationInvalid && firstInvalidObj) {
                        firstInvalidObj.focus();
                    }
                }
            }
        });
    },


    getStatusPulsanti: function(){

        listaPulsanti = [];
        pulsante = {};
        
        function getPulsanteAttivo( el){

            childs = el.childNodes;
            name = "";
            
            var i;
            for( i=0; i<childs.length; i++){

                if(childs[i].className.includes('btn-info')){

                    name = childs[i].attributes['name'].nodeValue;
                }

            }

            return name;
        }



        function getCampo( el ){

             return el.parentNode.lastChild.previousSibling.attributes['name'].nodeValue;   

        }


        function getSostituisci( el ,pulsante){

            if (!pulsante.localeCompare('svuota')==0){

                var contenitore = el.parentNode;
                var lastChild   = contenitore.lastChild.previousSibling;

                if( lastChild.getAttribute("data-type")!=null && lastChild.getAttribute("data-type").toLowerCase().includes('select')){

                    var prec = lastChild.previousSibling;
                    var selectchoice = prec.querySelector('.select2-choice');
                    return selectchoice.querySelector('span').innerHTML;

                }


            }
                return  el.parentNode.lastChild.previousSibling.value;
            return null;
               
        }

        function getTrova( el ,pulsante){

            if(pulsante.localeCompare('trovaesost')==0)
               return  el.lastChild.lastChild.previousSibling.value;
            return null;  
       }


        $(".pulsanti").each(
            
            function(){

                
                var puls        = getPulsanteAttivo(this);
                var campo       = getCampo(this);
                var sostituisci = getSostituisci(this,puls);
                var trova       = getTrova(this,puls);


                pulsante = {};
                pulsante['pulsante'] = puls;
                pulsante['campo']    = campo;
                pulsante['sostituisci'] = sostituisci;
                pulsante['trova'] = trova;
                
                var fieldset = $(this).closest("fieldset");
                pulsante['fieldset'] = fieldset.attr("id");
                pulsante['stato']    = fieldset.attr('stato');

                listaPulsanti.push(pulsante);
            }
            
        );

        return listaPulsanti;

    },


    getStatusFieldSet: function(){

        fieldsets = [];
        

        $("#editForm"+" fieldset" ).each(

            function(){

                fieldset ={};
                fieldset["fieldset"] = $(this).attr("id");
                fieldset["stato"]    = $(this).attr("stato");

                fieldsets.push( fieldset);
            }
        );

        return fieldsets;

    },



    save: function (el, enableValidation, $saveButton) {
        var formData = this.getCurrentFormData();

        var pulsanti = this.getStatusPulsanti();
        var pulsantijson = JSON.stringify(pulsanti);
        var fieldsets = JSON.stringify(this.getStatusFieldSet());
        var self = this;

        if (enableValidation) {
            self.$form.triggerHandler('submitForm');

            if (self.invalidFields || self.customValidationInvalid) {
                self.customValidationInvalid = false;
                self.setFormButtonStates(true);
                self.invalidFields = 0;
                Pinax.events.broadcast("pinax.message.showError", {"title": self.lang.errorValidationMsg, "message": ""});
                return;
            }
        }

        var triggerAction = $(el).data("trigger");

        // return;

        jQuery.ajax(this.pinaxOpt.AJAXAction, {
            data: jQuery.param({action: $(el).data("action"), data: [JSON.stringify(formData),pulsantijson,fieldsets]    }),
            type: "POST",
            success: function (data) {
                if (data.errors) {

                    // TODO localizzare
                    var errorMsg = '<p>' + PinaxLocale.FormEdit.unableToSave + '</p><ul>';
                    $.each(data.errors, function(id, value) {
                        errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                    });
                    Pinax.events.broadcast("pinax.message.showError", {"title": self.lang.errorValidationMsg, "message": errorMsg});

                } else {
                    self.updateFormData();

                    if (data.evt) {

                        window.parent.Pinax.events.broadcast(data.evt, data.message);
                    } else if (data.url) {

                        if (data.target == 'window') {
                            parent.window.location.href = data.url;
                        } else {
                            document.location.href = data.url;
                        }

                    } else if (data.set) {

                        $.each(data.set, function(id, value){
                            $('#'+id).val(value);
                        });
                        Pinax.events.broadcast("pinax.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        if (triggerAction) {
                            triggerAction('click', formData);
                        }

                    } else if (data.callback) {

                        window[data.callback](data);

                    } else {

                        if (triggerAction) {
                            triggerAction('click', formData);
                        } else {
                            Pinax.events.broadcast("pinax.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        }

                    }
                }

                self.setFormButtonStates(true);
            }
        });
    }
});