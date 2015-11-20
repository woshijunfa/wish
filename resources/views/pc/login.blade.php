@extends('pc.frame')
@section('title', '登录')
@section('content')

<div>

<form action="">
   <input type="hidden" id='url' value="{!! $url or '/' !!}">
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
    <label>密码：</label>
    <input type="password" name="password"></input>  
    <label class="error" id='password_error' style="display:none"></label>
  </div>
  <div>
    <label>保持登陆：</label>
    <input type="checkbox" name="remember_me"></input>  
  </div>

  <div>
    <input type="button" id='submit' value='登录'></input>  
  </div>
</form>

</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/tool.js"></script>
<script src="/js/login.js"></script>

@endsection
@stop



