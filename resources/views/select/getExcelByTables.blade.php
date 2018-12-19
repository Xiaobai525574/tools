@extends('layouts.tools')

@section('title')
    SelectTables
@stop

@section('css')
    <style>
        .select-row {
            margin-bottom: 10px;
        }
    </style>
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                   href="#collapseTwo"
                   aria-expanded="false" aria-controls="collapseTwo">
                    提示：输入框可能支持更便捷的输入方式^-^（戳一下看详情）
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
                <pre>
                    <span class="bg-info">标橙支持输入方式：</span>
                        1、逗号分隔(有无空格、换行、表别名、重复字段都没关系):
                        "t1.bnk_cod
                        ,t1.dup_sfp_cen_jho_id
                        ,t1.tyo_kis_hi"
                    <span class="bg-info">标红支持输入方式：</span>
                        1、逗号分隔(有无空格都没关系，不支持换行、表别名):
                        "bnk_cod,    skj_flg"
                        2、xml文件直接复制代码（有无空格、换行、表别名、重复字段都没关系，<span class="bg-danger">但字段名（例：t3.bnk_cod）必须在"="左边）</span>:
                        "t3.bnk_cod = #{bankCd,jdbcType=CHAR}
                        AND t3.skj_flg = #{sakujoFlg,jdbcType=NUMERIC}"
                    <span class="bg-info">resultMap Xml支持输入方式：</span>
                        1、直接复制&lt;resultMap&gt;...&lt;/resultMap&gt;标签：
                        "&lt;resultMap type="jp.co.nttdata.erm.base.biz.sql1.dto.pkts.PKTS1002Output" id="PKTS1002Output"&gt;
                            &lt;result column="bnk_cod" property="bankCd"/&gt;
                            &lt;result column="dup_sfp_cen_jho_id" property="dialupSyuhaiCenterInfoId"/&gt;
                            &lt;result column="tyo_kis_hi" property="tekiyoKaishiDate"/&gt;
                        &lt;/resultMap&gt;"
                    <span class="bg-info">代码input支持输入方式：</span>
                        1、直接复制包含有#{**,**}的字符串(只要包含#{**,**}即可，格式无关)：
                        "bnk_cod = #{bankCd,jdbcType=CHAR}
                        AND dup_sfp_cen_jho_id = #{dialupSyuhaiCenterInfoId,jdbcType=CHAR}
                         tyo_kis_hi = #{tekiyoKaishiDate,jdbcType=CHAR}
                        AND skj_flg = #{sakujoFlg,jdbcType=NUMERIC}"
                </pre>
            </div>
        </div>
    </div>
    <form id="getExcel" method="post">
        @csrf
        <div class="row select-row">
            <div class="col-md-2 btn-group">
                <input id="addTable" class="btn btn-default btn-primary col-md-6" type="button" value="Add Table">
                <input id="removeTable" class="btn btn-default btn-primary col-md-6" type="button" value="Remove">
            </div>
            <div class="col-md-2">
                <input class="form-control" id="id" name="id" placeholder="id(例:execPKTS0005)">
            </div>
            <div class="col-md-2">
                <input class="form-control" id="assertions" placeholder="resultMap(详见提示)">
            </div>
            <div class="col-md-2">
                <input class="form-control" id="inputs" placeholder="代码input(详见提示)">
            </div>
        </div>
        <div class="row select-row select-table">
            <div class="col-md-2">
                <input class="form-control" name="tableNames[]" placeholder="表名(例:tp_kuzjho_kydcen_knrkuz)">
            </div>
            <div class="col-md-2">
                <input class="col-md-6 form-control" name="tableRows[]" placeholder="数据条数(例:6)">
            </div>
            <div class="col-md-4">
                <input class="col-md-6 form-control" name="tableWheres[]"
                       placeholder="标红字段(详见提示)">
            </div>
            <div class="col-md-4">
                <input class="col-md-6 form-control" name="tableSelects[]" placeholder="标橙字段(详见提示)">
            </div>
        </div>
        <div class="row select-row select-submit">
            @for($i=1; $i<=12; $i++)
                <div class="col-md-2 btn-group">
                    <input class="col-md-6 btn btn-default btn-info" type="submit" name="excelNum"
                           @if($i < 10) value="00{{$i}}" @else value="0{{$i}}" @endif>
                    <input class="col-md-6 getCode btn btn-default btn-warning" type="button"
                           @if($i < 10) data-num="00{{$i}}" @else data-num="0{{$i}}" @endif value="Code">
                </div>
                @if($i == 6)
        </div>
        <div class="row select-row select-submit">
            @endif
            @endfor
        </div>
        <hr/>
        <textarea class="form-control" id="code" rows="18"></textarea>
    </form>
@stop

@section('script')
    <script type="text/javascript">
        $("#addTable").click(function () {
            var table = $("#getExcel>.select-table:last").clone();
            $("#getExcel>.select-table:last").after(table);
        });

        $("#removeTable").click(function () {
            if ($("#getExcel>.select-table").length > 1) {
                $("#getExcel>.select-table:last").remove();
            }
        });
        $(".getCode").click(function () {
            $.ajax({
                url: "{{url('select/getCodeByTables')}}",
                type: "POST",
                data: {
                    'id': $("#id").val(),
                    'inputs': $("#inputs").val(),
                    'assertions': $("#assertions").val(),
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

