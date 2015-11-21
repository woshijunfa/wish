@extends('h5.frame')
@section('title', '确认订单')
@section('content')

<div>

  <div class="notice">
    订单名称：{{$subject}}<br/>
    订单号：<span id='order_id'>{{$order_id}}</span><br/>
    订单日期：{{date('Y-m-d H-i-s',$created_at)}}<br/>
    出行日期：{{$order_dates}}<br/>
    导游：{{$nickname}}<br/>
    手机：{{$mobile}}<br/>
    邮箱：{{$email}}<br/>
    头像：{{$head_image}}<br/>
  </div>

  <div class="money_info">
    订单金额：{{$total_fee}}<br/>
  </div>

<span style="display:none;color:red;" class="error" id='error'></span><br/>
    <span class="up" onclick="wap_pay('alipay_wap')">支付宝支付</span><br/>
    <span class="up" onclick="wap_pay('upacp_wap')">银联支付</span><br/>
<!--     <span class="up" onclick="wap_pay('bfb_wap')">百度钱包 WAP</span><br/>
    <span class="up" onclick="wap_pay('wx')">微信支付</span><br/>
    <span class="up" onclick="wap_pay('jdpay_wap')">京东支付 WAP</span><br/>
    <span class="up" onclick="wap_pay('yeepay_wap')">易宝支付 WAP</span><br/> -->


</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/pingpp/pingpp.js"></script>
<script src="/js/pay.js"></script>

@endsection
@stop



