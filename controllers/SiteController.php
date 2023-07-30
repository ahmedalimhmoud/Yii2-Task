<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\NewsletterForm;
use yii\web\UploadedFile;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays NewsLetter Form.
     *
     * @return string
     */
    public function actionIndex()
    {
        $valideEmails = $invalideEmails = [];
        $model = new NewsletterForm();

        if ($model->load(Yii::$app->request->post())) {

            $model->file = UploadedFile::getInstance($model, 'file');

            if ($response = $model->send()) {
                Yii::$app->session->setFlash('msg', 'Newsletter Has Been Sent Successfully');

                [$valideEmails, $invalideEmails] = $response;

                return $this->render('report', [
                    'valideEmails' => $valideEmails,
                    'invalidEmails' => $invalideEmails
                ]);

            } else {
                Yii::$app->session->setFlash('msg', 'Error Happened will sending Newsletter');
            }
        }

        return $this->render('index', [
            'model' => $model,
            'valideEmails' => $valideEmails,
            'invalidEmails' => $invalideEmails
        ]);
    }


    public function actionReceive()
    {
        $connection = AMQPStreamConnection::create_connection([
            Yii::$app->params['RABBIT_HOST'], 
            Yii::$app->params['RABBIT_PORT'],
            Yii::$app->params['RABBIT_USERNAME'],
            Yii::$app->params['RABBIT_PASSWORD']
        ]);
        $channel = $connection->channel();

        $channel->queue_declare('email_queue', false, false, false, false);

        echo ' * Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function ($msg) {

            echo " * Message received", "\n";
            $data = json_decode($msg->body, true);

            $from = Yii::$app->params['senderName'];
            $from_email = Yii::$app->params['adminEmail'];
            $to_email = $data['emails'];
            $subject = $data['subject'];
            $message = $data['message'];

            $transporter = Swift_SmtpTransport::newInstance(Yii::$app->params['SMTP_HOST'], Yii::$app->params['SMTP_PORT'], Yii::$app->params['SMTP_PROTOCOL'])
              ->setUsername(Yii::$app->params['SMTP_USERNAME'])
              ->setPassword(Yii::$app->params['SMTP_PASSWORD']);

            $mailer = Swift_Mailer::newInstance($transporter);

            $message = Swift_Message::newInstance($transporter)
                ->setSubject($subject)
                ->setFrom(array($from_email => $from))
                ->setTo(array($to_email))
                ->setBody($message);

            $mailer->send($message);

            echo " * Message was sent", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('email_queue', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

    }
}
