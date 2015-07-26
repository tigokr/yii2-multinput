(function ($) {
    $.fn.multipleInput = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.multipleInput');
            return false;
        }
    };

    var defaultOptions = {
        id: null,
        template: null,
        jsTemplates: [],
        btnAction: null,
        btnType: null,
        max: 0,
        min: 0,
        replacement: []
    };

    var methods = {
        init: function (options) {
            var settings = $.extend({}, defaultOptions, options || {}),
                wrapper = $('#' + settings.id),
                form = wrapper.closest('form'),
                id = this.selector.replace('#', ''),
                count = wrapper.find('.multiple-input-list__item').length;

            wrapper.data('multipleInput', {
                settings: settings,
                currentIndex: 0,
                attributeDefaults: {}
            });

            $(document).on('click.multipleInput', '#' + settings.id + ' .js-input-remove', function (e) {
                e.preventDefault();
                methods.removeInput.apply(this);
            });

            $(document).on('click.multipleInput', '#' + settings.id + ' .js-input-plus', function (e) {
                e.preventDefault();
                methods.addInput.apply(this);
            });

            if (settings.min != null && count <= settings.min) {
                methods.hideActions.apply(wrapper);
            } else {
                methods.showActions.apply(wrapper);
            }

            var intervalID = setInterval(function () {
                if (typeof form.data('yiiActiveForm') === 'object') {
                    var attribute = form.yiiActiveForm('find', id);
                    if (typeof attribute === 'object') {
                        $.each(attribute, function (key, value) {
                            if (['id', 'input', 'container'].indexOf(key) == -1) {
                                wrapper.data('multipleInput').attributeDefaults[key] = value;
                            }
                        });
                        form.yiiActiveForm('remove', id);
                    }

                    wrapper.find('.multiple-input').find('input, select, textarea').each(function () {
                        methods.addAttribute.apply(this);
                    });
                    wrapper.data('multipleInput').currentIndex = wrapper.find('.multiple-input-list__item').length;
                    clearInterval(intervalID);
                }
                wrapper.trigger('init');
            }, 100);



        },

        addInput: function () {
            var wrapper = $(this).closest('.multiple-input').first(),
                data = wrapper.data('multipleInput'),
                settings = data.settings,
                template = settings.template,
                inputList = wrapper.find('.multiple-input-list').first(),
                count = wrapper.find('.multiple-input-list__item').length,
                replacement = settings.replacement || [];

            if (settings.max != null && count >= settings.max) {
                return;
            }

            var search = ['{multiple-index}', '{multiple-btn-action}', '{multiple-btn-type}', '{multiple-value}'],
                replace = [data.currentIndex, settings.btnAction, settings.btnType, ''];

            for (var i in search) {
                template = template.replaceAll(search[i], replace[i]);
            }

            for (var j in replacement) {
                template = template.replaceAll('{' + j + '}', replacement[j]);
            }

            $(template).appendTo(inputList);
            $(template).find('input, select, textarea').each(function () {
                methods.addAttribute.apply(this);
            });

            var jsTemplate;
            for (i in settings.jsTemplates) {
                jsTemplate = settings.jsTemplates[i].replaceAll('{multiple-index}', data.currentIndex).replaceAll('%7Bmultiple-index%7D', data.currentIndex);
                window.eval(jsTemplate);
            }
            methods.showActions.apply(this);
            wrapper.data('multipleInput').currentIndex++;
            wrapper.trigger('addNewRow');
        },

        removeInput: function () {
            var wrapper = $(this).closest('.multiple-input').first(),
                line = $(this).closest('.multiple-input-list__item'),
                data = wrapper.data('multipleInput'),
                settings = data.settings,
                count = wrapper.find('.multiple-input-list__item').length;

            if (settings.min != null && count <= settings.min) {
                methods.hideActions.apply(this);
                return;
            }

            line.removeClass('multiple-input-list__item');

            if (settings.min != null && count-1 <= settings.min) {
                methods.hideActions.apply(this);
            }

            line.find('input, select, textarea').each(function () {
                methods.removeAttribute.apply(this);
            });

            line.remove();

            wrapper.trigger('removeRow');

        },

        addAttribute: function () {
            var id = $(this).attr('id'),
                ele = $('#' + $(this).attr('id')),
                wrapper = ele.closest('.multiple-input').first(),
                form = ele.closest('form');

            form.yiiActiveForm('add', $.extend(wrapper.data('multipleInput').attributeDefaults, {
                'id': id,
                'input': '#' + id,
                'container': '.field-' + id
            }));
        },

        removeAttribute: function () {
            var id = $(this).attr('id');
            var form = $('#' + $(this).attr('id')).closest('form');
            try {
                form.yiiActiveForm('remove', id);
            } catch (e) {};
        },

        hideActions: function () {
            var wrapper = $(this).closest('.multiple-input').first();
            wrapper.find('.multiple-input-list__item .list-cell__button.remove').hide();
        },

        showActions: function () {
            var wrapper = $(this).closest('.multiple-input').first();
            wrapper.find('.multiple-input-list__item .list-cell__button').show();
        }
    };

    String.prototype.replaceAll = function (search, replace) {
        return this.split(search).join(replace);
    };
})(window.jQuery);