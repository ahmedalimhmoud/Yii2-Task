<?php

namespace app\models;

use Yii;
use yii\base\Model;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * NewsLetterForm is the model behind the contact form.
 */
class NewsLetterForm extends Model
{
    public $email;
    public $subject;
    public $body;
    public $file;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email, subject and body are required
            [['email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'csv,xls,xlsx'],
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @return bool whether the model passes validation
     */
    public function send()
    {
        $emails = [$this->email];

        if ($this->validate()) {
            $file = $this->file->saveAs('uploads/' . $this->file->baseName . '.' . $this->file->extension);

            if (isset($this->file->baseName)) {
                if ($file) {
                    $reader = ReaderEntityFactory::createXLSXReader();
                    $reader->open('uploads/' . $this->file->name);
                    # read each cell of each row of each sheet
                    foreach ($reader->getSheetIterator() as $sheet) {
                        foreach ($sheet->getRowIterator() as $row) {
                            if (isset($row->getCells()[0])) {
                                $emails[] = $row->getCells()[0]->getValue();
                            }
                        }
                    }
                    $reader->close();
                }
            }

            $valideEmails = array_filter(filter_var($emails, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_ARRAY));
            $invalidEmails = array_diff($emails, $valideEmails);

            if (!empty($valideEmails)) {

                $connection = AMQPStreamConnection::create_connection([
                    Yii::$app->params['RABBIT_HOST'], 
                    Yii::$app->params['RABBIT_PORT'],
                    Yii::$app->params['RABBIT_USERNAME'],
                    Yii::$app->params['RABBIT_PASSWORD']
                ]);
                $channel = $connection->channel();
                
                $channel->queue_declare('email_queue', false, false, false, false);
                
                $data = json_encode(['subject' => $this->subject, 'message' => $this->body, 'emails' => $valideEmails]);
                
                $msg = new AMQPMessage($data, array('delivery_mode' => 2));
                $channel->basic_publish($msg, '', 'email_queue');

                return [$valideEmails, $invalidEmails];
            } else {
                return false;
            }

        }
        return false;
    }
}
