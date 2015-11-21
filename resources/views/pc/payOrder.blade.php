@extends('pc.frame')
@section('title', '确认订单')
@section('content')

<div>

  <div class="notice">
    订单名称：{{$subject}}<br/>
    订单号：{{$order_id}}<br/>
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

  <div class="pay_method">
    <ul class="bank-list bank-list--xpay">
      <li class="item item left">
          <input id="check-alipay" class="radio ui-radio" type="radio" name="paytype"  checked="checked"  value="alipay">
          <label for="check-alipay" class="bank-logo" title="支付宝"><span class="bank bank--alipay">支付宝</span></label>
      </li>
      <li class="item item">
          <input id="check-tenpay" class="radio ui-radio" type="radio" name="paytype" value="tenpay">
          <label for="check-tenpay" class="bank-logo" title="财付通"><span class="bank bank--tenpay">财付通</span></label>
      </li>
      <li class="item item">
          <input id="check-wxqrpay" class="radio ui-radio" type="radio" name="paytype" value="wxqrpay">
          <label for="check-wxqrpay" class="bank-logo" title="微信支付"><span class="bank bank--wxqrpay">微信支付</span></label>
      </li>
    </ul>
  </div>

  <button id='pay_button'>提交确认支付</button>

</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/pingpp/pingpp-pc.js"></script>
<script src="/js/pay.js"></script>

@endsection
@stop



