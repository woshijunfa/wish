

function T_isMobileFormatOk(mobile)
{
	var phonereg = /^((1[0-9]{2})|159|153)+\d{8}$/;
	return phonereg.test(mobile);
}

function T_isPayPasswordFormatOk(password)
{
	var phonereg = /^\d{6}$/;
	return phonereg.test(password);
}

function T_isEmailFormatOk(email)
{
	var emailreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	return emailreg.test(email);
}



function T_isPassFormat(password)
{
	return password.length >=8 && password.length <= 20;
}


