(function(win, doc) {

    var form = doc.querySelector('#form-comment');
    if (!form) return;
    var footer = form.parentNode,
        q = win.location.search,
        a = doc.querySelectorAll('a.comment-parent'),
        x = form.querySelector('a.comment-reply-x'),
        content = form.content,
        content_placeholder = content.placeholder,
        parent = form.parent, i, j;
        q = !q || !q.match(/[?&]parent=\d+/);
    function reply(a) {
        a.addEventListener('click', function(e) {
            this.parentNode.parentNode.insertBefore(form, this.parentNode);
            form.classList.add('on-reply');
            content.placeholder = this.title;
            content.focus();
            parent.value = this.id.split(':')[1];
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
                e.preventDefault();
            }, false);
        }
    }

})(window, document);