# Installation #

## Composer ##
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist phuongdev89/yii2-email-manager "@dev"
```

or add

```
"phuongdev89/yii2-email-manager": "@dev"
```

to the require section of your `composer.json` file.

## Migration ##

Migration run

```php
php yii migrate --migrationPath=@vendor/phuongdev89/yii2-email-manager/src/migrations
```

# Configuration #

## Simple configuration:
```
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
            'class' => '\phuongdev89\email\EmailManager',
            'defaultTransport' => 'yiiMailer',
            'resendAfter'      => 5,//resend after 5 mins if stuck
            'tryTime'          => 3,//max try time resend
            'transports' => [
                'yiiMailer' => [
                    'class' => '\phuongdev89\email\transports\YiiMailer',
                ],
                /*
                'mailGun' => [ //Not required
                    'class'  => '\phuongdev89\email\transports\MailGun',
                    'apiKey' => 'xxx',
                    'domain' => 'our-domain.net',
                ],
                */
            ],
        ],
    ],
    'modules' => [
        'mailer'   => [
            'class'         => 'phuongdev89\email\Module',
            'cleanAfter'    => 30//clean after days
        ],
    ]
```
## Advanced config
First you need `phuongdev89/yii2-setting` installed, create 5 records on Setting module:
* `smtp_host` (value: `smtp.gmail.com`)
* `smtp_user` (value: `test@gmail.com`)
* `smtp_password` (value: `12345678`)
* `smtp_port` (value: `587`)
* `smtp_encryption` (value: `TLS`)

```
    'components' => [
        'mailer'        => [
            'class'            => '\phuongdev89\email\swiftmailer\Mailer',
        ],
        'emailManager'  => [
            'class'            => '\phuongdev89\email\components\EmailManager',
            'defaultTransport' => 'yiiMailer',
            'resendAfter'      => 5,//resend after 5 mins if stuck
            'tryTime'          => 3,//max try time resend
            'transports'       => [
                'yiiMailer' => [
                    'class' => '\phuongdev89\email\transports\YiiMailer',
                ],
                /*
                'mailGun' => [
                    'class'  => '\phuongdev89\email\transports\MailGun',
                    'apiKey' => 'xxx',
                    'domain' => 'our-domain.net',
                ],
                */
            ],
        ],
    ]
    'modules' => [
        'mailer'   => [
            'class'         => 'phuongdev89\email\Module',
            'cleanAfter'    => 30//clean after days
      ],
    ]
```
Add command to the list of the available commands. Put it into console app configuration:
```
    'controllerMap' => [
        'email' => '\phuongdev89\email\commands\EmailController',
    ],
```
Add email sending daemon into crontab, can be via lockrun or run-one utils (read more `phuongdev89/yii2-cron`):
```
    */5 * * * * php /your/site/path/yii email/start
```
# Usage
##Backend
Access this url:
```
http://backend.yourdomain.com/mailer
```
or
```
http://backend.yourdomain.com/index.php?r=mailer
```
##Simple usage
```
    // obtain component instance
    $emailManager = EmailManager::getInstance();
    // direct send via default transport
    $emailManager->send('from@example.com', 'to@example.com', 'test subject', 'test email');
    // queue send via default transport
    $emailManager->queue('from@example.com', 'to@example.com', 'test subject', 'test email');
    // direct send via selected transport
    $emailManager->transports['mailGun']->send('from@example.com', 'to@example.com', 'test subject', 'test email');
```
##Advanced usage
Create a shortcut name `welcome_email`. Example: 
```$xslt
Welcome {{fullname}},
Thanks for registered at {{url}}.
Your username: <b>{{username}}</b>
Your phone: <b>{{phone}}</b>
```
Send/Queue welcome email when done:
```    
    // use shortcuts
    $user = new User();
    $user->fullname = "Test ABC";
    $user->username = "testabc";
    $user->email = "test@gmail.com";
    $user->phone = "0123456789";
    ...
    if($user->save()) {
        EmailTemplate::findByShortcut('welcome_email')->queue($user->email, ['fullname' => $user->fullname, 'username' => $user->username, 'ur' => 'http://domain.com', 'phone' => $user->phone]);
    }
```
