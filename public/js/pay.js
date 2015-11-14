
var detarch_timer = null;

	function onSuccess(returnurl)
	{
		$('#paybox').hide();
		$('#success_box').show();
		$('#likefanhui').attr('href',returnurl);

		if (detarch_timer != null) {
			clearInterval(detarch_timer);
		};

		var i = 3;

		var timer = setInterval(function(){
			$('span.time').text(i);
			i -= 1;
		},1000);

		setTimeout(function(){
			clearInterval(timer);
			window.location = returnurl;
		},i*1000);

	    var btnGetValiCode = document.getElementById("getmobilecode");
		btnGetValiCode.innerHTML = "<i class='time'>"+ i-- +"秒后重新获取</i>";
	
	}

	$("#getmobilecode").click(function(){
		var _token =  $("[name='_token']").val();

		var postdata = {};
		postdata['_token'] = _token;
		postdata['action'] = 'pay_password';

		//登录操作
		$.post("/verify/sendsms", postdata,
		function(data){
			if (data.code == 0) {
	            $("#error").hide();

	            // 倒计时
	        	var i = 60;
	            var btnGetValiCode = document.getElementById("getmobilecode");
	            $("#getmobilecode").attr("disabled",true);
	            btnGetValiCode.innerHTML = "<i class='time'>"+ i-- +"秒</i>";
	            var timer = setInterval(function(){
					btnGetValiCode.innerHTML = "<i class='time'>"+ i-- +"秒</i>";
				},1000);
				setTimeout(function(){
					clearInterval(timer);
					waiting = false;
					btnGetValiCode.innerHTML = '获取验证码';
					$("#getmobilecode").removeAttr("disabled");
				},i*1000);

				return;
			}
			else
			{
				$('#error').text(data.desc);
				$('#error').show();
			};

		});	

	});


  $(".submit").click(function(){

	var _token =  $("[name='_token']").val();
	var password = $('#password').val();
	var trade_id = $('#trade_id').val();
	var code = $('#mobilecode').val();

	if (code.length <=0 && !$('#mobile_outbox').is(":hidden")) {
		$('#error').text('请输入手机验证码');
		$('#error').show();
		return;	
	};


	if (!T_isPayPasswordFormatOk(password)) 
	{
		$('#error').text('请输入正确的支付密码');
		$('#error').show();
		return;	
	};

	$('.submit').attr("disabled",true);
	$('.submit').val("正在支付中...");

	//post数据
	var postdata = {};
	postdata['_token'] = _token;
	postdata['password'] = password;
	postdata['trade_id'] = trade_id;
	postdata['mobile_code'] = code;

	//登录操作
	$.post("/trade/dopay", postdata,
	function(data){
		if (data.code == 0) {
			onSuccess(data.data.return_url);
			return;
		}
		else if (data.code ==3) {
			$('#mobile_outbox').show();
		}
		else if (data.code == 4 && data.data.count>=3) {
			$('#mobile_outbox').show();
		};

		if (data.code !=7) {
			$('.submit').val("确认支付");
			$('.submit').removeAttr("disabled");
			$('#error').text(data.desc);
			$('#error').show();
		}
	});	

  });

  // 按回车键注册
  $(document).keydown(function(event){
      if(event.keyCode == 13){
          $("#submit").click();
      }
  })


//侦测页面
$().ready(function(){

	var postdata = {};
	postdata['_token'] = $("[name='_token']").val();
	postdata['trade_id'] = $('#trade_id').val();

	detarch_timer = setInterval(function(){

		//登录操作
		$.post("/trade/detachStatus", postdata,
		function(data){

			if (data.code == 0) 
			{
				if (data.data.trade_status == 'success') {
					onSuccess(data.data.return_url);
					return;
				}
				else if (data.data.trade_status == 'failed') 
				{
					window.location = "/error?desc=" + encodeURIComponent('订单支付失败，请重新发起支付！');
					return;
				}
				else{
					$('.expire_time').text(data.data.last_min);
					return;
				}
			}

			//支付失败
			else if (data.code == 3 || data.code == 4) 
			{
				window.location = "/error?desc=" + encodeURIComponent(data.desc);
			};
		});	

	},5000);

});


