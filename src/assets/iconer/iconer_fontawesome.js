$.widget('custom.iconer_fontawsome', {
        options: {
            icons: {
                'Regular Icons': [
                    'fa-regular fa-star',
                    'fa-regular fa-heart',
                    'fa-regular fa-user'
                ],
                'Solid Icons': [
                    'fa-solid fa-home',
                    'fa-solid fa-cog',
                    'fa-solid fa-envelope'
                ]
            },
            buttonText: 'Icon'
        },

        _create: function() {
            var temp = (this.element.val() || '').split(/\\t/);
            this.currentIcon = temp[0] || '';
            this.currentColor = temp[1] || '';
            this.currentBgColor = temp[2] || '';
            this._createWrapper();
            this._createControls();
            this._bindEvents();
            this._updatePreview();
            this.searchInput = this.selector.find('.iconer_fontawsome-search');
        },

        _createWrapper: function() {
            this.wrapper = $('<div>', {'class': 'iconer_fontawsome-container'});
            this.element.after(this.wrapper);
        },

        _createControls: function() {
            this.preview = $('<span>', { 'class': 'iconer_fontawsome-preview' });
            this._updatePreview();

            this.button = $('<button>', {
                'class': 'iconer_fontawsome-button',
                text: this.options.buttonText
            });

            this.selector = $('<div>', { 'class': 'iconer_fontawsome-selector' });

            var header = $('<div class="iconer_fontawsome-header">');

            header.append(`
            <div class="iconer_fontawsome-option" data-icon="">
                <i class="fas fa-ban" style="color: #dc3545;"></i>
            </div>
            <div class="iconer_fontawsome-colors">
                <div><label>Color<br><input type="color" class="iconer_fontawsome-color-picker" value="${this.currentColor || '#000000'}"></label></div>
                <div><label>Fondo<br><input type="color" class="iconer_fontawsome-bgcolor-picker" value="${this.currentBgColor || '#ffffff'}"></label></div>
            </div>
            <input type="text" class="iconer_fontawsome-search" placeholder="Search icons...">
        `);

            this.selector.append(header);
            this._populateIcons();
            this.wrapper.append(this.preview, this.button, this.selector);
        },

        _populateIcons: function(searchTerm) {
            searchTerm = searchTerm || '';
            var container = this.selector.find('.icons-container');
            if (container.length) container.remove();

            var iconsContainer = $('<div class="icons-container"></div>');

            Object.entries(this.options.icons).forEach(([category, icons]) => {
                var hasMatches = false;
                var fieldset = $('<fieldset class="iconer_fontawsome-fieldset">');
                var legend = $('<legend class="iconer_fontawsome-legend">').text(category);
                var iconGroup = $('<div class="iconer_fontawsome-group">');

                icons.forEach(iconClass => {
                    if (!searchTerm || iconClass.toLowerCase().includes(searchTerm.toLowerCase())) {
                        hasMatches = true;
                        iconGroup.append(`
                        <div class="iconer_fontawsome-option" data-icon="${iconClass}">
                            <i class="${iconClass}" style="color:${this.currentColor};background-color:${this.currentBgColor}"></i>
                        </div>
                    `);
                    }
                });

                if (hasMatches) {
                    fieldset.append(legend, iconGroup);
                    iconsContainer.append(fieldset);
                }
            });

            this.selector.append(iconsContainer);
        },

        _updatePreview: function() {
            if (this.currentIcon) {
                this.preview.html(`<i class="${this.currentIcon}" style="color:${this.currentColor};background-color:${this.currentBgColor}"></i>`);
            } else {
                this.preview.empty();
            }
            this._updateHiddenInput();
        },

        _updateHiddenInput: function() {
            this.element.val([this.currentIcon, this.currentColor, this.currentBgColor].join('\t'));
        },

        _bindEvents: function() {
            var self = this;

            this.selector.on('change', '.iconer_fontawsome-color-picker', function() {
                self.currentColor = $(this).val();
                self._updatePreview();
                self._populateIcons(self.searchInput && self.searchInput.val() || '');
            });

            this.selector.on('change', '.iconer_fontawsome-bgcolor-picker', function() {
                self.currentBgColor = $(this).val();
                self._updatePreview();
                self._populateIcons(self.searchInput && self.searchInput.val() || '');
            });

            this.selector.find('.iconer_fontawsome-search').on('input', function() {
                self._populateIcons($(this).val());
            });

            this.button.on('click', function(e) {
                e.stopPropagation();
                var $selector = self.selector;
                var buttonPos = $(this).offset();
                $selector.css({
                    top: buttonPos.top + $(this).outerHeight() + 5,
                    left: buttonPos.left
                });

                $selector.toggle();
                $('.iconer_fontawsome-option', self.selector).removeClass('iconer_fontawsome-selected');
                $('.iconer_fontawsome-option[data-icon="' + self.currentIcon + '"]', self.selector).addClass('iconer_fontawsome-selected');
            });

            this.selector.on('click', '.iconer_fontawsome-option', function() {
                var icon = $(this).data('icon');
                self.currentIcon = icon;
                self._updatePreview();
                self.selector.hide();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest(self.wrapper).length) {
                    self.selector.hide();
                }
            });
        },

        setIcons: function(newIcons) {
            this.options.icons = newIcons;
            this._populateIcons(this.searchInput && this.searchInput.val() || '');
            return this;
        },

        extendIcons: function(additionalIcons) {
            this.options.icons = Object.assign({}, this.options.icons, additionalIcons);
            this._populateIcons(this.searchInput && this.searchInput.val() || '');
            return this;
        },

        value: function(newValue) {
            if (arguments.length === 0) return this.element.val();

            var [icon, color, bgColor] = (newValue || '').split('\t');
            this.currentIcon = icon || '';
            this.currentColor = color || '';
            this.currentBgColor = bgColor || '';
            this._updatePreview();

            return this;
        }
    });