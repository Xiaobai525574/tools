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
                          placeholder="1、不支持if/foreache等条件（可把相关代码删除，然后自定义数据条数）"
                >{{old('xml')}}</textarea>
            </div>
            <div class="col-md-2 select-btn">
                <div>
                    <input class="form-control" name="quantity" type="text" placeholder="数据条数（可选）">
                </div>
                @for($i=1; $i<=6; $i++)
                    <div class="col-md-12 btn-group" role="group" aria-label="...">
                        <input class="col-md-6 btn btn-default btn-info" type="submit" name="excelNum" value="00{{$i}}">
                        <input class="col-md-6 getCode btn btn-default btn-warning" type="button" data-num="00{{$i}}"
                               value="Code">
                    </div>
                @endfor
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
                    'excelNum': $(this).attr('data-num')
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
