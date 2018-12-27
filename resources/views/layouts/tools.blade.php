<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Tools')</title>


    <!-- Bootstrap -->
    <link href="{{url('/bootstrap-3.3.7-dist/css/bootstrap.css')}}" rel="stylesheet">
    <style>
        html, body {
            font-family: 'Raleway', sans-serif;
        }

        .container {
            margin-top: 60px;
        }

    </style>
    @section('css')
    @show
</head>
<body>
<div class="container">
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li @if($navActive == 'select') class="active" @endif><a href="{{url('select/index')}}">单表Select</a></li>
            <li @if($navActive == 'selects') class="active" @endif><a
                        href="{{url('select/getExcelByParameters')}}">多表Select</a></li>
            <li @if($navActive == 'delete') class="active" @endif><a href="{{url('delete/index')}}">Delete</a></li>
            <li @if($navActive == 'log') class="active" @endif><a href="{{url('updateInfo')}}">更新日志</a></li>
        </ul>
    </nav>
    <div class="alert alert-info" role="alert">广播：被标记颜色的单元格会变成标准类型，请手动格式化为文字列类型^-^</div>
    @yield('content', 'nothing')
</div>
<!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
<script src="{{url('/js/jquery-3.3.1.min.js')}}"></script>
<!-- 加载 Bootstrap 的所有 JavaScript 插件。你也可以根据需要只加载单个插件。 -->
<script src="{{url('/bootstrap-3.3.7-dist/js/bootstrap.js')}}"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@section('script')
@show
</body>
</html>
