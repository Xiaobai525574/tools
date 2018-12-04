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
    <form action="{{url('delete/createExcel')}}" method="post">
        @csrf
        <div class="container delete-form">
            <div class="row">
                <div class="col-md-5">
                    <textarea name="sql">{{old('sql')}}</textarea>
                </div>
                <div class="col-md-2 delete-btn">
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
