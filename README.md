# Statical

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/johnstevenson/statical/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/johnstevenson/statical/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/johnstevenson/statical/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/johnstevenson/statical/?branch=master)
[![Build Status](https://secure.travis-ci.org/johnstevenson/statical.png)](http://travis-ci.org/johnstevenson/statical)

PHP static proxy library.
## Contents
* [About](#About)
* [Usage](#Usage)
* [License](#License)

<a name="About"></a>
## About

**Statical** is a tiny PHP library that enables you to call class methods from a static accessor, so
the static call to `Foo::doSomething()` actually invokes the `doSomething()` method of a specific class
instance. To show a more concrete example:

```php
// Normal access
$view = $app->get('view');
$view->render('mytemplate', $data);

// Using Statical
View::render('mytemplate', $data);
```

This may or may not be a good thing and depends entirely on your point of view and requirements.

### How it Works
Everything runs through a `Statical\Manager` instance which needs at least three pieces of data to register
each static proxy:

* an alias
* a proxy class
* a target class

An **alias** is the short name you use to access your *target class* instance and call its
methods, for example `Foo`, `View` or whatever. It is actually an alias to your *proxy class*,
which in turn calls your *target class* instance.

A **proxy class** is a static class that extends the abstract `Statical\BaseProxy` class and
passes all unresolved calls through its parent's __callStatic() magic method. It is usually just
an empty class, like this:


```
class FooProxy extends \Statical\BaseProxy {}
```

A **target class** is the instantiated class whose methods you wish to call. It can be either:

* an actual instance
* a closure invoking an instance
* a reference to an object in a container or service locator.

This data is then registered with the Manager, using `addProxyInstance` or `addProxyService`,
which uses a lazy class-alias autoloader to resolve your method calls from the *alias* to
your *proxy class*. This calls back to the Manager to get your *target class* instance,
which is then called with the method.

### Namespaces
By default, each static proxy is registered in the global namespace. This means that any calls to
`Foo` will not work in a namespace unless prefixed with a backslash `\Foo` or there is
a *use* statement referencing the proxy class, for example `use Name\Space\FooProxy as Foo`.

**Statical** includes a powerful namespacing feature which allows you to add namespace patterns for
an alias (or even any alias). For example `addNamespace('Foo', 'App\\Library\\*')` means you can
call `Foo` in any *App\\Library\\...* namespace.

### Features
A few features in no particular order. Please see the [documentation][wiki] for more information.

- **Statical** creates a static proxy to itself, aliased as *Statical* and available in any namespace. Now you can call the Manager
with `Statical::addProxyService(...)` or whatever. This feature can be disabled or modified as required.

- You can use multiple containers when adding proxy services to the Manager.

- If you pass a closure as a proxy instance, it will be invoked once to resolve the target
instance. You can get a reference to this instance, or in fact any target class, by calling the
*getInstance()* method on your alias, for example `Foo::getInstance()`.

- **Statical** is test-friendly. If you register a container then it is used to resolve the
target instance for every proxied call, allowing you to swap in different objects. You can also
replace a proxy by registering a different instance/container with the same alias.


<a name="Usage"></a>
## Usage
If you downloaded the library through [composer][composer] then you must add
`require 'vendor/autoload.php'` somewhere in your bootstrap code. Otherwise you must point a PSR
autoloader to the `src` directory. Below are some quick examples. Firstly, using a class
instance:

```php
<?php
$alias = 'Foo';
$proxy = 'Name\\Space\\FooProxy';
$instance = new MyClass();

# Create our Manager
$manager new Statical\Manager();

# Add proxy instance
$manager->addProxyInstance($alias, $proxy, $instance);

# Now we can call MyClass methods via the static alias Foo
Foo::doSomething();
```

For a container or service locator you would do the following:

```php
<?php
$alias = 'Foo';
$proxy = 'Name\\Space\\FooProxy';

# Add MyService to the container/service locator
# Note that in this case the id is not related to the alias
$id = 'bar';
$container->set($id, function ($c) {
  return new MyService($c);
});

# Create our Manager
$manager new Statical\Manager();

# Add proxy service
$manager->addProxyService($alias, $proxy, $container, $id);

# MyService is resolved from the container each time Foo is called
Foo::doSomething();

```

As above but where the container id is a lower-cased version of the alias:

```php
<?php
$alias = 'View';
$proxy = 'Name\\Space\\ViewProxy';

# Add MyView to the container/service locator
$id = 'view';
$container->set($id, function ($c) {
  return new MyView($c);
});

# Create our Manager
$manager new Statical\Manager();

# Add proxy service
$manager->addProxyService($alias, $proxy, $container);

# MyView is resolved from the container each time View is called
View::doSomething();

```
Full usage [documentation][wiki] can be found in the Wiki.

<a name="License"></a>
## License

Statical is licensed under the MIT License - see the `LICENSE` file for details


  [composer]: http://getcomposer.org
  [wiki]:https://github.com/johnstevenson/statical/wiki/Home

