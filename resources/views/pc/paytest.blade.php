@extends('pc.frame')
@section('title', '登录')
@section('content')

<div>

<button id='paybtn'>支付</button>

</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/pingpp/pingpp-pc.js"></script>
<script src="/js/pingpp/pingpp.js"></script>

<script type="text/javascript">

$("#paybtn").click(function(){
/*

    pingpp.createPayment({!! $charge !!}, function(result, err){

        alert(result);
        alert(err);
    });
*/
    pingppPc.createPayment({!! $charge !!}, function(result, err){

        alert(result);
        alert(err);
    });

});

</script>

@endsection
@stop



