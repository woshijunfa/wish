
function clearerr()
{
	$("#mobile_error").hide();
	$("#password_error").hide();
}

$("#submit").click(function(){

	clearerr();

	var mobile = $("[name='mobile']").val();
	var password = $("[name='password']").val();
	var _token = $("[name='_token']").val();
	var remember_me = $("[name='remember_me']").is(':checked');

	var isok = T_isMobileFormatOk(mobile);
	if (false == isok) {
        $("#mobile_error").text('请输入正确的手机号');
        $("#mobile_error").show();
        return;
	};

	if(password.length <= 0)
	{
        $("#password_error").text('请输入密码');
        $("#password_error").show();
        return;
	}

	var postdata = {};
	postdata['mobile'] = mobile;
	postdata['password'] = password;
	postdata['remember_me'] = remember_me;	
	postdata['_token'] = _token;

	//发送注册手机号
	$.post('/user/login', postdata,
	function(result)
	{
	 	switch(result.code)
	 	{
	 		case 0:
	 			window.location = $('#url').val();
		 		break;
		 	case 3:
		        $("#error").text(result.desc);
		        $("#error").show();
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


