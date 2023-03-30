# Tutorial: registering WordPress hooks using PHP attributes

In this tutorial, I will demonstrate how to utilize WordPress hooks with PHP attributes. Although it may not be necessary for every simple plugin, employing PHP attributes can be particularly useful in plugins with a large codebase.

Note: The techniques presented in this tutorial are exclusively available in PHP 8! Therefore, make sure to adjust the 'Requires PHP' field in the plugin header accordingly.

## Why is registering hooks troublesome?

Using an object-oriented programming (OOP) approach for WordPress plugins has often caused difficulties when it comes to hook registration. There are various methods available, and I will present three commonly used options.

### Option 1: Using the constructor

```php
class MyClass
{
  public function __construct()
  {
    add_filter('body_class', [$this, 'addBodyClass']);
  }

  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}

new MyClass();
```

Using the constructor of a class for registering hooks can create issues when adding unit testing. Moreover, it can be considered a misuse of the constructor, which should solely be used for initializing an object's properties upon its creation.

### Option 2: Register from outside of the class

```php
class MyClass
{
  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}

$myClass = new MyClass();
add_filter('body_class', [$myClass, 'addBodyClass']);
```

That's an improvement - we no longer misuse the constructor of the class. However, in large plugins, we may end up with a long list of class instantiations, and in large classes, the hook registration can be far from the corresponding method, which can be inconvenient and unclear.

### Option 3: Register from static method

```php
class MyClass
{
  public static function register(): void
  {
    $self = new self();

    add_filter('body_class', [$self, 'addBodyClass']);
  }

  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}

MyClass::register();
```

This is how I used hook registration before switching to PHP attributes. I appreciate that the registration takes place inside the class and we don't need to use the constructor. However, the registration is still decoupled from the method.

## Leveraging PHP attributes

PHP attributes provide the capability to add structured metadata to classes, methods, functions, and more. These attributes are machine-readable and can be inspected during runtime using the Reflection API.

If you are not familiar with PHP attributes, please refer to [the documentation](https://www.php.net/manual/en/language.attributes.overview.php).

To utilize PHP attributes for hook registration, we need to complete three tasks:

1. Define the attribute class.
2. Use the attribute on the method.
3. Scan the classes with hooks.

In practice, we follow this sequence to achieve our goal. To gain a better understanding of the concept, we will begin with step 2, move on to step 1, and then complete step 3.

## The class with the hook

Let's say we want to add a classname to the body tag, as seen in the examples above. We take the pure class without any hook registration:

```php
class MyClass
{
  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}
```

The attribute declaration begins with ```#[```and ends with ```]```. Inside, the attribute is listed. We place the attribute declaration right before the method:

```php
class MyClass
{
  #[Filter('body_class')]
  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}
```

Observe how elegantly the attribute is connected to its respective method. By using this approach, you can easily make changes to the priority or number of arguments passed to the function in one central location.

Here is an example of how to change the priority:

```php
class MyClass
{
  #[Filter('body_class', 1)]
  public function addBodyClass(array $classes): array
  {
    $classes[] = 'my-new-class';

    return $classes;
  }
}
```

We'll cover that in more detail later on.

## The attribute class

In order for the code above to work, we need to define the corresponding attribute class.

We need the constructor to take the properties `hook`