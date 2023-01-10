<?php
/**
 * Created by phuongdev89.
 * @project yii2-setting
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    05/07/2016
 * @time    11:50 PM
 * @version 2.0.0
 */

namespace phuongdev89\email;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     *
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        if (!isset($app->get('i18n')->translations['email*'])) {
            $app->get('i18n')->translations['email*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
            ];
        }
        Yii::setAlias('email', __DIR__);
        Yii::setAlias('phuongdev89/email', __DIR__);
    }
}
