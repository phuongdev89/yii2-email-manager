<?php

namespace navatech\email\models;

use Exception;
use navatech\email\components\EmailManager;
use navatech\email\twig\EmailTemplateLoader;
use Twig_Environment;
use Twig_LoaderInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * This is the model class for table "{{%email_template}}".
 *
 * @property integer $id
 * @property string  $shortcut
 * @property string  $from
 * @property string  $subject
 * @property string  $text
 * @property string  $language
 */
class EmailTemplate extends ActiveRecord {

	public $params = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%email_template}}';
	}

	/**
	 * @param string $shortcut
	 * @param string $language
	 *
	 * @return self
	 */
	public static function findByShortcut($shortcut, $language = null) {
		$manager      = EmailManager::getInstance();
		$languageCode = $language;
		foreach (
			[
				$language,
				$manager->defaultLanguage,
				'en-US',
			] as $l
		) {
			$template     = static::findOne([
				'shortcut' => $shortcut,
				'language' => $l,
			]);
			$languageCode = $l;
			if ($template) {
				return $template;
			}
		}
		throw new \BadMethodCallException('Template not found: ' . VarDumper::dumpAsString($shortcut) . ', language ' . $languageCode);
	}

	/**
	 * @param $shortcut
	 * @param $language
	 *
	 * @return EmailTemplate
	 */
	public static function loadTemplate($shortcut, $language) {
		return self::findByShortcut($shortcut, $language);
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[
				[
					'shortcut',
					'from',
					'text',
					'language',
				],
				'required',
			],
			[
				['text'],
				'string',
			],
			[
				[
					'shortcut',
					'from',
					'subject',
					'language',
				],
				'string',
				'max' => 255,
			],
			[
				[
					'shortcut',
					'language',
				],
				'unique',
				'targetAttribute' => [
					'shortcut',
					'language',
				],
			],
		];
	}

	/**
	 * Twig instance factory
	 *
	 * @param Twig_LoaderInterface $loader
	 *
	 * @return Twig_Environment
	 */
	protected function getTwig(Twig_LoaderInterface $loader) {
		$twig = new Twig_Environment($loader);
		$twig->setCache(false);
		return $twig;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'       => 'ID',
			'shortcut' => Yii::t('email', 'Shortcut'),
			'from'     => Yii::t('email', 'From'),
			'subject'  => Yii::t('email', 'Subject'),
			'text'     => Yii::t('email', 'Text'),
			'language' => Yii::t('email', 'Language'),
		];
	}

	/**
	 * Queues current template for sending with the given priority
	 *
	 * @param       $to
	 * @param array $params
	 * @param int   $priority
	 * @param array $files
	 * @param null  $bcc
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public function queue($to, array $params = [], $priority = 0, $files = [], $bcc = null) {
		$text    = nl2br($this->renderAttribute('text', $params));
		$subject = $this->renderAttribute('subject', $params);
		EmailManager::getInstance()->queue($this->from, $to, $subject, $text, $priority, $files, $bcc);
		return true;
	}

	/**
	 * Renders attribute using twig
	 *
	 * @param string $attribute
	 * @param array  $params
	 *
	 * @return string
	 */
	public function renderAttribute($attribute, array $params) {
		$twig = $this->getTwig(new EmailTemplateLoader([
			'attributeName' => $attribute,
		]));
		try {
			$result = $twig->render($this->shortcut, $params);
		} catch (Exception $e) {
			$result = Yii::t('email', 'Error compiling email {0}: {1}', [
				$attribute,
				$e->getMessage(),
			]);
		}
		return $result;
	}
}
