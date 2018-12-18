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

        .select-form .select-btn > div {
            padding: 5px 0;
        }
    </style>
@stop

@section('content')
    <form id="getExcel" class="select-form" action="{{url('select/getExcel')}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <textarea id="xml" class="form-control" name="xml"
                          placeholder="1、不支持order by、group by、表别名、if/foreache等条件（可把相关代码删除，然后自定义数据条数）2、where条件仅支持“=”条件，可把其他（<=、<>等）条件暂时替换成“=”，不影响结果。"
                >{{old('xml')}}</textarea>
            </div>
            <div class="col-md-2 select-btn">
                <div>
                    <input class="form-control" name="quantity" type="text" placeholder="数据条数（可选）">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_001">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="001" value="Code">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_002">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="002" value="Code">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_003">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="003" value="Code">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_004">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="004" value="Code">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_005">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="005" value="Code">
                </div>
                <div class="btn-group" role="group" aria-label="...">
                    <input class="btn btn-default btn-info" type="submit" name="excelName"
                           value="setup_**_006">
                    <input class="getCode btn btn-default btn-warning" type="button" data-num="006" value="Code">
                </div>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" id="code"></textarea>
            </div>
        </div>
    </form>
    @if(session('table') == 'notExists')
        <div>表不存在^-^</div>
    @endif
@stop

@section('script')
    <script type="text/javascript">
        $(".getCode").click(function () {
            $.ajax({
                url: "{{url('select/getCode')}}",
                type: "POST",
                data: {
                    'xml': $("#xml").val(),
                    'num': $(this).attr('data-num')
                },
                success: function (data) {
                    if (data.status == "success") {
                        $("#code").val(data.info);
                    }
                }
            });
        });
    </script>
@stop
