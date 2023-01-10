<?php

namespace phuongdev89\email\models;

use baibaratsky\yii\behaviors\model\SerializedAttributes;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * This is the model class for table "{{%email_message}}".
 *
 * @property integer $id
 * @property string $status
 * @property integer $priority
 * @property string $from
 * @property string $to
 * @property string $subject
 * @property string $text
 * @property string $created_at
 * @property string $sent_at
 * @property string $bcc
 * @property string $files
 * @property int $try_time
 * @property int $email_template_id
 * @property EmailTemplate $emailTemplate
 */
class EmailMessage extends ActiveRecord
{

    const STATUS_ERROR = 'error';

    const STATUS_NEW = 'new';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_SENT = 'sent';

    const STATUS = [
        self::STATUS_ERROR => 'Error',
        self::STATUS_NEW => 'New',
        self::STATUS_IN_PROGRESS => 'In progress',
        self::STATUS_SENT => 'Sent',
    ];

    public $files = [];

    public $bcc = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email_message}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    static::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'serializedAttributes' => [
                'class' => SerializedAttributes::class,
                'attributes' => [
                    'files',
                    'bcc',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'priority',
                    'try_time',
                    'email_template_id',
                ],
                'integer',
            ],
            [
                [
	                'status',
	                'text'
                ],
                'string',
            ],
            [
                [
                    'created_at',
                    'sent_at',
                    'files',
                ],
                'safe',
            ],
            [
                [
                    'from',
                    'to',
                    'subject',
                ],
                'string',
                'max' => 255,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => Yii::t('email', 'Status'),
            'priority' => Yii::t('email', 'Priority'),
            'from' => Yii::t('email', 'From'),
            'to' => Yii::t('email', 'To'),
            'subject' => Yii::t('email', 'Subject'),
            'text' => Yii::t('email', 'Text'),
            'created_at' => Yii::t('email', 'Created At'),
            'sent_at' => Yii::t('email', 'Sent At'),
            'try_time' => Yii::t('email', 'Try Time'),
            'email_template_id' => Yii::t('email', 'Email Template'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getEmailTemplate()
    {
        return $this->hasOne(EmailTemplate::class, ['id' => 'email_template_id']);
    }
}
