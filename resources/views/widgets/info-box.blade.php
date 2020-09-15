<div {!! $attributes !!}>
    <div class="inner">
        <h3>{{ $info }}</h3>

        <p>{{ $name }}</p>
    </div>
    <div class="icon">
        <i class="fa fa-{{ $icon }}"></i>
    </div>

    @if($isShowLink)
        <a href="{{ $link }}" class="small-box-footer">
            {{ trans('admin.more') }}&nbsp;
            <i class="fa fa-arrow-circle-right"></i>
        </a>
    @else
        <div class="small-box-footer">
            <i class="fa"></i>
        </div>
        {{--        <a href="javascript:volid(0);" class="small-box-footer">--}}
        {{--                暂无更多&nbsp;--}}
        {{--            <i class="fa fa-arrow-circle-right"></i>--}}
        {{--        </a>--}}
    @endif
</div>
