# Commander

This package gives you an easy way to leverage commands and domain events in your Laravel projects.

## Installation

Per usual, install Commander through Composer.

```js
"require": {
    "laracasts/commander": "1.0.*"
}
```

Next, update `app/config/app.php` to include a reference to this package's service provider in the providers array.

```php
'providers' => [
    'Laracasts\Commander\CommanderServiceProvider'
]
```