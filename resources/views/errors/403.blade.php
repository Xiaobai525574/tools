@extends('layouts.tools')

@section('title')
    Error
@stop

@section('content')
    <h3>{{$exception->getMessage()}}</h3>
@stop