<?php
if (!defined('BY')) {
    die('Access Denied');
}

$order_id = base64_decode(getapk('dd'));
$order_data = data("order", "dd", $order_id);
//dd($order_data);
if($order_data['status'] == 2) {
    go("/", true);
}
$title = $sys_webname . "_收银台";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?php echo $title; ?></title>
    <meta name="description" content="A demo of a card payment on Stripe"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="/app/static/stripe/global.css"/>
    <?php if ($order_data['id']) { ?>
        <script src="https://js.stripe.com/v3/"></script>
        <script src="//cdn.polyfill.io/v1/polyfill.min.js" async defer></script>
        <script src="/app/static/stripe/client.js" defer></script>
    <?php } ?>
</head>

<body>
<!-- Display a payment form -->
<form id="payment-form">
    <?php if ($order_data['id']) { ?>
        <input type="hidden" name="dd" id="dd" value="<?php echo $order_data['dd']; ?>">
<!--        <div class="str-logo">-->
<!--            <img src="/app/static/stripe/stripe-icon.png" alt="">-->
<!--        </div>-->
        <div id="card-element"><!--Stripe.js injects the Card Element--></div>
        <button id="submit">
            <div class="spinner hidden" id="spinner"></div>
            <span id="button-text">支付</span>
        </button>
        <p id="card-error" role="alert"></p>
        <p class="result-message hidden">
            恭喜您支付成功
            <a href="<?php echo chtml('user', 'index'); ?>"> 返回个人中心</a>
        </p>
    <?php } else { ?>
        <p id="card-error" class="result-message" role="alert">对不起，订单信息无效！<a href="/">返回首页</a></p>
    <?php } ?>
</form>
</body>
</html>
