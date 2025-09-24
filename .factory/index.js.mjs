import {
    D,
    getDatum,
    getElement,
    getFormElement,
    getParent,
    letClass,
    setChildLast,
    setClass,
    setPrev,
    theLocation
} from '@taufik-nurrohman/document';

import {
    offEventDefault,
    onEvent
} from '@taufik-nurrohman/event';

let placeholderDefault;

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
    if (getParentValueFrom(theLocation.href)) {
        return; // Skip if current URL contains `parent` query!
    }
    let $ = getParent(e.target, 'a[href$="#comment"]:not([href*="&parent="],[href*="?parent="])'),
        comments = $ && getParent($, '.comments[data-status]'),
        commentsFooter = getElement('.comments-footer', comments),
        formElements = form.elements,
        formElementsContent = formElements.content,
        formElementsParent = formElements.parent;
    if (!$ || !comments.contains($)) {
        return; // Skip if cancel button does not exist or does exist but outside the root comment(s)’ container!
    }
    // Append comment form to the root comment(s)’ footer or to the root comment(s)’ container!
    setChildLast(commentsFooter || comments, letClass(form, 'in-reply'));
    form.action = letParentValueFrom(form.action);
    if (formElementsContent) {
        formElementsContent.focus();
        if (placeholderDefault) {
            formElementsContent.placeholder = placeholderDefault;
        }
    }
    if (formElementsParent) {
        formElementsParent.value = "";
    }
    offEventDefault(e);
}

function onClickReply(e) {
    let form = getFormElement('comment');
    if (!form) {
        return; // Skip if comment form does not exist!
    }
    let $ = getParent(e.target, 'a[href$="#comment"]:is([href*="&parent="],[href*="?parent="])'),
        comment = $ && getParent($, '.comment'),
        commentFooter = getElement('.comment-footer', comment),
        formElements = form.elements,
        formElementsContent = formElements.content,
        formElementsParent = formElements.parent, v;
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
    if (formElementsContent) {
        if (!placeholderDefault) {
            placeholderDefault = formElementsContent.placeholder;
        }
        formElementsContent.focus();
        formElementsContent.placeholder = $.title;
    }
    if (formElementsParent) {
        formElementsParent.value = v;
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