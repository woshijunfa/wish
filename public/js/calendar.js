

var calData = {};

function calendar_setmonth(var month)
{calData.month = month;}

function calendar_setUserId(var userId)
{calData.userId = userId;}


function calendar_load(var month,var userId)
{
	var postdata = {};
	postdata['month'] = calData.month;
	postdata['user_id'] = calData.userId;

	//发送注册手机号
	$.post('/user/getUserCalendar', postdata,
	function(result)
	{
	 	switch(result.code)
	 	{
	 		case 0:
	 			alter(result.data.month);
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
}




