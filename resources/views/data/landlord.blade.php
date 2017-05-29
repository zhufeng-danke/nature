@extends('layouts.data')

@section('data-content')

    <form action="{{ route('data-contract-landlord') }}" method="post">
        <h3 style="text-align: center;">收房合同</h3>
        {!! csrf_field() !!}
        <div class="form-group">
            <label>开始日期</label>
            <input type="date" class="form-control" name="start_date" value="{{ $start_date or '' }}"
                   placeholder="日期格式：XXXX-XX-XX，如2017-05-01">
        </div>
        <div class="form-group">
            <label>结束日期</label>
            <input type="date" class="form-control" name="end_date" value="{{ $end_date or '' }}"
                   placeholder="日期格式：XXXX-XX-XX，如2017-05-11">
        </div>
        <div class="form-group">
            <label>公寓ID</label>
            <input type="number" name="suit_id" value="{{ $suit_id or '' }}" class="form-control">
        </div>
        <button type="submit" class="btn btn-default"> 查询 & 导出 </button>
    </form>

    {{--<table class="table table-bordered">--}}
        {{--<caption>查询结果: {{ $count or '' }}</caption>--}}
        {{--<thead>--}}
        {{--<tr>--}}
            {{--<th>#</th>--}}
            {{--<th>First Name</th>--}}
            {{--<th>Last Name</th>--}}
            {{--<th>Username</th>--}}
        {{--</tr>--}}
        {{--</thead>--}}
        {{--<tbody>--}}
        {{--<tr>--}}
            {{--<th scope="row">1</th>--}}
            {{--<td>Mark</td>--}}
            {{--<td>Otto</td>--}}
            {{--<td>@mdo</td>--}}
        {{--</tr>--}}
        {{--</tbody>--}}
    {{--</table>--}}






@endsection