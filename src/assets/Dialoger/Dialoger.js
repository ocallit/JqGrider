
// **Revealing Module Pattern**
function Dialoger(options) {
    if (!(this instanceof Dialoger))
        return new Dialoger(options);

    var dialogs = {
        info: {
            className: 'dialoger-info',
            icon: 'fa-solid fa-circle-info',
            icon_style: 'color:blue;'
        },
        comment: {
            className: 'dialoger-comment',
            icon: 'fa-solid fa-comment',
            icon_style: 'color:darkblue;'
        },
        warning: {
            className: 'dialoger-warning',
            icon: 'fa-solid fa-triangle-exclamation',
            icon_style: 'color:darkYellow;'
        },
        error: {
            className: 'dialoger-error',
            icon: 'fa-solid fa-circle-exclamation',
            icon_style: 'color:red;'
        },
        alert: {
            className: 'dialoger-alert',
            icon: 'fa-solid fa-bell',
            icon_style: 'color:red;'
        },
        danger: {
            className: 'dialoger-danger',
            icon: 'fa-solid fa-skull-crossbones',
            icon_style: 'color:crimson;'
        }
    };
    
    var template = '<dialog class="dialoger-dialog" style="padding:0;resize: both;min-width: 64px;min-height: 64px;overflow: auto;">' +
        '    <div class="dialoger-titlebar">\n' +
        '        <h3 class="dialoger-title"></h3>\n' +
        '        <button class="dialoger-close">\n' +
        '            <i class="fa-solid fa-xmark"></i>\n' +
        '        </button>\n' +
        '    </div>\n' +
        '    <div class="dialoger-content">\n' +
        '        <i class="dialoger-icon"></i>\n' +
        '        <p class="dialoger-message"></p>\n' +
        '    </div>\n' +
        '</dialog>'

    function createDialog(level, message, title, customIcon) {
        var dialog = $(template)[0];
        dialog.id = 'dialog-' + Math.random().toString(36).substr(2, 9);
        document.body.appendChild(dialog);

        var levelConfig = dialogs[level.toLowerCase()];
        dialog.classList.add(levelConfig.className);

        var titleElement = dialog.querySelector('.dialoger-title');
        titleElement.textContent = title || level || "";

        var iconElement = dialog.querySelector('.dialoger-icon');
        iconElement.className = 'dialoger-icon ' + (customIcon || levelConfig.icon);
        iconElement.setAttribute('style', levelConfig.icon_style);

        var messageElement = dialog.querySelector('.dialoger-message');
        messageElement.textContent = message;

        var closeButton = dialog.querySelector('.dialoger-close');

        return new Promise(function(resolve) {
            function closeHandler() {
                dialog.close();
                dialog.remove();
                resolve();
            }

            closeButton.addEventListener('click', closeHandler);
            dialog.addEventListener('click', function(event) {
                if(event.target === dialog) {
                    closeHandler();
                }
            });

            dialog.showModal();
        });
    }

    function info(message, title, customIcon) {
        return createDialog('info', message, title, customIcon);
    }

    function comment(message, title, customIcon) {
        return createDialog('comment', message, title, customIcon);
    }

    function warning(message, title, customIcon) {
        return createDialog('warning', message, title, customIcon);
    }

    function error(message, title, customIcon) {
        return createDialog('error', message, title, customIcon);
    }

    function alert(message, title, customIcon) {
        return createDialog('alert', message, title, customIcon);
    }

    function danger(message, title, customIcon) {
        return createDialog('danger', message, title, customIcon);
    }

    
    return {
        info: info,
        comment: comment,
        warning: warning,
        error: error,
        alert: alert,
        danger: danger,
    }
}
