Comment Extension for Mecha
===========================

Release Notes
-------------

### 1.18.0

Start from this version, default comment type will be set to `HTML`. The default comment type value can be modified through `x.comment.page.type` setter/getter. Omitting this value will make the default comment type inherit to the current page type.

Please note that the default XSS filter currently only applies if the comment type is set to `HTML` or `text/html` explicitly. When you set the default comment type to other than `HTML` and `text/html` (or when you omit the default comment type, where the current page type is not set to `HTML` or `text/html`), then the default XSS filter will not work. You must make your own XSS filter specific to each comment type.

Some examples of custom XSS filters already exist in [markdown.comment](https://github.com/mecha-cms/x.markdown.comment) and [b-b-code.comment](https://github.com/mecha-cms/x.b-b-code.comment) projects where all HTML tags will be removed in the comment body unless it is written inside the code block markup.

### 1.17.0

 - Simplified comment form markup.
 - Added posibility to insert hint message on every comment form field.

### 1.16.1

 - Differentiate between `0 Comments`, `1 Comment`, `%d Comment` and `%d Comments` translation items.
 - Redirect to the correct page URL immediately whenever users try to access the form action URL directly (using the `GET` request type).

### 1.16.0

 - Added comment pagination feature.
 - Added `target` attribute on every comment reply link (and comment cancel link as well).

### 1.15.4

 - Updated layout.

### 1.15.3

 - Added ability to specify the depth level of comment replies.
 - Added log-in button as simple integration with user extension.
 - Removed `comments` property as a way to enable or disable comment feature on specific page. Store your custom comment state in the `state` property from now on.
