# Email Module #

## Installation ##

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist navatech/yii2-email-manager "~0.2.*"
```

or add

```
"navatech/yii2-email-manager": "~0.2.*"
```

to the require section of your `composer.json` file.

## Migration ##

Migration run

```php
php yii migrate --migrationPath=@vendor/navatech/yii2-email-manager/src/migrations
```

## Configuration ##

Simple configuration:

    'components' => [
        'emailManager' => [
            'class' => '\navatech\email\EmailManager',
            'transports' => [
                'yiiMailer' => '\navatech\email\transports\YiiMailer'
            ],
        ],
    ]

Multi transport configuration:

    'components' => [
        'emailManager' => [
            'class' => '\navatech\email\EmailManager',
            'defaultTransport' => 'yiiMailer',
            'transports' => [
                'yiiMailer' => [
                    'class' => '\navatech\email\transports\YiiMailer',
                ],
                'mailGun' => [
                    'class' => '\navatech\email\transports\MailGun',
                    'apiKey' => 'xxx',
                    'domain' => 'our-domain.net',
                ],
            ],
        ],
    ]

Add command to the list of the available commands. Put it into console app configuration:

    'controllerMap' => [
        'email' => '\navatech\email\commands\EmailCommand',
    ],

Add email sending daemon into crontab via lockrun or run-one utils:

    */5 * * * * run-one php /your/site/path/yii email/run-spool-daemon

OR, if you will use cboden/ratchet

    */5 * * * * run-one php /your/site/path/yii email/run-loop-daemon

## Usage ##

    // obtain component instance
    $emailManager = EmailManager::geInstance();
    // direct send via default transport
    $emailManager->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    // queue send via default transport
    $emailManager->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    // direct send via selected transport
    $emailManager->transports['mailGun']->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    
    // use shortcuts
    EmailTemplate::findByShortcut('shortcut_name')->queue('recipient@email.org', ['param1' => 1, 'param2' => 'asd']);

