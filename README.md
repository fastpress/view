# Fastpress View

Fastpress View is a powerful and flexible templating engine for PHP, designed to render views and manage layouts seamlessly within the Fastpress framework.

## Installation

Use [Composer](https://getcomposer.org/) to install Fastpress View into your project:
```bash
composer require fastpress/view
```

## Usage

### Creating an Instance

You can create an instance of the `View` class by passing the application context or configuration array.

```php
$app = ['template' => ['views' => '/path/to/views/', 'layout' => '/path/to/layout/']];
$view = new \Fastpress\View\View($app);
```
### Setting Configuration Options
Set configuration options using the set method:
```php
$view->set('option', 'value');
```

### Rendering Views
Render a view file with optional variables:
```php
$view->render('viewFile.php', ['variable' => 'value']);
```

### Extending Layouts
Extend an existing layout file:
```php
$view->extend('layoutFile');
```

### Managing Content Blocks
Start and end content blocks within your layout:
```php
$view->block('blockName');
// ... HTML and PHP content here ...
$view->endblock('blockName');
```

### Retrieve the content of a named block:
```php
$view->content('blockName');
```

### Setting and Retrieving Layouts
Set a layout file:
```php
$view->layout('layoutFile');
```

## Contributing
Contributions are welcome! Please feel free to submit a pull request or open issues to improve the library.


## License
This library is open-sourced software licensed under the MIT license.

## Support
If you encounter any issues or have questions, please file them in the issues section on GitHub.
