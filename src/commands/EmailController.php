<?php

namespace phuongdev89\email\commands;

use Exception;
use phuongdev89\cron\commands\DaemonController;
use phuongdev89\email\components\EmailManager;
use phuongdev89\email\models\EmailMessage;
use phuongdev89\email\Module;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class EmailCommand
 * @package email\commands
 */
class EmailController extends DaemonController
{

    public $restartDb = true;

    /**
     * @param $id int
     * @return void
     * @throws InvalidConfigException
     */
    public function actionReSend($id = null)
    {
        /**@var EmailManager $instance */
        $instance = Yii::$app->get('emailManager');
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            /**@var EmailMessage[] $emails */
            if ($id != null) {
                $emails = EmailMessage::find()->where(['id' => $id])->all();
            } else {
                $emails = EmailMessage::find()->where(['status' => EmailMessage::STATUS_IN_PROGRESS])->andWhere([
                    '<',
                    'created_at',
                    strtotime($instance->resendAfter . ' minutes ago'),
                ])->andWhere([
                    '<',
                    'try_time',
                    $instance->tryTime,
                ])->all();
            }
            foreach ($emails as $email) {
                $email->updateAttributes([
                    'status' => EmailMessage::STATUS_NEW,
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
    public function actionSendOne()
    {
        $db = Yii::$app->db;
        $model = EmailMessage::find()->andWhere(['status' => EmailMessage::STATUS_NEW])->orderBy(['priority' => SORT_DESC, 'id' => SORT_ASC])->one();
        if ($model !== null) {
            $model->updateAttributes(['status' => EmailMessage::STATUS_IN_PROGRESS]);
            $transaction = $db->beginTransaction();
            try {
                if (filter_var($model->to, FILTER_VALIDATE_EMAIL)) {
                    $result = EmailManager::getInstance()->send($model->from, $model->to, $model->subject, $model->text, $model->files, $model->bcc);
                    if ($result) {
                        $model->sent_at = time();
                        $model->status = EmailMessage::STATUS_SENT;
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
        }
        return true;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionClean()
    {
        /**@var Module $module */
        $module = Yii::$app->getModule('mailer');
        $emailMessages = EmailMessage::find()->andWhere([
            '<',
            'created_at',
            (time() - ($module->cleanAfter * 3600 * 24)),
        ])->all();
        foreach ($emailMessages as $emailMessage) {
            if ($module->cleanOnlyBody) {
                $emailMessage->updateAttributes(['text'=>'cleared']);
            } else {
                $emailMessage->delete();
            }
        }
    }


    /**
     * @throws Exception
     */
    protected function worker()
    {
        $this->actionSendOne();
    }

    /**
     * @return string
     */
    protected function daemonName(): string
    {
        return "email-sending";
    }
}
