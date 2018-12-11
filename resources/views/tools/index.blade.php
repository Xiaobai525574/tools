@extends('layouts.tools')

@section('title')
    首页
@stop

@section('content')
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li><a href="{{url('select/index')}}">单表Select</a></li>
            <li><a href="{{url('select/getExcelByTables')}}">多表Select</a></li>
            <li><a href="{{url('delete/index')}}">Delete</a></li>
        </ul>
    </nav>
@stop