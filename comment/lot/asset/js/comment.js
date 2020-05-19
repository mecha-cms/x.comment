(function(win, doc) {

    let form = doc.forms.comment;

    if (!form) return;

    let footer = form.parentNode,
        comments = footer.parentNode,
        q = win.location.search,
        a = comments.getElementsByClassName('js:reply'),
        x = form.getElementsByClassName('js:cancel')[0],
        content = form['comment[content]'],
        placeholder = content.placeholder,
        test = /(\?|&(?:amp;)?)parent(?:=([1-9]\d{3,}-(?:0\d|1[0-2])-(?:0\d|[1-2]\d|3[0-1])(?:-(?:[0-1]\d|2[0-4])(?:-(?:[0-5]\d|60)){2}))?|&)/g,
        parent = form['comment[parent]'], i, j;

    q = !q || !q.match(test);

    function reply(a) {
        a.addEventListener('click', function(e) {
            let s = this.parentNode.parentNode.parentNode, // `a < li < ul.comment-links < footer.comment-footer`
                a = form.getAttribute('action'),
                i = this.getAttribute('data-parent');
            s.parentNode.insertBefore(form, s);
            a = a.replace(test, "");
            a += (a.indexOf('?') > -1 ? '&' : '?') + 'parent=' + i;
            form.setAttribute('action', a);
            form.classList.add('is:reply');
            content.placeholder = this.title;
            content.focus();
            parent.value = i;
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
                form.setAttribute('action', form.getAttribute('action').replace(test, ""));
                form.classList.remove('is:reply');
                content.placeholder = placeholder;
                content.focus();
                parent.removeAttribute('value');
                e.preventDefault();
            }, false);
        }
    }

})(this, this.document);
