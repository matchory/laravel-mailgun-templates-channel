<div id="top"></div>

Mailgun Templates Messages Notification Channel [![Latest Stable Version](https://poser.pugx.org/matchory/laravel-mailgun-templates-channel/v)](https://packagist.org/packages/matchory/laravel-mailgun-templates-channel) [![Total Downloads](https://poser.pugx.org/matchory/laravel-mailgun-templates-channel/downloads)](https://packagist.org/packages/matchory/laravel-mailgun-templates-channel) [![Latest Unstable Version](https://poser.pugx.org/matchory/laravel-mailgun-templates-channel/v/unstable)](https://packagist.org/packages/matchory/laravel-mailgun-templates-channel) [![License](https://poser.pugx.org/matchory/laravel-mailgun-templates-channel/license)](https://packagist.org/packages/matchory/laravel-mailgun-templates-channel) [![Laravel Octane Compatible](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://github.com/laravel/octane)
===============================================
> Provides a notification channel for Mailgun's message templates to Laravel applications.

This library adds a new notification channel to your app that moves email templates from Laravel to Mailgun. This is
useful if, for example, you need to send emails from multiple applications, or need a simple way for non-developers to
manage email templates.

Installation
------------
Install the library from composer:

````bash
composer require matchory/laravel-mailgun-templates-channel
````

Configuration
-------------
Configuration follows the instructions outlined in the Laravel documentation: You should put your Mailgun credentials in
the `config/services.php` file:

```php
    // ...

    'mailgun' => [
    
        // Add your mailing domain as registered on Mailgun
        'domain' => env('MAILGUN_DOMAIN', 'mailing.example.com'),

        // Add your Mailgun secret
        'secret' => env('MAILGUN_SECRET'),
        
        // Optional: Specify the endpoint of Mailgun's EU API if you're a EU
        // customer and need to comply to the GDPR
        'endpoint' => env('MAILGUN_ENDPOINT', 'https://api.eu.mailgun.net'),
    ],

    // ...
```

<p align="right">(<a href="#top">back to top</a>)</p>

Usage
-----
To send message templates, you should first create a template on Mailgun (navigate to "Sending" > "Templates" to manage
your templates in the Mailgun web app). Then, create a new notification with a `toMailgun` method:

```php
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;

class TestNotification extends Notification
{
    use Queueable;
    
    public function __construct(private readonly int $randomNumber) {}

    public function toMailgun(mixed $notifiable): MailgunTemplatedMessage
    {
        return (new MailgunTemplatedMessage('your_template_name'))
            ->from('noreply@example.com')
            ->subject('Test Subject')
            ->param('foo', 'bar')
            ->params([
                'some' => 'more data',
                'available' => 'in your template',
                'name' => $notifiable->name,
                'number' => $this->randomNumber
            ]);
    }
}
```

Send that notification, and you'll receive an email with the rendered template:

```php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

// number chosen by fair dice roll
Notification::sendNow(Auth::user(), new TestNotification(4));
```

That's it - you're able to use message templates now!

<p align="right">(<a href="#top">back to top</a>)</p>

Advanced usage
--------------
The `MailgunTemplatedMessage` instance exposes several message building methods to add more metadata to your message.
This includes the usual stuff like subject, CC, BCC, recipient, and sender, and also _options_ and _params_.  
By setting additional options, you can control Mailgun features like delayed delivery and tracking; by setting params,
you can add template variables to be used while Mailgun renders the template.

Refer to the [Mailgun documentation](https://documentation.mailgun.com/en/latest/user_manual.html#mailing-lists-1) to
learn about the specifics.

### Message options
The following methods can be leveraged to handle message options:

```php
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;

$message = new MailgunTemplatedMessage();

// Add an option.
// Note that the value can be anything that can be converted to JSON!
$message->addOption(name: 'skip-verification', value: false);

// Use the fluent methods for chaining several operations together. They all
// have an equivalent getter and setter.
$message->option(name: 'skip-verification', value: false)
        ->option('require-tls', true)

// Set multiple options at once
$message->options([
    'skip-verification' => false,
    'require-tls' => true,
]);

// Check whether options are set
$message->hasOption('require-tls'); // true

// Retrieve all options
$options = $message->getOptions(); 

// Remove a previously set option. If the option isn't set, this does nothing
$message->removeOption('require-tls');

// Equivalent to the above removeOption() call
$message = $message->withoutOption('require-tls');
```

<p align="right">(<a href="#top">back to top</a>)</p>

### Template parameters
The following methods can be leveraged to handle template rendering parameters:

```php
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;

$message = new MailgunTemplatedMessage();

// Add an param.
// Note that the value can be anything that can be converted to JSON!
$message->addParam(name: 'foo', value: 'bar');

// Use the fluent methods for chaining several operations together. They all
// have an equivalent getter and setter.
$message->param(name: 'foo', value: 'bar')
        ->param('baz', true)

// Set multiple params at once
$message->params([
    'foo' => false,
    'bar' => true,
]);

// Check whether params are set
$message->hasParam('foo'); // true

// Retrieve all params
$params = $message->getParams(); 

// Remove a previously set param. If the param isn't set, this does nothing
$message->removeParam('foo');

// Equivalent to the above removeParam() call
$message = $message->withoutParam('foo');
```

<p align="right">(<a href="#top">back to top</a>)</p>

Managing templates
------------------
Unfortunately, the Mailgun SDK currently has no facilities to manage message templates, although the API endpoints exist
on the Mailgun servers (See [this issue](https://github.com/mailgun/mailgun-php/issues/832) for reference).

As soon as the templates API is implemented, we will add managing capabilities to this library - including automatically
updating your message templates from local blade files.

<p align="right">(<a href="#top">back to top</a>)</p>

Contributions
-------------
Contributions are what make the open source community such an amazing place to learn, inspire, and create.
Any contributions you make are greatly appreciated.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also
simply open an issue. Don't forget to give the project a star! Thanks again!

<p align="right">(<a href="#top">back to top</a>)</p>

License
-------
Distributed under the MIT License. See [LICENSE](./LICENSE) for more information.

<p align="right">(<a href="#top">back to top</a>)</p>
