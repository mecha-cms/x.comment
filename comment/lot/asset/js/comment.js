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
    function reply(a) {
        a.addEventListener('click', function(e) {
            this.parentNode.parentNode.appendChild(form);
            content.placeholder = this.title;
            parent.value = this.id.split(':')[1];
            e.preventDefault();
        }, false);
    }
    for (i = 0, j = a.length; i < j; ++i) {
        reply(a[i]);
    }
    if (x && (!q || !q.match(/[?&]parent=\d+/))) {
        x.addEventListener('click', function(e) {
            footer.appendChild(form);
            content.placeholder = content_placeholder;
            parent.removeAttribute('value');
            e.preventDefault();
        }, false);
    }

})(window, document);