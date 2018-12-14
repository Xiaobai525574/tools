@extends('layouts.tools')

@section('title')
    SelectTables
@stop

@section('css')
    <style>
        .select-row {
            margin-bottom: 10px;
        }

        .select-submit input {
            width: 100%;
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
                    提示：标红、标橙输入框支持更便捷的输入方式^-^（戳一下看详情）
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
                <pre>
                标橙支持输入方式：
                    1、逗号分隔(有无空格、换行、表别名、重复字段都没关系):
                    "t1.bnk_cod
                    ,t1.dup_sfp_cen_jho_id
                    ,t1.tyo_kis_hi"
                标红支持输入方式：
                    1、逗号分隔(有无空格都没关系，不支持换行、表别名):
                    "bnk_cod,    skj_flg"
                    2、xml文件直接复制代码（有无空格、换行、表别名、重复字段都没关系，<span class="text-danger">但字段名（例：t3.bnk_cod）必须在"="左边）</span>:
                    "t3.bnk_cod = #{bankCd,jdbcType=CHAR}
                    AND t3.skj_flg = #{sakujoFlg,jdbcType=NUMERIC}"
                </pre>
            </div>
        </div>
    </div>
    <form id="getExcel" method="post">
        @csrf
        <div class="row select-row">
            <div class="col-md-2">
                <input id="addTable" class="btn btn-default btn-warning col-md-6" type="button" value="Add Table">
                <input id="removeTable" class="btn btn-default btn-warning col-md-6" type="button" value="Remove">
            </div>
            <div class="col-md-2">
                <input class="form-control" name="id" placeholder="id(例:execPKTS0005)">
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
                       placeholder="标红字段(例:bnk_cod,stn_cod,kuz_sbt_cod)">
            </div>
            <div class="col-md-4">
                <input class="col-md-6 form-control" name="tableSelects[]" placeholder="标橙字段(例:kuz_id,trh_syr_hi)">
            </div>
        </div>
        <div class="row select-row select-submit">
            <div class="col-md-2">
                <input id="setupExcel001" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_001">
            </div>
            <div class="col-md-2">
                <input id="setupExcel002" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_002">
            </div>
            <div class="col-md-2">
                <input id="setupExcel003" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_003">
            </div>
            <div class="col-md-2">
                <input id="setupExcel004" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_004">
            </div>
            <div class="col-md-2">
                <input id="setupExcel005" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_005">
            </div>
            <div class="col-md-2">
                <input id="setupExcel006" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_006">
            </div>
        </div>
        <div class="row select-row select-submit">
            <div class="col-md-2">
                <input id="setupExcel007" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_007">
            </div>
            <div class="col-md-2">
                <input id="setupExcel008" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_008">
            </div>
            <div class="col-md-2">
                <input id="setupExcel009" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_009">
            </div>
            <div class="col-md-2">
                <input id="setupExcel010" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_010">
            </div>
            <div class="col-md-2">
                <input id="setupExcel011" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_011">
            </div>
            <div class="col-md-2">
                <input id="setupExcel012" class="btn btn-default btn-info" type="submit" name="excelName"
                       value="setup_**_012">
            </div>
        </div>
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
    </script>
@stop

