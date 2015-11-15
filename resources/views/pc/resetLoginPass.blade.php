@extends('pc.frame')
@section('title', '重置密码')
@section('content')

<div>

<form action="">
   <input type="hidden" id='url' value="{!! empty(\Input::get('callback')) ? '/' : \Input::get('callback') !!}">
   {!! csrf_field() !!}
  <div>
    <span id='error'></span>
  </div>

  <div>
    <label>手机：</label>
    <input type="text" name="mobile"></input>  
    <label class="error" id='mobile_error' style="display:none"></label>
  </div>
  <div>
    <label>图片验证码：</label>
    <input type="text" name="img_code"></input>
    <img class="img_code" id="img_code" onclick="this.src='/verify/getimg?r='+Math.random();" src="/verify/getimg">
    <label class="error" id='img_code_error' style="display:none"></label>
  </div>
  <div>
    <label>手机验证码：</label>
    <input type="text" name="mobile_code"></input>  
    <button id="get_mobile_code" type="button">获取验证码</button>
    <label class="error" id='mobile_code_error' style="display:none"></label>
  </div>
  <div>
    <label>密码：</label>
    <input type="password" name="password"></input>  
    <label class="error" id='password_error' style="display:none"></label>
  </div>
  <div>
    <label>确认密码：</label>
    <input type="password" name="_password"></input>  
    <label class="error" id='_password_error' style="display:none"></label>
  </div>

  <div>
    <input type="button" id='submit' value='修改密码'></input>  
  </div>
</form>

</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/tool.js"></script>
<script src="/js/resetpass.js"></script>

@endsection
@stop

