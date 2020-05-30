Comment Extension for Mecha
===========================

Release Notes
-------------

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
