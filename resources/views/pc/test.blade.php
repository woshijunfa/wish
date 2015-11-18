@extends('pc.frame')
@section('title', '登录')
@section('content')

<div>

{{var_dump($cals)}}

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

div.validday.rest
{
  background: #aaa;
}

div.validday.free
{
  background: green;
}

div.validday.date
{
  background: red;
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

<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/tool.js"></script>
<script src="/js/login.js"></script>


<script type="text/javascript">
$("td div").click(function(){

    alert( $(this).attr('id'));


});
</script>

@endsection
@stop


