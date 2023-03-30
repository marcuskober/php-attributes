# 📖 Tutorial: Registering WordPress hooks using PHP attributes

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

---
## Leveraging PHP attributes

PHP attributes provide the capability to add structured metadata to classes, methods, functions, and more. These attributes are machine-readable and can be inspected during runtime using the Reflection API.

If you are not familiar with PHP attributes, please refer to [the documentation](https://www.php.net/manual/en/language.attributes.overview.php).

To utilize PHP attributes for hook registration, we need to complete three tasks:

1. Define the attribute class.
2. Use the attribute on the method.
3. Scan the classes with hooks.

In practice, we follow this sequence to achieve our goal. To gain a better understanding of the concept, we will begin with step 2, move on to step 1, and then complete step 3.

---
### 1️⃣ The class with the hook

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

The attribute declaration begins with `#[` and ends with `]`. Inside, the attribute is listed. We place the attribute declaration right before the method:

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

---

### 2️⃣ The attribute class

In order for the code above to work, we need to define the corresponding attribute class.

We need the constructor to take the properties `hook`, `priority` and `acceptedArgs`, as the `add_filter` function needs these too. We leverage the [constructor property promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) of PHP 8.

```php
class Filter
{
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }
}
```

Now we need to transform this regular class into an attribute class. As previously mentioned, attributes can also be added to classes. To indicate that this class is an attribute, we must include the #[Attribute] definition immediately preceding the class definition:

```php
#[Attribute]
class Filter
{
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }
}
```

Now, we want to incorporate the call to `add_filter()` within our class:

```php
#[Attribute]
class Filter
{
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }

    public function register(callable|array $method): void
    {
        add_filter($this->hook, $method, $this->priority, $this->acceptedArgs);
    }
}
```

That's our attribute class. The next step is to make it functional.

---

### 3️⃣ Scanning our hooked classes

Now that our attribute class is ready and we have another class that uses this attribute, it's time to scan the classes for `Filter` attributes.

n order to scan a class for attributes, we need to utilize the [Reflection API](https://www.php.net/manual/en/language.attributes.reflection.php) of PHP.

Let's assume that we have a main class for our plugin:

```php
class App
{
  public static function init(): void
  {
  }
}

App::init();
```

We will create a method called `registerHooks()` and for simplicity's sake, we will hardcode the list of classes to scan directly into the method. However, in production, it's recommended to use other techniques. You can refer to another approach in the plugin inside this repository.

```php
class App
{
  public static function init(): void
  {
    $self = new self();
    $self->registerHooks();
  }

  private function registerHooks(): void
  {
    $hookedClasses = [
      'MyClass',
    ];
  }
}

App::init();
```

Note: You need to use the qualified class name here (see [src/Main/App.php](https://github.com/marcuskober/php-attributes/blob/142568d438c493051b97a002fee7f9479a99e137/src/Main/App.php)).

Our goal now is to retrieve all the methods from the classes and check if they have any Filter attributes. To achieve this, we need to iterate through the classes and create a ReflectionClass instance of each class:

```php
class App
{
  public static function init(): void
  {
    $self = new self();
    $self->registerHooks();
  }

  private function registerHooks(): void
  {
    $hookedClasses = [
      'MyClass',
    ];

    foreach ($hookedClasses as $hookedClass) {
      $reflectionClass = new ReflectionClass($hookedClass);
    }

  }
}

App::init();
```

Now that we have the reflection class, we can retrieve all its methods:

```php
$methods = $reflectionClass->getMethods();
```

This will return an array of `ReflectionMethod` objects. We can then loop through each method and get all `Filter` attributes, if any. The `getAttributes()` method returns an array filled with the `Filter` attribute objects or an empty array if no methods with filter attributes are found. We can then loop through each filter attribute object using a foreach loop:

```php
foreach ($methods as $method) {
    $filterAttributes = $method->getAttributes(Filter::class);

    foreach ($filterAttributes as $filterAttribute) {
      // do the magic
    }
}
```

In the next step, we can instantiate the `Filter` attribute class using the `newInstance` method:

```php
foreach ($methods as $method) {
    $filterAttributes = $method->getAttributes(Filter::class);

    foreach ($filterAttributes as $filterAttribute) {
      $filter = $filterAttribute->newInstance();
    }
}
```

Let's recall what property we need for the `register` method of our `Filter` class. We need the method in the form of an array (`[$className, $method]`). To get the required method, we first need to instantiate the class with the hooks:

```php
foreach ($methods as $method) {
    $filterAttributes = $method->getAttributes(Filter::class);

    foreach ($filterAttributes as $filterAttribute) {
      $hookedClassObject = new $hookedClass();
      
      $filter = $filterAttribute->newInstance();
      $filter->register([$hookedClassObject, $method->getName()]);
    }
}
```

And because a method is allowed to have multiple attributes and the hooked class may have more than one method, we need to ensure that the hooked class is instantiated only once. Here's the full `App` class for you to better understand the code:

```php
class App
{
  private array $instances = [];

  public static function init(): void
  {
    $self = new self();
    $self->registerHooks();
  }

  private function registerHooks(): void
  {
    $hookedClasses = [
      'MyClass',
    ];

    foreach ($hookedClasses as $hookedClass) {
      $reflectionClass = new ReflectionClass($hookedClass);

      foreach ($reflectionClass->getMethods() as $method) {
        $filterAttributes = $method->getAttributes(Filter::class);

          foreach ($filterAttributes as $filterAttribute) {
            if (! isset($this->instances[$hookedClass])) {
              $this->instances[$hookedClass] = new $hookedClass();
            }

            $filter = $filterAttribute->newInstance();
            $filter->register([$this->instances[$hookedClass], $method->getName()]);
          }
      }
    }
  }
}
```

### 4️⃣ Extending our code to register actions too

The `Action` attribute class looks very similar to the `Filter` class:

```php
#[Attribute]
class Action
{
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }

    public function register(callable|array $method): void
    {
        add_action($this->hook, $method, $this->priority, $this->acceptedArgs);
    }
}
```

To be able to search for `Filter` and `Action` attributes using the `getAttributes()` method, we create a simple interface for our hook classes:

```php
interface HookInterface
{
    public function register(callable|array $method): void;
}
```

Our `Filter` and `Action` attribute classes must implement this interface:

```php
#[Attribute]
class Filter implements HookInterface
{
  // Class code
}

#[Attribute]
class Action implements HookInterface
{
  // Class code
}
```

Now we can easily use our exiting code of the `registerHooks()` method to support filters and actions:

```php
private function registerHooks(): void
{
  $hookedClasses = [
    'MyClass',
  ];

  foreach ($hookedClasses as $hookedClass) {
    $reflectionClass = new ReflectionClass($hookedClass);

    foreach ($reflectionClass->getMethods() as $method) {
      $hookAttributes = $method->getAttributes(HookInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($hookAttributes as $hookAttribute) {
          if (! isset($this->instances[$hookedClass])) {
            $this->instances[$hookedClass] = new $hookedClass();
          }

          $hook = $hookAttribute->newInstance();
          $hook->register([$this->instances[$hookedClass], $method->getName()]);
        }
    }
  }
}
```

For `getAttributes()` to accept the interface as a class name, we need to set the flag `ReflectionAttribute::IS_INSTANCEOF` (see [documentation](https://www.php.net/manual/en/reflectionfunctionabstract.getattributes.php)).

---

## End of tutorial

I hope you found this tutorial helpful. If you have any further questions, feel free to ask.

Here's a summary of what we covered:

- We learned how to create attribute classes in PHP 8 and how to apply them to our code.
- We used attributes to create Filter and Action hooks in our WordPress plugin.
- We used the Reflection API of PHP to scan our code for hooks and to register them automatically.

Thank you for reading, and happy coding!

Feel free to download this WordPress plugin and experiment with it: [https://github.com/marcuskober/php-attributes/archive/refs/heads/main.zip](https://github.com/marcuskober/php-attributes/archive/refs/heads/main.zip)

---

# About me

Hey there, I'm Marcus, and I'm a passionate full time WordPress developer who's dedicated to crafting high-quality, well-structured plugins. For me, coding is more than just a job; it's a creative outlet where I can constantly challenge myself to find new and better solutions.

When I'm not working on WordPress projects, you can find me hanging out in Cologne, Germany, with my lovely wife and two wonderful kids.

Don't hesitate to get in touch with me at [hello@marcuskober.de](mailto:hello@marcuskober.de). I'm always open to new ideas and collaborations!