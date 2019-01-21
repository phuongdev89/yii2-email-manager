<?php

namespace navatech\email\commands;

use navatech\email\components\EmailManager;
use navatech\email\models\EmailMessage;
use React\EventLoop\Factory;
use yii\console\Controller;

/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class EmailCommand
 * @package email\commands
 */
class EmailController extends Controller {

	/**
	 * Run daemon based on "for cycle"
	 *
	 * @param int $loopLimit
	 * @param int $chunkSize
	 */
	public function actionRunSpoolDaemon($loopLimit = 1000, $chunkSize = 100) {
		set_time_limit(0);
		for ($i = 1; $i < $loopLimit; $i ++) {
			$this->runChunk($chunkSize);
			sleep(1);
		}
	}

	/**
	 * Tries to run sendOne $chunkSize times
	 *
	 * @param int $chunkSize
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected function runChunk($chunkSize = 100) {
		for ($i = 0; $i < $chunkSize; $i ++) {
			$r = $this->sendOne();
			if (!$r) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Send one email from queue
	 * @return bool
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function sendOne() {
		$db          = \Yii::$app->db;
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
		} catch (\Exception $e) {
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
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		return true;
	}

	/**
	 * Run daemon based on ReactPHP loop
	 */
	public function actionRunLoopDaemon() {
		$loop = Factory::create();
		$loop->addPeriodicTimer(1, function() {
			$this->runChunk();
		});
		$loop->run();
	}

	/**
	 * Send one email action
	 * @throws \Exception
	 */
	public function actionSendOne() {
		$this->sendOne();
	}

	/**
	 * @throws \yii\db\Exception
	 */
	public function actionReSend() {
		$db          = \Yii::$app->db;
		$transaction = $db->beginTransaction();
		try {
			/**@var EmailMessage[] $emails */
			$emails = EmailMessage::find()->where(['status' => EmailMessage::STATUS_IN_PROGRESS])->andWhere([
				'<',
				'created_at',
				strtotime('2 minutes ago'),
			])->all();
			foreach ($emails as $email) {
				$email->updateAttributes(['status' => EmailMessage::STATUS_NEW]);
			}
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
		}
	}
}
