<?php

namespace app\modules\weixin\controllers;

use app\components\Common;
use app\components\WeixinApi;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;

class WeixinController extends Controller
{
    /**
     * 请求微信获取用户CODE
     * @param string $url
     */
    public function actionWeixinLogin($url='')
    {
        if(empty($url)){
            Common::sendJson(1,'url');
        }
        $wxModel = new WeixinApi();
        $callbackurl = Url::toRoute(['/weixin/weixin/openid','backurl'=>$url],true);
        $usrl = $wxModel->getOauthUrl($callbackurl);
        $this->redirect($usrl);
    }

    /**
     * 通过CODE 获取用户openID
     * @param string $code
     * @param string $backurl
     */
    public function actionOpenid($code='',$backurl='')
    {

        if(empty($code)){
            Common::sendJson(1,'code');
        }

        if(empty($backurl)){
            $backurl = Url::toRoute(['/weixin/weixin/get-opendid']);
        }

        $wxModel = new WeixinApi();
        $token = $wxModel->getOauthToken($code);
        $tokenArr = JSON::decode($token);
        if(empty($tokenArr)){
            Common::saveLog('weixin','can not gei openid');
            Common::sendJson(999,'get openid');
        }else{
            if(isset($tokenArr['openid'])){
                $time = time();
                $sign =  md5($tokenArr['openid'].\Yii::$app->params['AlloweScret'].$time);
                $backurl = Common::setUrlLink($backurl,['openid'=>$tokenArr['openid'],'time'=>$time,'sign'=>$sign]);
                $this->redirect($backurl);
            }else{
                Common::sendJson(999,'get openid error',$tokenArr);
            }
        }

        Common::sendJson(999,'get openid');
    }

    public function actionTest()
    {
        Common::saveLog("test","back".$GLOBALS['HTTP_RAW_POST_DATA']);
    }

    /**
     * 生成订单
     */
    public function setOrder($openid,$backurl,$order_no,$body,$money)
    {

        $nowtime = time();
        $wxModel = new WeixinApi();
        $result = $wxModel->getOrderPay($openid,$backurl,$order_no,$body,$money);
        $return_code = isset($result['return_code'])?$result['return_code']:'FAIL';
        if(!empty($return_code)){
            if($return_code=='SUCCESS'){
                $prepay_id = isset($result['prepay_id'])?$result['prepay_id']:'';
                if(!empty($prepay_id)){
                    $data = ['timeStamp'=>$nowtime,'package'=>"prepay_id=".$prepay_id,'signType'=>'MD5','nonceStr'=>md5($nowtime),'appId'=>\Yii::$app->params['OTHERCONFIG']['WEIXIN']['appId']];

                    $dataSign=[];
                    foreach($data as $k=>$v){
                        $dataSign[$k]=$k."=".$v;
                    }

                    $sign = $wxModel->getPaySign($dataSign);
                    $data['paySign'] = $sign;
                    return $data;
                }
            }
        }

        return $result;
    }

    /**
     * 微信支付
     * @return string
     */
    public function actionPay()
    {
        $this->layout=false;
        $openid = isset($_REQUEST['openid'])?$_REQUEST['openid']:'';
        $money = isset($_REQUEST['money'])?intval($_REQUEST['money']):'';
        $callbackurl = isset($_REQUEST['callbackurl'])?$_REQUEST['callbackurl']:'';
        $body = isset($_REQUEST['body'])?$_REQUEST['body']:'shijia';
        $order_no = isset($_REQUEST['order_no'])?$_REQUEST['order_no']:'';

        if(empty($money)||empty($callbackurl)||empty($body)||empty($order_no)){
            var_dump('订单号|金额|回调地址|描述 缺失');
            exit;
        }

        if(empty($openid)){
            $backurl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $login_url = URL::toRoute(['/weixin/weixin/weixin-login','url'=>$backurl]);
            return $this->redirect($login_url);
        }else{
            $sign = isset($_REQUEST['sign'])?$_REQUEST['sign']:'';
            $time = isset($_REQUEST['time'])?$_REQUEST['time']:'';
            $signlocal =  md5($openid.\Yii::$app->params['AlloweScret'].$time);
            if($sign!=$signlocal){
                var_dump('非法请求');
                exit;
            }
        }

//        Common::saveLog("test","go:".$order_no);
        $data= $this->setOrder($openid,$callbackurl,$order_no,$body,$money);
        if(!isset($data['package'])){
            var_dump($data);
            exit;
        }

        return $this->render('index',['data'=>$data,'money'=>$money]);
    }

}
