@extends('layouts.tools')

@section('title')
    SelectTable
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
    <form id="getExcel" class="select-form" action="{{url('select/getExcel')}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <textarea class="form-control" name="sql" placeholder="不支持order by、group by、表别名、if/foreache等条件（可把相关代码删除，然后自定义数据条数）">{{old('sql')}}</textarea>
            </div>
            <div class="col-md-2 select-btn">
                <input class="form-control" name="quantity" type="text" placeholder="数据条数（可选）">
                <input id="setupExcel001" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_001">
                <input id="setupExcel002" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_002">
                <input id="setupExcel003" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_003">
                <input id="setupExcel004" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_004">
                <input id="setupExcel005" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_005">
                <input id="setupExcel006" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_006">
                <input id="setupExcel007" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_007">
                <input id="setupExcel008" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_008">
                <input id="setupExcel009" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_009">
                <input id="setupExcel010" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_010">
                <input id="getCode" class="btn btn-default btn-warning" type="button" value="Code">
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
