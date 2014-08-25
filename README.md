Rating (plugin for Omeka)
=========================

About
-----

[Rating] is a plugin for [Omeka] that adds a widget to allow users to rate items
or other records instantly via Ajax.

The plugin uses [RateIt], a jQuery (star) rating plugin, whose main qualities
are: light, fast, progressive enhancement, touch support, customizable,
unobtrusive JavaScript, use of HTML5 data attributes, RTL support, ARIA and
keyboard support.


Installation
------------

Uncompress files and rename plugin folder "Rating".

Then install it like any other Omeka plugin and follow the config instructions.

The plugin can use [GuestUser] if it is installed.

* Note about privacy

To avoid multiple ratings by the same visitor (not identified user), the IP is
checked. This option can be enabled or disabled for privacy purpose. When
disabled, no check is done for anonymous visitors. Furthermore, IP can be set
clear or hashed (md5).


Displaying Rating Widget
------------------------

The widget can be displayed via three mechanisms.

* Hooks

The plugin will add the rating widget automatically on `items/show` and
`items/browse` pages via the hook, if the current user has right to use it:

```php
fire_plugin_hook('public_items_show', array('view' => $this, 'item' => $item));
```

* Helpers

If you need more flexibility, in particular for records other than items
(collections, files, exhibits, exhibit pages, simple pages), you can use helper:

```php
// Attach css and js before calling head() (or add tags anywhere).
queue_css_file('rating');
queue_js_file('RateIt/jquery.rateit.min');

// Anywhere in the page. Can be called multiple times with different records.
echo $this->rating()->widget($record, $user, $display);

// Anywhere in the page after the last rating widget.
echo common('rating-js');
```

Rights are automatically managed. The javascript codes are managed separately to
get a lower code, in particular in browse pages. `$display` is an ordered array
that contains parameters to choose the type of widget (see below). Default is to
show the average score of the record.

If you just need the average score or the specific rate of a user, use:

```php
echo $this->rating()->score($record);
echo $this->rating()->rate($record, $user);
```

* Shortcodes

[Shortcodes] are supported (Omeka 2.2 or above). Some illustrative examples:

```
[rating record_id=1]
[rating record_type='Collection' record_id=1 user=1]
[rating record_type='Item' record_id=1 display="score text, rate visual"]
[rating record_type='Item' record_id=1 display="rate visual, score text" user=2]
```

Options are:
- `record_type`: an Omeka record type , e.g. "Item" (default) or "Collection".
- `record_id` (required): the identifier of the record.
- `user`: allows to get the rate of the specified user. If not set, the current
user is selected. Not used with score.
- `display` (optional): ordered comma separated options to choose the
form of the widget:
  - "score": raw value without css (default if no user; exclusive from other
  ones).
  - "score text": just the score with css.
  - "score visual": visual average score and count of ratings for the record.
  - "rate": raw value without css (default if user is set; exclusive from other
  ones).
  - "rate text": just the rate with css
  - "rate visual": widget that allows user to rate the record (if allowed).

As the helper, rights are automatically managed. Javascript and css are added
automatically too. Visual and text results can be themed.


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and database regularly so you can
roll back if needed.


Troubleshooting
---------------

See online issues on the [Rating issues] page on GitHub.


License
-------

* Rating Plugin

This plugin is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software's author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user's
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


* RateIt widget

The [RateIt] jQuery (star) rating plugin is released under the [MIT licence] (MIT).

Copyright (c) 2013 Gideon Junge

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


Contact
-------

Current maintainers:
* Daniel Berthereau (see [Daniel-KM] on GitHub)


Copyright
---------

* Copyright Daniel Berthereau, 2013-2014
* Copyright Gideon Junge, 2013-2014 ([RateIt])


[Omeka]: https://omeka.org "Omeka.org"
[RateIt]: https://rateit.codeplex.com
[Rating]: https://github.com/Daniel-KM/Rating
[Shortcodes]: http://omeka.org/codex/Shortcodes
[Rating issues]: https://github.com/Daniel-KM/Rating/issues
[GuestUser]: https://github.com/omeka/plugin-GuestUser
[CeCILL v2.1]: http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html "GNU/GPL v3"
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[MIT licence]: http://opensource.org/licenses/MIT
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
