(function () {
    'use strict';
    var isArray = function isArray(x) {
        return Array.isArray(x);
    };
    var isDefined = function isDefined(x) {
        return 'undefined' !== typeof x;
    };
    var isInstance = function isInstance(x, of) {
        return x && isSet(of) && x instanceof of ;
    };
    var isNull = function isNull(x) {
        return null === x;
    };
    var isNumeric = function isNumeric(x) {
        return /^-?(?:\d*.)?\d+$/.test(x + "");
    };
    var isObject = function isObject(x, isPlain) {
        if (isPlain === void 0) {
            isPlain = true;
        }
        if ('object' !== typeof x) {
            return false;
        }
        return isPlain ? isInstance(x, Object) : true;
    };
    var isSet = function isSet(x) {
        return isDefined(x) && !isNull(x);
    };
    var toNumber = function toNumber(x, base) {
        if (base === void 0) {
            base = 10;
        }
        return base ? parseInt(x, base) : parseFloat(x);
    };
    var _toValue = function toValue(x) {
        if (isArray(x)) {
            return x.map(function (v) {
                return _toValue(v);
            });
        }
        if (isNumeric(x)) {
            return toNumber(x);
        }
        if (isObject(x)) {
            for (var k in x) {
                x[k] = _toValue(x[k]);
            }
            return x;
        }
        if ('false' === x) {
            return false;
        }
        if ('null' === x) {
            return null;
        }
        if ('true' === x) {
            return true;
        }
        return x;
    };
    var fromJSON = function fromJSON(x) {
        var value = null;
        try {
            value = JSON.parse(x);
        } catch (e) {}
        return value;
    };
    var D = document;
    var getAttribute = function getAttribute(node, attribute, parseValue) {
        if (parseValue === void 0) {
            parseValue = true;
        }
        if (!hasAttribute(node, attribute)) {
            return null;
        }
        var value = node.getAttribute(attribute);
        return parseValue ? _toValue(value) : value;
    };
    var getDatum = function getDatum(node, datum, parseValue) {
        if (parseValue === void 0) {
            parseValue = true;
        }
        var value = getAttribute(node, 'data-' + datum, parseValue),
            v = (value + "").trim();
        if (parseValue && v && ('[' === v[0] && ']' === v.slice(-1) || '{' === v[0] && '}' === v.slice(-1)) && null !== (v = fromJSON(value))) {
            return v;
        }
        return value;
    };
    var getElement = function getElement(query, scope) {
        return (scope || D).querySelector(query);
    };
    var getFormElement = function getFormElement(nameOrIndex) {
        return D.forms[nameOrIndex] || null;
    };
    var getParent = function getParent(node, query) {
        if (query) {
            return node.closest(query) || null;
        }
        return node.parentNode || null;
    };
    var hasAttribute = function hasAttribute(node, attribute) {
        return node.hasAttribute(attribute);
    };
    var letClass = function letClass(node, value) {
        return node.classList.remove(value), node;
    };
    var setChildLast = function setChildLast(parent, node) {
        return parent.append(node), node;
    };
    var setClass = function setClass(node, value) {
        return node.classList.add(value), node;
    };
    var setPrev = function setPrev(current, node) {
        return current.before(node), node;
    };
    var offEventDefault = function offEventDefault(e) {
        return e && e.preventDefault();
    };
    var onEvent = function onEvent(name, node, then, options) {
        node.addEventListener(name, then, options);
    };

    function getParentValueFrom(href) {
        return u(href).searchParams.get('parent');
    }

    function letParentValueFrom(href) {
        href = u(href);
        href.searchParams.delete('parent');
        return href + "";
    }

    function onClickCancel(e) {
        var form = getFormElement('comment');
        if (!form) {
            return; // Skip if comment form does not exist!
        }
        var $ = getParent(e.target, '[data-task=cancel]'),
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
        var form = getFormElement('comment');
        if (!form) {
            return; // Skip if comment form does not exist!
        }
        var $ = getParent(e.target, '[data-task=reply]'),
            comment = $ && getParent($, '.comment'),
            commentFooter = getElement('.comment-footer', comment),
            formContent = form['comment[content]'],
            formParent = form['comment[parent]'],
            v;
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
})();