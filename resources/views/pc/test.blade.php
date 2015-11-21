@extends('pc.frame')
@section('title', '登录')
@section('content')

<div>
<style type="text/css">
.calendar_table
{
  margin-left: 100px;
}

td div
{
    width: 30px;
    height: 30px;
}


div.validday.cal_status_rest
{
  background: #aaa;
}

div.validday.cal_status_free
{
  background: green;
}

div.validday.cal_status_date
{
  background: red;
}

div.cal_selected{
  border: 2px solid #fff;
}

div.unvalidday
{
  background: #777;
}



</style>

<table class="calendar_table" border="1">
<tr>
  <th>一</th>
  <th>二</th>
  <th>三</th>
  <th>四</th>
  <th>五</th>
  <th class="calendar-table-weekend">六</th>
  <th class="calendar-table-weekend">日</th>
</tr>

@foreach ($cals as $calweek)
  <tr>
  @foreach ($calweek as $day)
    <td>
      <div class="<?php
      if($day['month'] == $month) echo 'validday ';
      else echo 'unvalidday ';
      echo $day['status'];
      ?>" id="{{$day['date']}}">
        {{$day['day']}}
        @if($day['status'] == 'free')
        <span class='price'>{{$day['price']}}￥</span>
        @endif
      </div>
    </td>
  @endforeach
  </tr>
@endforeach

</table>

</div>

<div id='calendarrrr'>

</div>

<div>
  <p class='text' id='selectedDays'>sss</p>
  <button id='order_button' > 马上预定 </button>
</div>

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/calendar.js"></script>


<script type="text/javascript">

$(document).ready(function(){
    calendar_setmonth('2015-11');
    calendar_setUserId(1);
    calendar_setContainer('calendarrrr');
    calendar_load();
});


$("div.validday").click(function(){

    alert( $(this).attr('id'));


});
</script>

@endsection
@stop


