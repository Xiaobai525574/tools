@extends('layouts.tools')

@section('title')
    SelectTables
@stop

@section('content')
    <form id="getExcel" method="post">
        @csrf
        <div class="row table">
            <div class="col-md-3">
                <input class="form-control" name="tableNames[]" placeholder="表名">
            </div>
            <div class="col-md-3">
                <input class="col-md-6 form-control" name="tableRows[]" placeholder="数据条数">
            </div>
            <div class="col-md-3">
                <input class="col-md-6 form-control" name="tableWheres[]" placeholder="where条件（标红，用逗号分隔）">
            </div>
            <div class="col-md-3">
                <input class="col-md-6 form-control" name="tableSelects[]" placeholder="select条件（标橙，用逗号分隔）">
            </div>
        </div>
        <div class="row">
            <input id="addTable" class="btn btn-default btn-warning col-md-2" type="button" value="Add Table">
            <input id="removeTable" class="btn btn-default btn-warning col-md-2" type="button" value="Remove Table">
            <div class="col-md-2">
                <input class="form-control" name="id" placeholder="id（替换**）">
            </div>
        </div>
        <div class="row">
            <input id="setupExcel001" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_001">
            <input id="setupExcel002" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_002">
            <input id="setupExcel003" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_003">
            <input id="setupExcel004" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_004">
            <input id="setupExcel005" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_005">
            <input id="setupExcel006" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_006">
        </div>
        <div class="row">
            <input id="setupExcel007" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_007">
            <input id="setupExcel008" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_008">
            <input id="setupExcel009" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_009">
            <input id="setupExcel010" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_010">
            <input id="setupExcel011" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_011">
            <input id="setupExcel012" class="btn btn-default btn-info col-md-2" type="submit" name="excelName"
                   value="setup_**_012">
        </div>
    </form>
@stop

@section('script')
    <script type="text/javascript">
        $("#addTable").click(function () {
            var table = $("#getExcel>.table:last").clone();
            $("#getExcel>.table:last").after(table);
        });

        $("#removeTable").click(function () {
            if ($("#getExcel>.table").length > 1) {
                $("#getExcel>.table:last").remove();
            }
        });
    </script>
@stop

