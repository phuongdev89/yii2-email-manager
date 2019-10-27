<?php

namespace navatech\email\commands;

use Exception;
use navatech\email\components\EmailManager;
use navatech\email\interfaces\EmailSpoolDaemonInterface;
use navatech\email\models\EmailMessage;
use navatech\email\Module;
use navatech\email\traits\EmailSpoolDaemonTrait;
use React\EventLoop\Factory;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\StaleObjectException;
use yii\helpers\Console;

/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class EmailCommand
 * @package email\commands
 */
class EmailController extends Controller implements EmailSpoolDaemonInterface {

	use EmailSpoolDaemonTrait;

	/**
	 * Send one email action
	 * @throws Exception
	 */
	public function actionSendOne() {
		$this->sendOne();
	}

	/**
	 * Run daemon based on "for cycle"
	 *
	 * @param int $loopLimit
	 * @param int $chunkSize
	 *
	 * @throws Exception
	 * @throws Throwable
	 */
	public function actionSpoolDaemon($loopLimit = 1000, $chunkSize = 100) {
		set_time_limit(0);
		$this->file = Yii::getAlias('@runtime/' . $this->id . '-' . $this->action->id . '.lock');
		if (file_exists($this->file)) {
			if (file_get_contents($this->file) < strtotime($this->cycle() . ' minutes ago')) {
				unlink($this->file);
				sleep(10);
			}
		}
		if (!file_exists($this->file)) {
			$time = time();
			file_put_contents($this->file, $time);
			$this->filetime = $time;
		}
		Console::output('File time: ' . date('Y-m-d H:i:s', file_get_contents($this->file)));
		for ($i = 1; $i < $loopLimit; $i ++) {
			$this->runSpoolChunk($chunkSize);
			sleep(1);
		}
		unlink($this->file);
	}

	/**
	 * Run daemon based on ReactPHP loop
	 */
	public function actionLoopDaemon() {
		$loop = Factory::create();
		$loop->addPeriodicTimer(1, function() {
			$this->runLoopChunk();
		});
		$loop->run();
	}

	/**
	 * Tries to run sendOne $chunkSize times
	 *
	 * @param int $chunkSize
	 *
	 * @return bool
	 * @throws Exception
	 * @throws Throwable
	 */
	public function runSpoolChunk($chunkSize = 100) {
		for ($i = 0; $i < $chunkSize; $i ++) {
			try {
				if ($this->checkPid()) {
					$this->clean();
					$this->reSend();
					$r = $this->runOne();
					if (!$r) {
						return false;
					}
				}
			} catch (Exception $e) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Tries to run sendOne $chunkSize times
	 *
	 * @param int $chunkSize
	 *
	 * @return bool
	 * @throws Exception
	 * @throws Throwable
	 */
	public function runLoopChunk($chunkSize = 100) {
		for ($i = 0; $i < $chunkSize; $i ++) {
			$this->clean();
			$this->reSend();
			$r = $this->sendOne();
			if (!$r) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidConfigException
	 */
	private function reSend() {
		/**@var EmailManager $instance */
		$instance    = Yii::$app->get('emailManager');
		$db          = Yii::$app->db;
		$transaction = $db->beginTransaction();
		try {
			/**@var EmailMessage[] $emails */
			$emails = EmailMessage::find()->where(['status' => EmailMessage::STATUS_IN_PROGRESS])->andWhere([
				'<',
				'created_at',
				strtotime($instance->resendAfter . ' minutes ago'),
			])->andWhere([
				'<',
				'try_time',
				$instance->tryTime,
			])->all();
			foreach ($emails as $email) {
				$email->updateAttributes([
					'status'   => EmailMessage::STATUS_NEW,
					'try_time' => $email->try_time + 1,
				]);
			}
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
		}
	}

	/**
	 * Send one email from queue
	 * @return bool
	 * @throws Exception
	 */
	private function sendOne() {
		$db          = Yii::$app->db;
		$transaction = $db->beginTransaction();
		try {
			$id = $db->createCommand('SELECT id FROM {{%email_message}} WHERE status=:status ORDER BY priority DESC, id ASC LIMIT 1 FOR UPDATE', [
				'status' => EmailMessage::STATUS_NEW,
			])->queryScalar();
			if ($id === false) {
				$transaction->rollBack();
				return false;
			}
			/** @var EmailMessage $model */
			$model         = EmailMessage::findOne($id);
			$model->status = EmailMessage::STATUS_IN_PROGRESS;
			$model->updateAttributes(['status']);
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		$transaction = $db->beginTransaction();
		try {
			if (filter_var($model->to, FILTER_VALIDATE_EMAIL)) {
				$result = EmailManager::getInstance()->send($model->from, $model->to, $model->subject, $model->text, $model->files, $model->bcc);
				if ($result) {
					$model->sent_at = time();
					$model->status  = EmailMessage::STATUS_SENT;
				} else {
					$model->status = EmailMessage::STATUS_ERROR;
				}
			} else {
				$model->status = EmailMessage::STATUS_ERROR;
			}
			$model->updateAttributes([
				'sent_at',
				'status',
			]);
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		return true;
	}

	/**
	 * @throws Throwable
	 * @throws StaleObjectException
	 */
	private function clean() {
		/**@var Module $module */
		$module        = Yii::$app->getModule('mailer');
		$emailMessages = EmailMessage::find()->andWhere([
			'<',
			'created_at',
			(time() - ($module->cleanAfter * 3600 * 24)),
		])->all();
		foreach ($emailMessages as $emailMessage) {
			$emailMessage->delete();
		}
	}

	/**
	 * @return string
	 */
	public function cycle() {
		return 10;
	}
}
