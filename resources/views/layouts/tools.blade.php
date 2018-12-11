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
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
    @section('css')
    @show
</head>
<body>
<div class="flex-center full-height">
    <div class="container">
        <div class="alert alert-info" role="alert">请手动将数据类型调整为文字列^-^</div>
        @yield('content', 'nothing')
    </div>
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
