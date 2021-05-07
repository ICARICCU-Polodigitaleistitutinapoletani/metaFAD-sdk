Pinax.oop.declare("pinax.FormEdit.mediapickerMAG", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    $mediaPicker: null,
    populateDataEnabled: false,
    eventPos: null,
    imageResizer: null,

    initialize: function (element, pinaxOpt) {
        element.data('instance', this);
        this.$element = element;
        this.populateDataEnabled = element.attr('data-populate_data') == 'true';
        this.imageResizer = pinaxOpt.imageResizer;

        var that = this;
        var $input = element.hide(),
            pickerType = $input.attr('data-mediatype'),
            externalFiltersOR = $input.attr('data-externalFiltersOR'),
            hasPreview = $input.attr('data-preview') == 'true';

        that.$mediaPicker =
            hasPreview ? jQuery('<div id="'+element.attr('name')+'-mediapicker" class="mediaPickerSelector mediaPickerField"><div class="mediaPickerCaption"></div><div class="mediaPickerElement">' + PinaxLocale.MediaPicker.imageEmpty + '</div></div>')
            : jQuery('<input class="mediaPickerField" type="text" size="50" readonly="readonly" style="cursor:pointer" value="' + PinaxLocale.MediaPicker.imageEmptyText + '">');

        if (!$input.next().hasClass('mediaPickerField')) {
            that.$mediaPicker.insertAfter($input).click(function() {
                    var url = pinaxOpt.mediaPicker;
                    if (pickerType) {
                        url += '&mediaType=' + pickerType;
                    }
                    if (externalFiltersOR) {
                        url += '&externalFiltersOR=' + externalFiltersOR;
                    }
                    Pinax.openIFrameDialog( hasPreview ? PinaxLocale.MediaPicker.imageTitle : PinaxLocale.MediaPicker.mediaTitle,
                                            url,
                                            1400,
                                            50,
                                            50,
                                            null,
                                            Pinax.responder(that, that.disposeEvent));
                    Pinax.lastMediaPicker = that;
                    that.eventPos = Pinax.events.on("pinaxcms.onSetMediaPicker", Pinax.responder(that, that.onSetMediaPicker));
                });
        }

    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        if (value) {
            this.setProps(JSON.parse(value));
        }
    },

    populateData: function(values) {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');

        for (var field in values) {
            var $el = $container.find('input[data-media_picker_mapping='+field+']');
            if ($el) {
                var obj = $el.data('instance');

                if (obj) {
                    obj.setValue(values[field]);
                }
            }
        }
    },

    clearData: function() {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');
        $container.find('input[disabled=disabled]').val('');
    },

    setProps: function (props) {
        var $this = this.$mediaPicker,
            $img = $this.find('img');
        if (this.populateDataEnabled) {
            if (props) {
                this.populateData(props);
            } else {
                this.clearData();
            }
        }

        if (!props || !props.id) {
            if ($img.length) {
                $img.replaceWith(PinaxLocale.MediaPicker.imageEmpty);
            }
            else {
                $this.val(PinaxLocale.MediaPicker.imageEmptyText);
            }
            $this.prev().val('');
        }
        else {
            if (!$img.length) {
                $this.val(props.title);
            }
            $this.prev().val( props.id );
            $this.prev().trigger('change');
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    getPreview: function (val) {
        try {
            var props = JSON.parse(val);
            return props.title;
        } catch(e) {
            return val;
        }
    },

    disposeEvent: function()
    {
        if (this.eventPos!==null && this.eventPos!==undefined) {
            Pinax.events.unbind("pinaxcms.onSetMediaPicker", this.eventPos);
            this.eventPos = null;
        }
    },

    onSetMediaPicker: function(event)
    {
        this.disposeEvent();
        this.setProps(event.message);
        //Pinax.closeIFrameDialog();
    },

    focus: function () {
        var mediaPickerId = this.$element.attr('id')+'-mediapicker';
        $('#'+mediaPickerId).addClass('GFEValidationError');
        document.getElementById(mediaPickerId).scrollIntoView();
    },

    destroy: function() {
        this.disposeEvent();
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        this.$element.addClass(className);
    },

    removeClass: function(className) {
        this.$element.removeClass(className);
    }
});
