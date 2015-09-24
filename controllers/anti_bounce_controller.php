<?php
/*
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                   ###    ####   ###    #   #    ###

             Copyright 2013 ULURU.CO.,LTD. All Rights Reserved.

*/

/**
 * AntiBounce Controller
 *
 * @package     app
 * @subpackage  Controller
 * @since       2015/08/12
 * @author      t_maehira@uluru.jp
 * @version     Shufti 3.0.0
 */



require VENDORS . 'autoload.php';
use Aws\Sns\Message as Message;
use Aws\Sns\MessageValidator as MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException as InvalidSnsMessageException;

class AntiBounceController extends AntiBounceAppController
{
    public $name = 'AntiBounce';

    public function beforeFilter()
    {
        $this->Auth->allow('*');
    }

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->modelClass = null;
    }

    public function receive()
    {
        // SNS からのメッセージを取得
        $message = Message::fromRawPostData();

        // SNS からの通知かどうか確認
        if (! $this->checkNotificateBySns($message)) {
            $error = 'Can\'t verify SNS messages to ensure that they were sent by AWS.';
            $this->log($error);
            return $error;
        }

        $detail = json_decode($message['Message'], true);

        // TopicArn の確認 (AWS内でユニークなキー)
        if (! $this->checkSnsTopic($message['TopicArn'], $detail['mail']['source'])) {
            $error = 'check';
            $this->log($error);
            return $error;
        }

        $targetEmail = $detail['bounce']['bouncedRecipients'][0]['emailAddress'];

$targetEmail = 'dev_shufti+280798@uluru.jp';

        // 各フィールドをアップデート
        $saveData = array();
        extract(Configure::read('AntiBounce.data'));

        $primaryId = $this->getPrimaryValueByEmail(
            $model,
            $primaryKey,
            $mailField,
            $targetEmail
        );

        $saveData[$model] = $fields;
        $saveData[$model][$primaryKey] = $primaryId;
        $keys = array_keys($fields);

        $saved = ClassRegistry::init($model)->save($saveData, true, $keys);

        if (! $saved) {
            $this->log('Error: Failed update.');
            $this->log(ClassRegistry::init($model)->validationErrors);
        }
    }

    private function getPrimaryValueByEmail($model, $primaryKey, $mailField, $email)
    {
        $result = ClassRegistry::init($model)->find(
            'first',
            array(
                'recursive' => -1,
                'fields' => $primaryKey,
                'conditions' => array(
                    $mailField => $email
                )
            )
        );
        return $result[$model][$primaryKey];
    }

    // SNS からの通知かどうか確認
    private function checkNotificateBySns($message)
    {
        $messageValidator = new MessageValidator();
        return $messageValidator->isValid($message);
    }

    // 受け取った通知内容と設定ファイルと相違ないかを確認
    private function checkSnsTopic($topic, $mail)
    {
        $settings = Configure::read('AntiBounce');
        return $topic == $settings['topic'] && $mail == $settings['mail'];
    }
}

/* vim: set et ts=4 sts=4 sw=4 fenc=utf-8 ff=unix : */
