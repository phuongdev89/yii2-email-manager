# Installation #

## Composer ##
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist navatech/yii2-email-manager "1.0.*"
```

or add

```
"navatech/yii2-email-manager": "1.0.*"
```

to the require section of your `composer.json` file.

## Migration ##

Migration run

```php
php yii migrate --migrationPath=@vendor/navatech/yii2-email-manager/src/migrations
```

# Configuration #

## Simple configuration:
    'components' => [
        'mailer'        => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'host'             => 'smtp.gmail.com',
            'username'         => 'test@gmail.com',
            'password'         => '12345678',
            'port'             => '587',
            'encryption'       => 'TLS',
        ],
        'emailManager' => [
            'class' => '\navatech\email\EmailManager',
            'defaultTransport' => 'yiiMailer',
            'transports' => [
                'yiiMailer' => [
                    'class' => '\navatech\email\transports\YiiMailer',
                ],
                /*
                'mailGun' => [ //Not required
                    'class'  => '\navatech\email\transports\MailGun',
                    'apiKey' => 'xxx',
                    'domain' => 'our-domain.net',
                ],
                */
            ],
        ],
    ]

## Advanced config
First you need `navatech/yii2-setting` installed, create 5 records on Setting module:
* `smtp_host` (value: `smtp.gmail.com`)
* `smtp_user` (value: `test@gmail.com`)
* `smtp_password` (value: `12345678`)
* `smtp_port` (value: `587`)
* `smtp_encryption` (value: `TLS`)


    'components' => [
        'mailer'        => [
            'class'            => '\navatech\email\swiftmailer\Mailer',
        ],
        'emailManager'  => [
            'class'            => '\navatech\email\components\EmailManager',
            'defaultTransport' => 'yiiMailer',
            'transports'       => [
                'yiiMailer' => [
                    'class' => '\navatech\email\transports\YiiMailer',
                ],
                /*
                'mailGun' => [
                    'class'  => '\navatech\email\transports\MailGun',
                    'apiKey' => 'xxx',
                    'domain' => 'our-domain.net',
                ],
                */
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
    $emailManager = EmailManager::getInstance();
    // direct send via default transport
    $emailManager->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    // queue send via default transport
    $emailManager->queue('from@example.com', 'to@example.com', 'test subject', 'test email');
    // direct send via selected transport
    $emailManager->transports['mailGun']->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    
    // use shortcuts
    EmailTemplate::findByShortcut('shortcut_name')->queue('recipient@email.org', ['param1' => 1, 'param2' => 'asd']);

