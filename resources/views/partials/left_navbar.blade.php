<ul class="nav navbar-nav hidden-sm">
    <li class="dropdown">
        <a href="" class="dropdown-toggle btn btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <i class="fa fa-fw fa-plus visible-xs-inline-block"></i>
            <span class="ng-scope">快捷访问</span> <span class="caret"></span>
        </a>
        <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
            @foreach ($speedy as $key=>$value)
                <li><a href="{{$key}}" class="ng-scope">{{$value}}</a></li>
            @endforeach

        </ul>
    </li>
</ul>
