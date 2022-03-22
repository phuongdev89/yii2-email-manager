<?php

use yii\db\Migration;

class m140929_033917_email_module_install extends Migration
{

    public function up()
    {
	    try {
		    $this->createTable('{{%email_message}}', [
			    'id' => $this->primaryKey(),
			    'status' => $this->integer()->notNull()->defaultValue(0),
			    'priority' => $this->integer()->notNull()->defaultValue(0),
			    'from' => $this->string(),
			    'to' => $this->string(),
			    'subject' => $this->string(),
			    'text' => $this->text(),
			    'created_at' => $this->integer(),
			    'sent_at' => $this->integer(),
			    'bcc' => $this->text(),
			    'files' => $this->text(),
		    ], 'Engine=InnoDB');
	    } catch (Exception|Error $e) {
	    }
    }

    public function down()
    {
        $this->dropTable('{{%email_message}}');
    }
}
