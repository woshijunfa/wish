
$("#get_mobile_code").click(function(){

	var mobile = $('#mobile').val();
	var imgcode = $('#img_code').val();
	
	var isok = T_isMobileFormatOk(mobile);
	if (false == isok) {
        $("#error").text('请输入正确的手机号');
        $("#error").show();
        return;
	};

	if (imgcode.length <= 0) {
        $("#error").text('请填写图片验证码');
        $("#error").show();
        return;
	};

	//发送注册手机号
	$.post('/verify/getphonecode', postdata,
	function(result){
	 	switch(result.code)
	 	{
	 		//验证成功进行token跨域书写
	 		case 0:
	 			$("#submit").text('注册中...');
		 		break;
			default:
		 		registObj.showerr('注册失败，请重新尝试');
		 		registObj.resetBtn();
		 		$("#submit").removeAttr("disabled");
				break;
	 	}
	});

});




