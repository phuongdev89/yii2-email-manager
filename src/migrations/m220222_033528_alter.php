<?php

use yii\db\Migration;

/**
 * Class m190122_033528_add_try_time
 */
class m220222_033528_alter extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    try {
		    $this->addColumn('{{%email_message}}', 'email_template_id', \yii\db\Schema::TYPE_INTEGER . ' DEFAULT NULL');
	    } catch (Exception|Error $e) {
	    }
        $this->alterColumn('{{%email_message}}', 'status', \yii\db\Schema::TYPE_STRING . '(255) NOT NULL DEFAULT "0"');
        $this->update('{{%email_message}}', ['status' => \phuong17889\email\models\EmailMessage::STATUS_NEW], ['status' => '0']);
        $this->update('{{%email_message}}', ['status' => \phuong17889\email\models\EmailMessage::STATUS_IN_PROGRESS], ['status' => '1']);
        $this->update('{{%email_message}}', ['status' => \phuong17889\email\models\EmailMessage::STATUS_SENT], ['status' => '2']);
        $this->update('{{%email_message}}', ['status' => \phuong17889\email\models\EmailMessage::STATUS_ERROR], ['status' => '3']);
        $this->alterColumn('{{%email_message}}', 'status', "ENUM('error','new','in_progress','sent') NOT NULL DEFAULT 'new'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190122_033528_add_try_time cannot be reverted.\n";
        return false;
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190122_033528_add_try_time cannot be reverted.\n";

        return false;
    }
    */
}
