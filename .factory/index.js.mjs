import {
    D,
    getDatum,
    getElement,
    getFormElement,
    getParent,
    letClass,
    setChildLast,
    setClass,
    setPrev
} from '@taufik-nurrohman/document';

import {
    offEventDefault,
    onEvent
} from '@taufik-nurrohman/event';

function getParentValueFrom(href) {
    return u(href).searchParams.get('parent');
}

function letParentValueFrom(href) {
    href = u(href);
    href.searchParams.delete('parent');
    return href + "";
}

function onClickCancel(e) {
    let form = getFormElement('comment');
    if (!form) {
        return; // Skip if comment form does not exist!
    }
    let $ = getParent(e.target, '[data-task=cancel]'),
        comments = $ && getParent($, '.comments[data-status]'),
        commentsFooter = getElement('.comments-footer', comments),
        formContent = form['comment[content]'],
        formParent = form['comment[parent]'];
    if (!$ || !comments.contains($)) {
        return; // Skip if cancel button does not exist or does exist but outside the root comment(s)’ container!
    }
    // Append comment form to the root comment(s)’ footer or to the root comment(s)’ container!
    setChildLast(commentsFooter || comments, letClass(form, 'in-reply'));
    form.action = letParentValueFrom(form.action);
    if (formContent) {
        formContent.focus();
        formContent.placeholder = getDatum(formContent, 'hint') || "";
    }
    if (formParent) {
        formParent.value = "";
    }
    offEventDefault(e);
}

function onClickReply(e) {
    let form = getFormElement('comment');
    if (!form) {
        return; // Skip if comment form does not exist!
    }
    let $ = getParent(e.target, '[data-task=reply]'),
        comment = $ && getParent($, '.comment'),
        commentFooter = getElement('.comment-footer', comment),
        formContent = form['comment[content]'],
        formParent = form['comment[parent]'], v;
    if (!$ || !comment.contains($)) {
        return; // Skip if reply button does not exist or does exist but outside the comment’s container!
    }
    // Insert comment form before the comment’s footer …
    if (commentFooter) {
        setPrev(commentFooter, setClass(form, 'in-reply'));
    // … or append it to the comment’s container!
    } else {
        setChildLast(comment, setClass(form, 'in-reply'));
    }
    form.action = setParentValueTo(form.action, v = getParentValueFrom($.href));
    if (formContent) {
        formContent.focus();
        formContent.placeholder = $.title;
    }
    if (formParent) {
        formParent.value = v;
    }
    offEventDefault(e);
}

function setParentValueTo(href, v) {
    href = u(href);
    href.searchParams.set('parent', v);
    return href + "";
}

function u(href) {
    return new URL(href);
}

onEvent('click', D, onClickCancel, true);
onEvent('click', D, onClickReply, true);