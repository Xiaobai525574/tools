@extends('layouts.tools')

@section('title')
    Select
@stop

@section('css')
    <style>
        .select-form textarea {
            width: 100%;
            height: 360px;
        }

        .select-form .select-btn input {
            margin: 10px 0;
            width: 100%;
            display: block;
        }
    </style>
@stop

@section('content')
    <form id="getExcel" action="{{url('select/getExcel')}}" method="post">
        @csrf
        <div class="container select-form">
            <div class="row">
                <div class="col-md-5">
                    <textarea name="sql">{{old('sql')}}</textarea>
                </div>
                <div class="col-md-2 select-btn">
                    <input name="quantity" type="text" placeholder="数据条数">
                    <input id="getExcel" class="btn btn-default btn-info" type="submit" value="Excel">
                    <input id="getCode" class="btn btn-default btn-info" type="button" value="Code">
                </div>
                <div class="col-md-5">
                    <textarea id="code">{{old('sql')}}</textarea>
                </div>
            </div>
        </div>
    </form>
    @if(session('table') == 'notExists')
        <div>表不存在^-^</div>
    @endif
@stop

@section('script')
    <script type="text/javascript">
        $("#getCode").click(function () {
            $.ajax({
                url: "{{url('select/getCode')}}",
                type: "POST",
                success: function (data) {
                //    todo:
                    alert(data);
                }
            });
        });
    </script>
@stop
