<?php
// 海外支付类

use Stripe\Stripe;
use Stripe\Webhook;

class paymentController
{

    private $db;
    private $stripe_api_key;
    private $stripe_api_test_key;
    private $stripe_notiKey;
    private $stripe_notiKeyTest;

    public function __construct()
    {
        global $db;
        $this->db = $db;
        // 接口key https://stripe.com/docs/api
        $this->stripe_api_key = $GLOBALS['conf']['stripe_api_key'];
        $this->stripe_api_test_key = $GLOBALS['conf']['stripe_api_test_key'];

        // 回调key
        $this->stripe_notiKey = $GLOBALS['conf']['stripe_notiKey'];
        $this->stripe_notiKeyTest = $GLOBALS['conf']['stripe_notiKeyTest'];

        // 模型赋值通用 key
        Stripe::setApiKey($this->stripe_api_test_key);
    }

    // 支付方法
    public function paySubmit()
    {
        try {
            // retrieve JSON from POST body
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);
            if($json_obj) {
                // 订单真实性检测
                $order_data = data("order", "dd", $json_obj->dd);
                if(empty($order_data['id'])) {
                    http_response_code(500);
                    exit;
                }

                $paymentIntent = \Stripe\PaymentIntent::create([
                    'description' => $order_data['description'],
                    'amount' => $order_data['price'] * 100,
                    'currency' => 'cny',
                    'payment_method_types' => ['card'],
                    'metadata' => ['integration_check' => 'accept_a_payment', 'dd' => $order_data['dd']],
                ]);

                // 储存订单对比条件
                $paymentIntentJson = json_encode($paymentIntent);
                $this->db->q("update #@__order set status=1,payment_order_id='{$paymentIntent->id}',payment_order_data='{$paymentIntentJson}' where id='{$order_data['id']}' and status!=2");

                $output = [
                    'clientSecret' => $paymentIntent->client_secret,
                ];

                echo json_encode($output);
            } else {
                http_response_code(500);
            }
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function notify()
    {
    }

    public function notifyTest()
    {
        $endpoint_secret = $this->stripe_notiKeyTest;

        $payload = @file_get_contents('php://input');
        $sig_header = @$_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        file_put_contents("HTTP_STRIPE_SIGNATURE_TEST0.txt", $payload);

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }
        file_put_contents("HTTP_STRIPE_SIGNATURE_TEST1.txt", json_encode($event));
        switch ($event->type) {
            case 'payment_intent.succeeded':
                // 返回订单数据
                $succeeded = $event->data->object->charges->data[0];
                file_put_contents("HTTP_STRIPE_SIGNATURE_TEST2.txt", json_encode($succeeded));
                if ($succeeded->status == 'succeeded') {
                    //目前先直接拿金额进行对比，后续再考虑汇率换算
//                    $where = ['payment_order_id' => $succeeded->payment_intent];
                    $dbOrder = data("order", "payment_order_id", $succeeded->payment_intent, " and status=1");
                    if(!empty($dbOrder['id'])) {
                        if ($dbOrder['price'] * 100 == $succeeded->amount) {
                            $this->notifyDealwith($dbOrder);
                            echo "status Ok";
                        } else {
                            echo "status amount Error";
                        }
                    } else {
                        echo "status invalid";
                    }
                }
                break;
            default:
                // Unexpected event type
                http_response_code(400);
                exit();
        }

        http_response_code(200);
    }

    // 支付完回调需求处理
    private function notifyDealwith($dbOrder)
    {
        $this->db->q("update #@__order set status=2 where id='{$dbOrder['id']}'");
        $this->userNumberTime($dbOrder);
        $this->userCoursePay($dbOrder);
    }

    // 会员期限调整
    private function userNumberTime($dbOrder)
    {
        if($dbOrder['type'] == 1) {
            $user_data = data("us", "id", $dbOrder['uid']);
            if($user_data['huiyuantime'] < time()) {
                $huiyuantime = huiyuantimeGet($dbOrder['huiyuantime']);
                $this->db->q("update #@__us set levels=1, huiyuantime='{$huiyuantime}' where id='{$dbOrder['uid']}'");
            } else {
                $huiyuantime = $user_data['huiyuantime'] + huiyuantimeGet($dbOrder['huiyuantime'], "time");
                $this->db->q("update #@__us set levels=1, huiyuantime='{$huiyuantime}' where id='{$dbOrder['uid']}'");
            }
        }
    }

    // 课程购买记录
    private function userCoursePay($dbOrder)
    {
        // 处理 type 0 课程订单
        if($dbOrder['type'] == 0) {
            $tbl = "user_course";
            $course_data = data("course", "id", $dbOrder['kcid']);
            if($course_data['id']) {
                $formdata = getformdata($tbl, 0);
                $formdata['uid'] = $dbOrder['uid'];
                $formdata['kid'] = $course_data['id'];
                $formdata['kctype'] = $course_data['kctype'];
                $formdata['status'] = 1;
                $formdata['createtime'] = time();
                $id = saveformdata($formdata, $tbl);
            }
        }
    }
}