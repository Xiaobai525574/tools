@extends('layouts.tools')

@section('title')
    首页
@stop

@section('content')
    <div>
        <h4>2018/1/16</h4>
        <pre>
            1、新增支持9000条数据（无重复）生成^-^
            2、优化日期格式（8位和17位长度）^-^
            3、修复2位长度数据超过90条出现0开头数据的问题^-^
            4、修复3位长度数据超过900条出现0开头数据问题^-^
            5、修复8位长度非日期数据异常问题（感谢郭世豪同学的BUG反馈）^-^
        </pre>
        <h4>2018/1/9</h4>
        <pre>
            1、设定默认字体为'ＭＳ Ｐゴシック'^-^
        </pre>
        <h4>2018/1/8</h4>
        <pre>
            1、修复重复使用某张表，excel生成会报错的问题^-^
        </pre>
        <h4>2018/1/3</h4>
        <pre>
            1、新增支持标识主键（填充绿色字段）唯一约束（字体红色字段）^-^
        </pre>
        <h4>2018/12/31</h4>
        <pre>
            1、新增支持多表关联代码、Excel生成^-^
            2、新增支持将Excel数据写入代码^-^
            3、修复sheet页表名过长问题（感谢崔莹莹同学的BUG反馈）^-^
            4、新增表名、数据条数空格过滤等^-^
            5、修复请求超时^-^
            6、新增对方法名、>等判断条件的自动过滤^-^
        </pre>
        <h4>2018/12/19</h4>
        <pre>
            1、新增支持1000条数据生成^-^
            2、修复select代码生成bug(感谢任老师的反馈)^-^
            3、新增支持assertThat处的数据写入(请先生成Excel，然后工具会把Excel最后一行中相应的数据写入代码中)^-^
        </pre>
        <h4>2018/12/18</h4>
        <pre>
            1、新增select代码生成^-^
        </pre>
        <h4>2018/12/14</h4>
        <pre>
            1、新增默认选中左上单元格^-^
            2、新增过滤重复字段功能（标红、标橙）^-^
        </pre>
        <h4>2018/12/13</h4>
        <pre>
            1、新增标红、标橙输入框支持更便捷的输入方式^-^
            2、修复科学计数法格式的日期报错问题^-^
        </pre>
    </div>
@stop