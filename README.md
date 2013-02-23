# EVANDER

Evander provides a set of loosely coupled, highly extensible libraries intended
for lightweight application development.

## Overview

A nimble and flexible framework with *freedom* at its base, Evader rethinks the
purpose of a framework. It does not enforce  particular architectural patterns 
or strategies but instead is designed to give developers what they want when 
they want it, and to stay out of their way when they don't.

Developers are free to modify Evander in any way they find necessary, so long as
their modifications remain under the original license. However, modifications 
to Evander files themselves are generally discouraged. Instead, hooks in 
`hook/` and the MVC under `mvc/` should be used where possible to separate 
application code from Evander itself. Sticking to these mechanisms will ensure 
a smoother update process for future versions of Evander.

Evander is compatible with PHP 5.1.2 or above; for full functionality, it 
should be run with PHP 5.3 or above.

### Credits

Evander is written and maintained by Eric Bollens <ebollens@ucla.edu>.

### License

Evander is open-source software licensed under the Apache License, Version 2.0. 

A copy of the License may be found at:

> http://www.apache.org/licenses/LICENSE-2.0

## Getting Started

Evander is designed for two basic architectural approaches:

* **MODEL-VIEW-CONTROLLER** Under this scheme, each page is encapsulated under a 
controller object method. If a user requests `index.php?main/index`, logic 
under the method `Main_Controller->index()` determines the page output. This is 
the default paradigm for applications built under Evander, as it provides the 
cleanest separation of business logic, output and data models.
* **SCRIPTS** The standard PHP scripts approach is an equally valid approach to
application architecture leveraging Evander. Such applications define a 
different PHP file for each page in the application. To use Evander within PHP 
scripts, simply include `/core.php` instead of `/mvc.php`, the latter of which 
is included by the default `/www/index.php` file to instantiate the MVC. With 
the exception of the functionality provided by MVC and the files under `mvc/`, 
all other features of Evander are fully available in this model.

Once an approach has been selected, the next question is whether or not to use
the template engine provided by Evander. By default, it is enabled, as it is
necessary for `Head_Assets` and `Body_Assets`, among others.

To disable templating universally across Evander, change the template config 
variable in `config.php`:

```php
$CONFIG->use_template = false;
```

Templating can also be disabled for any individual script through the call:

```php
Template::disable();
```

Similarly, if disabled in the config, templating can be re-enabled for a page:

```php
Template::enable();
```

For a more detailed description of template functionality, see "FEATURES".

Once decisions have been made about MVC and the template engine, the only
remaining task to get started with Evander is to modify other variables under
`config.php`.

Whether leveraging the built-in MVC or including `core.php` at the top of each
script file, all the functionality of Evander is available once the 
configuration variables have been defined.

At the most basic level, this functionality includes automatic autoloading of 
classes, output buffering, customizable error and exception handling, views, 
templating, and more (see "FEATURES" for a full list of functionality).

For security purposes, it is recommended that `/www` is configured as the web
root and that no other directories or files are made web-accessible.

## Features

At this point in time, Evander includes the following features:

### Class Autoloading

The `require()` and `include()` functions are cumbersome and completely
unnecessary, and Evander does away with them by defining an autoload
function `__autoload_framework()` [`lib/autoload.php`].

When a class `{CLASS}` is called for the first time, Evander will attempt
to load it from `lib/{CLASS}.php.` If the class is suffixed with `_Model`
or `_Exception`, it will attempt to load it from `lib/model/{CLASS}.php`
or `lib/exception/{CLASS}.php` respectively. For interfaces suffixed with
`_Interface`, meanwhile, it will attempt to load it from 
`lib/interface/{INTERFACE}.php`.

If the autoloader cannot find a given class, an `E_FATAL` error will be
thrown. Undefined interfaces and exceptions are an exception to this.
In these cases, the autoloader respectively creates an empty interface 
or a base exception class.

NOTE: If MVC is being used, the autoloader will first try to find the
class definitions within `mvc/lib/` before it checks within `lib/`. It
uses the same scheme for special suffixes. This logic is contained 
within `__autoload_mvc()` [`lib/mvc.php`].

### Output Buffering

Transparent to the application, when a script writes to standard out, it
does not immediately write to the screen. Instead, it is stored in an 
output buffer and is only output when the script concludes.

This makes it possible to manipulate or erase output to the screen at
any point before the end of execution by calling static methods of the 
`Output` class [`lib/output.php`].

### Bootstrap

The `Bootstrap` class [`lib/bootstrap.php`] is responsible for the
initialization and shutdown of all scripts under Evander.

For initialization, the bootstrap starts up the output buffer and 
database connection manager, as well as loading the `$CONFIG` global. It
also provides a hook for application-specified logic during start up by 
adding logic to `Bootstrap_Hook->init()` [`hook/bootstrap_hook.php`].

For shutdown, the bootstrap's primary responsibility is to actually
render the output to the page captured in the output buffer. It also
provides several hooks at this point for application-specific logic. To
add logic before the output buffer is rendered to the screen, modify
`Bootstrap_Hook->prerender()`. Similarly, a hook also exists for after the
output buffer is output, `Bootstrap_Hook->postrender()`, and for at the 
conclusion of the shutdown routine, `Bootstrap_Hook->shutdown()`.

### Hooks

A hook is a way of injecting user-specified code at a particular point
in execution without having to place it directly within the main file
of the executing code. The `Hook` object [`lib/hook.php`] provides a simple
way of calling this functionality.

To leverage an existing hook, simply modifying the code under hook/ for
the specified `{HOOK}` under the file `hook/{HOOK}_hook.php`. Examples
within Evander itself include the hooks defined for `Bootstrap` and `MVC`.

Hook classes are defined with the `_Hook` suffix, and should be invoked
by the HOOK object by their name (neglecting the suffix). The file that
defines the hook is under the `hook/` directory with a name including the
suffix. For example, `Hook::execute('Bootstrap', 'Init')` calls the `init()`
instance method of `Bootstrap_Hook` defined in `hook/bootstrap_hook.php`.

In the event that a hook is not defined, `Hook::execute()` will throw a 
`Hook_Exception`. To ignore this, wrap `Hook::execute()` call as follows:

```php
try
{
	Hook::execute('Bootstrap', 'Init');
}catch(Hook_Exception $e){}
```

This is generally recommended, and both Bootstrap and MVC do as such to
make the hook definitions optional.

### Error Handling

PHP does not handle fatal errors or exceptions in a reasonable way, and
in fact does not even provide a built-in mechanism for catching fatal
errors such as `E_PARSE`, `E_CORE_ERROR`, or `E_COMPILE_ERROR`.

The `Error` object [`lib/error.php`] modifies this behavior to improve 
handling of both uncaught exceptions and the following fatal errors:

* `E_ERROR`
* `E_RECOVERABLE_ERROR`
* `E_USER_ERROR`
* `E_PARSE`
* `E_CORE_ERROR`
* `E_COMPILE_ERROR`

In order to catch the latter three, add the following to `.htaccess`:

```
php_value auto_prepend_file shutdown.php
```

The default behavior of the error handler is to pass information related
to the error into the error/details view [`view/error/details.php`] and
display this rather than regular page output. It will empty the output
buffer before doing this to ensure that partial output of the script
that crashed is not written to the screen.

### Paths

While a `DIR_ROOT` constant is available under Evander, to simplify the
construction of file system paths, the `Path` library provides static
methods that output paths under hook, lib, mvc, mvc view, template,
view (possibly considering mvc views) and the root itself.

### URLs

While the `$CONFIG->site_url` variable is available under Evander, to 
simplify the construction of web urls, the URL library provides static
methods that output urls under asset, css, img, js, template and the
root itself.

### Templates

The template engine wraps any content page, whether generated under MVC 
or the standalone script paradigm, within a template view. The `Template`
object allows a user to modify many parameters related to the template
engine.

To enable the template engine, set the config variable [`config.php`]:

```php
$CONFIG->use_template = true;
```

Conversely, to disable it, set the config variable false [`config.php`]:

```php
$CONFIG->use_template = false;
```

By default, the template engine is running. It does not add significant
overhead, while it does provide added functionality for `Head_Assets` and
`Body_Assets`, which otherwise must be handled in a custom manner if used.

The Template object also includes static methods to disable or enable 
the engine directly via `Template::enable()` or `Template::disable()`. It
might be useful, for example, to disable the template engine on a page
that outputs XML or JSON so that you can still leverage Evander on the
page and while still using the template engine on other pages.

By default, the template engine uses `assets/template/default.php`. 
Similarly, if the template is disabled, it actually just leverages a
different, empty file `assets/template/empty.php`. One may define other
template files and leverage them by calling `Template::set_template()`
with the file name (excluding `.php`).

Variables may be passed to the template as follows:

```php
Template::set_var($name, $value);
```

Once set, the variable is then accessible in the template file with the
name $name. For example, if `Template::set_var('var', 'val')` is called, 
then the variable `$var === 'val'` in the template file. Such variable
definitions are local to the scope of the template file and will not
collide with other variables in page content or views.

By default, several special variables are available in a template file:

* `$HEAD_ASSETS` Generated by the Head_Assets object and should be placed in 
any template file somewhere within the `head` HTML entity.
* `$BODY_ASSETS` Generated by the Body_Assets object and should be placed in 
any template file within `body`, generally right before the `</body>` tag.
* `$CONFIG` The config global as defined in `config.php`.

The Template object also includes accessors to get the current template
file and variables.

### Views

The `View` class provides a simple mechanism to encapsulate a PHP file,
generally intended for output, within an object. This encourages reuse
and contains the scope of variable definitions that might otherwise have
to be defined as global in scope to reach the necessary file.

A view can be constructed as follows:

```php
$view = new View($viewfile);
```

The `$viewfile` should be the name of a file under `asset/view`.

NOTE: In the case that MVC is enabled, view behavior is overridden so 
that a view may be defined in `mvc/view/`. Under this case, the `View` 
object default to the `mvc/view/` if a file is defined in both places. 
This behavior may be overridden by calling `new View($viewfile, false)` to 
force the view to use the view in `view/` rather than `mvc/view/`.

Once a view file has been constructed, it operates in a manner similar
to template in terms of variable encapsulation. A variable under the
scope of a view may be defined as follows:

```php
$view->var = 'val';
```

This makes `$var === 'val'` within the view file. Such variable
definitions are local to the scope of the view file and will not collide
with other variables in other page content, views or the template.

Once any necessary variables have been defined for the view, it should
then be rendered to the page as follows:

```php
echo $view->render();
```

As with other functionality, views are not required, but they provide a
simple way to encapsulate output, and they are an intrinsic part of the
model-view-controller paradigm for those leveraging it.

### Model-View-Controller (MVC)

The MVC object is responsible for adding logic to the script routine to
handle the model-view-controller paradigm.

To enable the MVC, set its config variable [`config.php`]:

```php
$CONFIG->use_mvc = true;
```

To disable the MVC, set its config variable false [`config.php`]:

```php
$CONFIG->use_mvc = false;
```

The MVC object can also be run on only a single page by disabling it (as
described above) and then calling `MVC::init()` and `MVC::execute()` on the
page where it should be used. This is most useful if there is a desire
to use MVC on some pages and not others, in which case the `use_mvc` 
config variable should be disabled and then `index.php` should explicitly
call `MVC::init()` and `MVC::execute()`. This will have the effect that
`index.php` will serve the MVC and then other pages can operate as 
individual scripts (see "OVERVIEW" for a description of the difference).

Once the MVC is in use, it assumes page paths of the following form:

```php
index.php?controller/method
```

For example, if `index.php?main/index` is requested, MVC constructs a
`Main_Controller` object as defined in `mvc/controller/main.php` and then
calls the `Main_Controller->index()` method.

Parameters can be passed into the controller method by adding additional
segments to the request path such as the following example:

```php
index.php?main/index/val1/val2
```

In this case, the prototype for the class would look like this:

```php
class Main_Controller
{
	public function index($var1, $var2)
	{
		// ...
	}
}
```

Within `Main_Controller->index()`, based on the above request, then 
`$var1 === 'val1'` and `$var2 === 'val2'`.

### Head and Body Assets

`Head_Assets` [`lib/head_assets.php`] and `Body_Assets` [`lib/body_assets.php`] 
are responsible for collecting files that should be included in the
`head` and at the end of the `body` respectively and then pushing them
into the template, if the template is used.

In the case of a template, the contents collected are available as:

* `$HEAD_ASSETS`
* `$BODY_ASSETS`

For a more detailed description of this process, see "TEMPLATE".

They can also be retrieved directly via static methods:

* `Head_Assets::render()`
* `Body_Assets::render()`

As for setting assets to be rendered as such, the Head_Assets object
accepts CSS and JS files rendered out as `link` and `script` tags
respectively, while the `Body_Assets` object accepts only JS files.
`Head_Assets` should be used to define files that are required by the
onLoad event, while `Body_Assets` should be used for custom Javascript
that may be cumbersome in size but that isn't contingent on the load
event.

### DB Connection and Result Abstractions

`DB` [`lib/db.php`] is responsible for managing database connections. By
default, one connection is defined 

### Active Record Model

The `Active_Record_Model` [`lib/model/active_record_model.php`] encapsulates
either an existing row or a new row in a database table.

To construct an active record model object bounded to an existing row:

```php
$rec = new Active_Record_Model($table, $key);
```

If a column besides `id` is used as the primary key, or at minimum a
unique key to identify the row, then another parameter may be specified:

```php
$rec = new Active_Record_Model($table, $key, $column);
```

To define an unbound (new) record, simply drop the $key syntax:

```php
$rec = new Active_Record_Model($table);
```

Again, if a different column besides `id` is used as the primary key, 
then specify it as follows:

```php
$rec = new Active_Record_Model($table, null, $column);
```

Once defined, a series of operations may be performed against the record
such as checking whether the row exists, accessing column values, 
modifying column values, and adding or deleting the row.

The Active_Record_Model makes several concessions for performance:

* **Buffering** The model buffers changes until either `update()` is called for 
an existing record or `add()` is called for a new record.
* **Lazy Load** The contents of the row are not actually retrieved from the 
database until such time as the first value is requested. This extends to the 
point that one may even blindly update a row with update() without ever 
fetching current values.
* **Demand Load** If the contents of a row are already known, one can use the 
load() method to pass these values into the model directly. This should be used 
when building a large set of records so as to avoid excess DBq.

To extend the `Active_Record_Model` for a particular table, it is possible
to define a new object and simply extend the construct.

```php
class Table_Model
{
	public function __construct($id, $column = 'id')
	{
		parent::__construct('table', $id, $column);
	}
}
```

An object of type `Table_Model` will behave just like `Active_Record_Model`
except that it will always be found to `table` and does not need to
explicitly define this.

### Filesystem Models

*documentation todo*

### Input Handlers

*documentation todo*
