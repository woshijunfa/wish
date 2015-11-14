
$("#get_mobile_code").click(function(){

	clearerr();
	var mobile = $("[name='mobile']").val();
	var imgcode = $("[name='img_code']").val();
	var _token = $("[name='_token']").val();

	
	var isok = T_isMobileFormatOk(mobile);
	if (false == isok) {
        $("#mobile_error").text('请输入正确的手机号');
        $("#mobile_error").show();
        return;
	};

	if (imgcode.length <= 0) {
        $("#img_code_error").text('请填写图片验证码');
        $("#img_code_error").show();
        return;
	};

	var postdata = {};
	postdata['mobile'] = mobile;
	postdata['verifycode'] = imgcode;
	postdata['_token'] = _token;

	//发送注册手机号
	$.post('/verify/getphonecode?action=regist', postdata,
	function(result){
	 	switch(result.code)
	 	{
	 		case 0:
				sendsmsok();
		 		break;
		 	case 3:
		 		$("#img_code").attr('src','/verify/getimg?r='+Math.random());
		        $("#img_code_error").text(result.desc);
		        $("#img_code_error").show();
				break;
		 	case 4:
		        $("#mobile_error").text(result.desc);
		        $("#mobile_error").show();
				break;
			default:
		        $("#error").text(result.desc);
		        $("#error").show();
				break;
	 	}
	});

});

function clearerr()
{
	$("#mobile_error").hide();
	$("#img_code_error").hide();
	$("#mobile_code_error").hide();
	$("#password_error").hide();
	$("#_password_error").hide();
}

function sendsmsok()
{
	// 倒计时
	var i = 60;
	var btnGetValiCode = document.getElementById("get_mobile_code");
	$("#get_mobile_code").attr("disabled",true);
	btnGetValiCode.innerHTML = "<i class='time'>"+ i-- +"秒后重新获取</i>";
	var timer = setInterval(function(){
                btnGetValiCode.innerHTML = "<i class='time'>"+ i-- +"秒后重新获取</i>";
        },1000);
    setTimeout(function(){
        clearInterval(timer);
        waiting = false;
        btnGetValiCode.innerHTML = '获取验证码';
        $("#get_mobile_code").removeAttr("disabled");
	    },i*1000);
}

$("#submit").click(function(){

	var mobile = $("[name='mobile']").val();
	var mobile_code = $("[name='mobile_code']").val();
	var password = $("[name='password']").val();
	var _password = $("[name='_password']").val();
	var _token = $("[name='_token']").val();

	var isok = T_isMobileFormatOk(mobile);
	if (false == isok) {
        $("#mobile_error").text('请输入正确的手机号');
        $("#mobile_error").show();
        return;
	};

	if(mobile_code.length <= 0)
	{
        $("#mobile_code_error").text('请输入手机验证码');
        $("#mobile_code_error").show();
        return;
	}

	if(password.length <= 0)
	{
        $("#password_error").text('请输入密码');
        $("#password_error").show();
        return;
	}

	if(_password.length <= 0)
	{
        $("#_password_error").text('请输入确认密码');
        $("#_password_error").show();
        return;
	}

	if (password != _password) 
	{
        $("#_password_error").text('密码输入不一样，请重新输入');
        $("#_password_error").show();
        return;
	};

	var postdata = {};
	postdata['mobile'] = mobile;
	postdata['mobile_code'] = mobile_code;
	postdata['password'] = password;
	postdata['_token'] = _token;

	//发送注册手机号
	$.post('/user/regist', postdata,
	function(result){
	 	switch(result.code)
	 	{
	 		case 0:
	 			window.location = $('#url').val();
		 		break;
		 	case 3:
		        $("#mobile_code_error").text(result.desc);
		        $("#mobile_code_error").show();
				break;
		 	case 2:
		        $("#mobile_error").text(result.desc);
		        $("#mobile_error").show();
				break;
			default:
		        $("#error").text(result.desc);
		        $("#error").show();
				break;
	 	}
	});

});


