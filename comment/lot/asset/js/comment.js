(function($, win, doc) {

    if (typeof $ === "object") {
        var $$ = function() {}, i;
        for (i in $) {
            $$[i] = $[i];
        }
        win.COMMENT = $ = $$;
    }

    var form = doc.getElementById($.id);

    if (!form) return;

    var hooks = {}, param,
        footer = form.parentNode,
        q = win.location.search,
        a = doc.getElementsByClassName('comment-reply:v'),
        x = form.getElementsByClassName('comment-reply:x')[0],
        content = form.content,
        content_placeholder = content.placeholder,
        parent = form.parent, i, j;
        q = !q || !q.match(/[?&]parent=\d+/);

    function set(name, fn) {
        name = name.split('.');
        if (!hooks[name[0]]) hooks[name[0]] = {};
        var i = name[1] || Object.keys(hooks[name[0]]).length;
        hooks[name[0]][i] = fn;
    }

    function reset(name) {
        if (!name) {
            return hooks = {};
        }
        name = name.split('.');
        if (name[1]) {
            delete hooks[name[0]][name[1]];
        } else {
            delete hooks[name[0]];
        }
    }

    function fire(name, param) {
        name = name.split('.');
        if (!hooks[name[0]]) hooks[name[0]] = {};
        if (name[1]) {
            if (hooks[name[0]][name[1]]) hooks[name[0]][name[1]].apply(form, param);
        } else {
            for (var i in hooks[name[0]]) {
                hooks[name[0]][i].apply(form, param);
            }
        }
    }

    $.set = set;
    $.reset = reset;
    $.fire = fire;

    function reply(a) {
        a.addEventListener('click', function(e) {
            var s = this.parentNode;
            s.parentNode.insertBefore(form, s);
            form.classList.add('on-reply');
            content.placeholder = this.title;
            content.focus();
            parent.value = this.id.split(':')[1];
            param = [e, this];
            fire('on.comment.reply', param);
            fire('on.comment.reply.v', param);
            e.preventDefault();
        }, false);
    }
    if (q) {
        for (i = 0, j = a.length; i < j; ++i) {
            reply(a[i]);
        }
        if (x) {
            x.addEventListener('click', function(e) {
                footer.appendChild(form);
                form.classList.remove('on-reply');
                content.placeholder = content_placeholder;
                content.focus();
                parent.removeAttribute('value');
                param = [e, this];
                fire('on.comment.reply', param);
                fire('on.comment.reply.x', param);
                e.preventDefault();
            }, false);
        }
    }

})(window.COMMENT, window, document);