@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 style="text-align: center;">公寓</h2>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">收房合同</h3>
            </div>
            <div class="panel-body">
                <a href="{{ route('data-contract-landlord') }}" class="btn btn-md btn-success">查询 & 导出</a>
            </div>
        </div>

        <h2 style="text-align: center;">房间</h2>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">出房合同</h3>
            </div>
            <div class="panel-body">
                <a href="{{ route('data-contract-customer') }}" class="btn btn-md btn-success">查询 & 导出</a>
            </div>
        </div>

    </div>
@endsection
