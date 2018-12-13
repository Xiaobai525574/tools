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
    <form id="getExcel" method="post">
        @csrf
        <div class="row select-row">
            <div class="col-md-2">
                <input id="addTable" class="btn btn-default btn-warning col-md-6" type="button" value="Add Table">
                <input id="removeTable" class="btn btn-default btn-warning col-md-6" type="button" value="Remove">
            </div>
            <div class="col-md-2">
                <input class="form-control" name="id" placeholder="id（替换**）">
            </div>
        </div>
        <div class="row select-row select-table">
            <div class="col-md-2">
                <input class="form-control" name="tableNames[]" placeholder="表名">
            </div>
            <div class="col-md-2">
                <input class="col-md-6 form-control" name="tableRows[]" placeholder="数据条数">
            </div>
            <div class="col-md-4">
                <input class="col-md-6 form-control" name="tableWheres[]" placeholder="where条件（标红，用逗号分隔）">
            </div>
            <div class="col-md-4">
                <input class="col-md-6 form-control" name="tableSelects[]" placeholder="select条件（标橙，用逗号分隔）">
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

