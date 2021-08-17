Comment Extension for [Mecha](https://github.com/mecha-cms/mecha)
=================================================================

![Comment](https://user-images.githubusercontent.com/1669261/110820519-c1ffd500-82c1-11eb-9d81-260e8ddb24ee.png)

---

Release Notes
-------------

### 1.20.0

 - Added `comment-body`, `comment-footer`, `comment-form`, `comment-form-tasks`, `comment-header`, `comment-tasks`, `comments-body`, `comments-footer`, `comments-header` and `comments-tasks` hooks.
 - Improved comment pagination feature. If there isn&rsquo;t any comment pagination offset appear in the URL, by default, the current comments chunk in the comments section will be the last comments page.
 - Improved comments markup. They are now uses combination of `<article>` and `<section>` tags.
 - Moved user-related features to a [separate extension](https://github.com/mecha-cms/x.user.comment).
 - Removed `comment.footer` hook (has been replaced by `comment-tasks` hook).
 - [@mecha-cms/mecha#96](https://github.com/mecha-cms/mecha/issues/96)

### 1.19.2

 - [@mecha-cms/mecha#94](https://github.com/mecha-cms/mecha/issues/94)

### 1.19.1

 - Fixed bug of default avatar not showing in comments due to the [user](https://github.com/mecha-cms/x.user) extension that does not exist.
 - Fixed bug of invalid `parent` query string value that generates a new empty comment (#3)
 - Make sure to disable the comment form if comments are closed (#4)

### 1.19.0

 - Removed default spam filter. This feature can be created as a separate extension (#2)

### 1.18.1

 - Improved default XSS filter. Now will also filter HTML attribute names started with `on` and HTML attribute values started with `javascript:`.

### 1.18.0

Start from this version, default comment type will be set to `HTML`. The default comment type value can be modified through `x.comment.page.type` setter/getter. Omitting this value will make the default comment type inherit to the current page type.

Please note that the default XSS filter currently only applies if the comment type is set to `HTML` or `text/html` explicitly. When you set the default comment type to other than `HTML` and `text/html` (or when you omit the default comment type, where the current page type is not set to `HTML` or `text/html`), then the default XSS filter will not work. You must make your own XSS filter specific to each comment type.

Some examples of custom XSS filters already exist in [markdown.comment](https://github.com/mecha-cms/x.markdown.comment) and [b-b-code.comment](https://github.com/mecha-cms/x.b-b-code.comment) projects where all HTML tags will be removed in the comment body unless it is written inside the code block markup.

### 1.17.0

 - Added posibility to insert hint message on every comment form field.
 - Simplified comment form markup.

### 1.16.1

 - Differentiate between `0 Comments`, `1 Comment`, `%d Comment` and `%d Comments` translation items.
 - Redirect to the correct page URL immediately whenever users try to access the form action URL directly (using the `GET` request type).

### 1.16.0

 - Added `target` attribute on every comment reply link (and comment cancel link as well).
 - Added comment pagination feature.

### 1.15.4

 - Updated layout.

### 1.15.3

 - Added ability to specify the depth level of comment replies.
 - Added log-in button as simple integration with user extension.
 - Removed `comments` property as a way to enable or disable comment feature on specific page. Store your custom comment state in the `state` property from now on.
