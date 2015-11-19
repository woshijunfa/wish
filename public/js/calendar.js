

var calData = {};
calData.cals = {};

function calendar_setmonth(month)
{calData.month = month;}

function calendar_setUserId(userId)
{calData.userId = userId;}

function calendar_setContainer(elid)
{calData.elid = elid;}


function calendar_load()
{
	var postdata = {};
	postdata['month'] = calData.month;
	postdata['user_id'] = calData.userId;

	//发送注册手机号
	$.post('/user/getUserCalendar', postdata,
	function(result)
	{
		if (result.code == 0) {
			return calendar_load_success(result.data);
		}else
		{
			return calendar_loadError(result);
		}
	});
}

function calendar_load_success(cals)
{
	calData.curMonth = cals.month;
	calData.curMonthCals = cals.data;
	calData.cals[calData.curMonth] = cals.data;
	calendar_build();
}

function calendar_build()
{

	var bodyHtml = '<div class="monthTitle">' + calData.curMonth + "</div>";

	var rowBeginTag = '<tr>';
	var rowHtml = rowBeginTag;

	var temCals = calData.cals[calData.curMonth];
	for(var date in temCals)  
	{
		var cal = temCals[date];
		csscls = calendar_p_getCalClassList(cal)
		var cellHtml = '<td><div name="' + cal.date + '" class="'+csscls+'">';
		cellHtml += '<span class="cal_day">' + cal.day + '</span>';
		if (typeof(cal.price) != "undefined")  cellHtml += '<span class="cal_price">' + cal.price + '￥</span>';
		cellHtml += '</div></td>'
		rowHtml += cellHtml;

		//当周日的时候换行
		if (cal.week == 0) 
		{
			bodyHtml += rowHtml + '</tr>';
			rowHtml = rowBeginTag;
		};
	};

	//构建html+class
	var headerHtml = '<table class="calendar_table" border="1"><tr><th>一</th><th>二</th><th>三</th><th>四</th><th>五</th><th>六</th><th>日</th></tr>';
	var endHtml = '</table>';
	fullHtml = headerHtml + bodyHtml + endHtml;

	$("#"+calData.elid).html(fullHtml);
	$("div.validday").click(calendar_valid_day_click);
	$("div.unvalidday").click(calendar_unvalid_day_click);
}

function calendar_p_getCalClassList(cal)
{
	var baseClass = 'cal_day';

	//是否本月
	if (cal.month == calData.curMonth)
	{
		baseClass = baseClass + ' validday';
		baseClass = baseClass + ' cal_status_' + cal.status;
	} 
	else baseClass = baseClass + ' unvalidday';

	return baseClass;
}

function calendar_loadError(result)
{
	$("#"+calData.elid).html('<span>获取失败</span>');
}

function calendar_get_select_class(cssClass)
{
	arrClass = cssClass.split(' ');
	if (!arrClass.in_array('cal_selected')) cssClass += ' cal_selected';
	return cssClass;
}

function calendar_get_unselect_class(cssClass)
{
	arrClass = cssClass.split(' ');
	if (arrClass.in_array('cal_selected'))
	{
		var newCssClass = '';
		for(i=0;i<arrClass.length;i++)
		{
			if (arrClass[i] != 'cal_selected')
			{
				if ('' == newCssClass) newCssClass = arrClass[i];
				else newCssClass += " " + arrClass[i];
			}
		}

		return newCssClass;
	}
	return cssClass;
}

function calendar_is_select_by_class(cssClass)
{
	return arrClass.in_array('cal_selected');	
}

function calendar_getInfoByDate(date)
{
	return calData.cals[calData.curMonth][date];
}

function calendar_reverse_select(date,cssClass)
{
	var cal = calData.cals[calData.curMonth][date];
	if (cal.status != 'free') return;

	var arrClass = cssClass.split(' ');
	var isSelected = arrClass.in_array('cal_selected');
	if (isSelected ) 
	{
		cssClass = calendar_get_unselect_class(cssClass);
		calData.cals[calData.curMonth][date].isSelected = false;
	}
	else
	{
		cssClass = calendar_get_select_class(cssClass);
		calData.cals[calData.curMonth][date].isSelected = true;
	}

	var expr = "#"+calData.elid+" [name='"+date+"']";
	$(expr).attr('class',cssClass);	
}

//获取用户选中的日期
function calendar_get_selectd_day()
{
	var selectedDays = new Array();
	for (var month in calData.cals) 
	{
		monthCals = calData.cals[month];
		for (var day in monthCals)
		{
			cal = monthCals[day];
			if (cal.isSelected == true) selectedDays.push(day);
		} 
	};

	return selectedDays;
}

function calendar_valid_day_click()
{
	var day = $(this).attr('name');
	var cssClass = $(this).attr('class');

	//反向选择
	calendar_reverse_select(day,cssClass);

	//获取选中的日期
	var selectedDates = calendar_get_selectd_day();

	var strDates = selectedDates.join(' ');
	$('#selectedDays').text(strDates);
}


function calendar_unvalid_day_click()
{
	var day = $(this).attr('name');
	cal = calendar_getInfoByDate(day);

	calendar_setmonth(cal.month);
	calendar_load();
}


Array.prototype.S=String.fromCharCode(2);
Array.prototype.in_array=function(e){
    var r=new RegExp(this.S+e+this.S);
    return (r.test(this.S+this.join(this.S)+this.S));
};
Array.prototype.in_array=function(e){
    var r=new RegExp(this.S+e+this.S);
    return (r.test(this.S+this.join(this.S)+this.S));
};


$('#order_button').click(function(){
	var selectedDates = calendar_get_selectd_day();

	var postdata = {};
	postdata['user_id'] = calData.userId;
	postdata['dates'] 	= selectedDates;

	//发送注册手机号
	$.post('/order/createOrder', postdata,
	function(result)
	{
		if (result.code == 0) {
			return calendar_load_success(result.data);
		}
	});
});

