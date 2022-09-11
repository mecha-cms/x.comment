import {
    D,
    W,
    getAttribute,
    getElement,
    getElements,
    getFormElement,
    getParent,
    letAttribute,
    letClass,
    setAttribute,
    setChildLast,
    setClass,
    setPrev,
    theLocation
} from '@taufik-nurrohman/document';

import {
    offEventDefault,
    onEvent
} from '@taufik-nurrohman/event';

const form = getFormElement('comment');

if (form) {
    let footer = getParent(form),
        comments = getParent(footer),
        q = theLocation.search,
        content = form['comment[content]'],
        parent = form['comment[parent]'],
        placeholder = getAttribute(content, 'placeholder'),
        test = /(?:\?|&(?:amp;)?)parent(?:=([1-9]\d{3,}-(?:0\d|1[0-2])-(?:0\d|[1-2]\d|3[0-1])(?:-(?:[0-1]\d|2[0-4])(?:-(?:[0-5]\d|60)){2}))?|&)/g;
    q = !q || !q.match(test);
    function onEventCancel(a) {
        onEvent('click', a, function (e) {
            setChildLast(footer, form);
            setAttribute(form, 'action', getAttribute(form, 'action').replace(test, ""));
            letClass(form, 'is:reply');
            setAttribute(content, 'placeholder', placeholder);
            content.focus();
            parent && (letAttribute(parent, 'value'));
            offEventDefault(e);
        })
    }
    function onEventReply(a) {
        onEvent('click', a, function (e) {
            // `a < li < ul.comment-tasks < footer.comment-footer`
            let s = getParent(getParent(getParent(this))),
                a = getAttribute(form, 'action'),
                i = test.exec(this.href);
            i = i ? i[1] : "";
            setPrev(s, form);
            a = a.replace(test, "");
            a += (a.indexOf('?') > -1 ? '&' : '?') + 'parent=' + i;
            setAttribute(content, 'placeholder', this.title);
            setAttribute(form, 'action', a);
            setClass(form, 'is:reply');
            content.focus();
            parent && (parent.value = i);
            offEventDefault(e);
        });
    }
    q && getElements('.js\\:cancel', comments).forEach(onEventCancel);
    q && getElements('.js\\:reply', comments).forEach(onEventReply);
}