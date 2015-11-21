
$("#pay_button").click(function(){

	var channel = $('.pay_method input[name="paytype"]:checked').val();

	var postdata = {};
	postdata['channel'] = channel;
	postdata['order_id'] = $('#order_id').text();

	//生成支付对象
	$.post("/pay/getPayChangeObject", postdata,
	function(data){

		if (data.code ==0) {
			pingppPc.createPayment(data.data['charge'], function(result, err){

		        alert(result);
		        alert(err);
		    });
		};
	
		alert(data.desc);

	});


});

function wap_pay(channel)
{
	$("#error").hide();

	var postdata = {};
	postdata['channel'] = channel;
	postdata['order_id'] = $('#order_id').text();

	//生成支付对象
	$.post("/pay/getPayChangeObject", postdata,
	function(data){

		if (data.code ==0) {
			pingpp.createPayment(data.data['charge'], function(result, err){

		        alert(result);
		        alert(err);
		    });
		};

		$("#error").text(data.desc);
		$("#error").show();

	});
}




// //侦测页面
// $().ready(function(){

// 	var postdata = {};
// 	postdata['_token'] = $("[name='_token']").val();
// 	postdata['trade_id'] = $('#trade_id').val();

// 	detarch_timer = setInterval(function(){

// 		//登录操作
// 		$.post("/trade/detachStatus", postdata,
// 		function(data){

// 			if (data.code == 0) 
// 			{
// 				if (data.data.trade_status == 'success') {
// 					onSuccess(data.data.return_url);
// 					return;
// 				}
// 				else if (data.data.trade_status == 'failed') 
// 				{
// 					window.location = "/error?desc=" + encodeURIComponent('订单支付失败，请重新发起支付！');
// 					return;
// 				}
// 				else{
// 					$('.expire_time').text(data.data.last_min);
// 					return;
// 				}
// 			}

// 			//支付失败
// 			else if (data.code == 3 || data.code == 4) 
// 			{
// 				window.location = "/error?desc=" + encodeURIComponent(data.desc);
// 			};
// 		});	

// 	},5000);

// });


