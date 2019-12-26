@if(!isset($item['children']))
    <li>
        <a href="{{ Route::has($item['uri'])?route($item['uri'],[],false): admin_base_path($item['uri'])}}"><i
                class="fa {{$item['icon']}}"></i>
            <span>{{$item['title']}}</span>
        </a>
    </li>
@else
    <li class="treeview">
        <a href="#">
            <i class="fa {{$item['icon']}}"></i>
            <span>{{$item['title']}}</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            @foreach($item['children'] as $item)
                @include('adminE::partials.auto_menu', $item)
            @endforeach
        </ul>
    </li>
@endif
