(function () {
    'use strict';
    var isArray = function isArray(x) {
        return Array.isArray(x);
    };
    var isDefined = function isDefined(x) {
        return 'undefined' !== typeof x;
    };
    var isInstance = function isInstance(x, of ) {
        return x && isSet( of ) && x instanceof of ;
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
    var toValue = function toValue(x) {
        if (isArray(x)) {
            return x.map(function (v) {
                return toValue(v);
            });
        }
        if (isNumeric(x)) {
            return toNumber(x);
        }
        if (isObject(x)) {
            for (var k in x) {
                x[k] = toValue(x[k]);
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
    var fromValue = function fromValue(x) {
        if (isArray(x)) {
            return x.map(function (v) {
                return fromValue(x);
            });
        }
        if (isObject(x)) {
            for (var k in x) {
                x[k] = fromValue(x[k]);
            }
            return x;
        }
        if (false === x) {
            return 'false';
        }
        if (null === x) {
            return 'null';
        }
        if (true === x) {
            return 'true';
        }
        return "" + x;
    };
    var D = document;
    var W = window;
    var getAttribute = function getAttribute(node, attribute, parseValue) {
        if (parseValue === void 0) {
            parseValue = true;
        }
        if (!hasAttribute(node, attribute)) {
            return null;
        }
        var value = node.getAttribute(attribute);
        return parseValue ? toValue(value) : value;
    };
    var getElements = function getElements(query, scope) {
        return (scope || D).querySelectorAll(query);
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
    var letAttribute = function letAttribute(node, attribute) {
        return node.removeAttribute(attribute), node;
    };
    var letClass = function letClass(node, value) {
        return node.classList.remove(value), node;
    };
    var setAttribute = function setAttribute(node, attribute, value) {
        if (true === value) {
            value = attribute;
        }
        return node.setAttribute(attribute, fromValue(value)), node;
    };
    var setChildLast = function setChildLast(parent, node) {
        return parent.append(node), node;
    };
    var setClass = function setClass(node, value) {
        return node.classList.add(value), node;
    };
    var setPrev = function setPrev(current, node) {
        return getParent(current).insertBefore(node, current), node;
    };
    var theLocation = W.location;
    var offEventDefault = function offEventDefault(e) {
        return e && e.preventDefault();
    };
    var onEvent = function onEvent(name, node, then, options) {
        if (options === void 0) {
            options = false;
        }
        node.addEventListener(name, then, options);
    };
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
                parent && letAttribute(parent, 'value');
                offEventDefault(e);
            });
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
})();