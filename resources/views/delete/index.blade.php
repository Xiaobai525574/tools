@extends('layouts.tools')

@section('title')
    Delete
@stop

@section('css')
    <style>
        .delete-form textarea {
            width: 100%;
            height: 360px;
        }

        .delete-form .delete-btn input {
            margin: 10px 0;
            width: 100%;
            display: block;
        }
    </style>
@stop

@section('content')
    <form id="getExcel" class="delete-form" action="{{url('delete/getExcel')}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <textarea class="form-control" name="sql">{{old('sql')}}</textarea>
            </div>
            <div class="col-md-2 delete-btn">
                <input class="form-control" name="quantity" type="text" placeholder="数据条数（可选）">
                <input id="getExcel" class="btn btn-default btn-info" type="submit" value="Excel">
                <input id="getCode" class="btn btn-default btn-info" type="button" value="Code">
            </div>
            <div class="col-md-5">
                <textarea class="form-control" id="code">{{old('sql')}}</textarea>
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
                url: "{{url('delete/getCode')}}",
                type: "POST",
                success: function (data) {
                    //    todo:
                    alert(data);
                }
            });
        });
    </script>
@stop
