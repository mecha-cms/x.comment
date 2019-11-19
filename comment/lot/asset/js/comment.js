(function(win, doc) {

    var script = doc.currentScript || doc.getElementsByTagName('script').pop(),
        src = script.src,
        id = src.split('#')[1],
        form = id && doc.getElementById(id);

    if (!form) return;

    var footer = form.parentNode,
        q = win.location.search,
        a = doc.getElementsByClassName('comment-reply:v'),
        x = form.getElementsByClassName('comment-reply:x')[0],
        content = form['comment[content]'],
        content_placeholder = content.placeholder,
        test = /(\?|&(?:amp;)?)parent(?:=([1-9]\d{3,}-(?:0\d|1[0-2])-(?:0\d|[1-2]\d|3[0-1])(?:-(?:[0-1]\d|2[0-4])(?:-(?:[0-5]\d|60)){2}))?|&)/g,
        parent = form['comment[parent]'], i, j;

    q = !q || !q.match(test);

    function reply(a) {
        a.addEventListener('click', function(e) {
            var s = this.parentNode.parentNode.parentNode, // `a < li < ul.comment-links < footer.comment-footer`
                a = form.getAttribute('action'),
                i = this.id.split(':')[1];
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
                content.placeholder = content_placeholder;
                content.focus();
                parent.removeAttribute('value');
                e.preventDefault();
            }, false);
        }
    }

})(window, document);