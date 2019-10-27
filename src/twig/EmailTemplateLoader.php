<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 */

namespace navatech\email\twig;

use navatech\email\components\EmailManager;
use navatech\email\models\EmailTemplate;
use Twig\Source;
use Twig_LoaderInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class EmailTemplateLoader extends Component implements Twig_LoaderInterface {

	/** @var string Attribute name to fetch template from */
	public $attributeName = 'text';

	/**
	 * @param $name
	 *
	 * @return mixed|string|Source
	 * @throws InvalidConfigException
	 */
	public function getSource($name) {
		$currentLanguage = Yii::$app->language;
		$defaultLanguage = ArrayHelper::getValue(EmailManager::getInstance()->languages, 0, 'en-US');
		/** @var EmailTemplate $model */
		$model = EmailTemplate::find()->where(['shortcut' => $name])->andWhere('language = :currentLanguage OR language = :defaultLanguage OR language = :systemDefaultLanguage', [
			':currentLanguage'       => $currentLanguage,
			':defaultLanguage'       => $defaultLanguage,
			':systemDefaultLanguage' => 'en-US',
		])->one();
		if (!$model) {
			Yii::error("Missing template {$name}, current language {$currentLanguage}, default language {$defaultLanguage}", 'email');
			return "!!! UNKNOWN TEMPLATE {$name} !!!";
		}
		return $model->getAttribute($this->attributeName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCacheKey($name) {
		return $name . $this->attributeName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFresh($name, $time) {
		return false;
	}

	/**
	 * Returns the source context for a given template logical name.
	 *
	 * @param string $name The template logical name
	 *
	 * @return Source
	 *
	 * @throws InvalidConfigException
	 */
	public function getSourceContext($name) {
		return $this->getSource($name);
	}

	/**
	 * Check if we have the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to check if we can load
	 *
	 * @return bool If the template source code is handled by this loader or not
	 */
	public function exists($name) {
		return true;
	}
}
