@extends('pc.frame')
@section('title', '注册')
@section('content')

<div>

<form action="">

  <div>
    <span id='error'></span>
  </div>

  <div>
    <label>昵称：</label>
    <input type="text" name="nickname"></input>  
  </div>
  <div>
    <label>手机：</label>
    <input type="text" name="mobile"></input>  
  </div>
  <div>
    <label>图片验证码：</label>
    <input type="text" name="img_code"></input>
    <img class="img_code" id="img_code" onclick="this.src='/verify/getimg?r='+Math.random();" src="/verify/getimg">
  </div>
  <div>
    <label>手机验证码：</label>
    <input type="text" name="mobile_code"></input>  
    <input type="button" id='get_mobile_code' value='获取验证码'/>
  </div>
  <div>
    <label>邮箱：</label>
    <input type="text" name="email"></input>  
  </div>
  <div>
    <label>密码：</label>
    <input type="password" name="password"></input>  
  </div>
  <div>
    <label>确认密码：</label>
    <input type="password" name="_password"></input>  
  </div>

  <div>
    <input type="button" id='submit' value='注册'></input>  
  </div>
</form>

</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/tool.js"></script>
<script src="/js/regist.js"></script>

@endsection
@stop

