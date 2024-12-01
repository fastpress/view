# Fastpress View

Fastpress View is a powerful and flexible templating engine for PHP, designed to render views and manage layouts seamlessly within the Fastpress framework.

## Installation

Use [Composer](https://getcomposer.org/) to install Fastpress View into your project:
```bash
composer require fastpress/view
```

## Methods

### `render(string $view, array $data = []): self`

Renders a view with the given data.

**Parameters:**

- `$view`: The name of the view file.
- `$data`: An array of data to pass to the view.

**Returns:**

- The `View` instance.


### `extend(string $layout): self`

Extends a layout.

**Parameters:**

- `$layout`: The name of the layout file.

**Returns:**

- The `View` instance.


### `block(string $name): self`

Starts a template block.

**Parameters:**

- `$name`: The name of the block.

**Returns:**

- The `View` instance.


### `endBlock(string $name = null): void`

Ends a template block.

**Parameters:**

- `$name`: The name of the block (optional).

**Returns:**

- `void`


### `content(string $name): void`

Outputs the content of a template block.

**Parameters:**

- `$name`: The name of the block.

**Returns:**

- `void`


### `share(string $key, mixed $value): self`

Shares data across all views.

**Parameters:**

- `$key`: The key for the shared data.
- `$value`: The value of the shared data.

**Returns:**

- The `View` instance.


### `e(mixed $value): string`

Escapes HTML special characters in a string.

**Parameters:**

- `$value`: The value to escape.

**Returns:**

- The escaped string.


### `set(string $key, mixed $value = null): void`

Sets an application configuration value.

**Parameters:**

- `$key`: The configuration key.
- `$value`: The configuration value.

**Returns:**

- `void`


### `get(string $key): mixed`

Gets an application configuration value.

**Parameters:**

- `$key`: The configuration key.

**Returns:**

- The configuration value.