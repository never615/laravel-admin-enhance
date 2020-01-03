<style>
    .quick-menu {
        float: left;
    }

    .quick-menu .dropdown button {
        height: 40px;
        padding: 10px;
        margin: 5px;
    }

    .quick-menu-text {
        /*font-size: 0.8em;*/
    }
</style>
<ul class="quick-menu nav navbar-nav hidden-sm">
    <li class="dropdown">
        <button class="dropdown-toggle btn btn-primary" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
            <span class="ng-scope quick-menu-text">快捷访问</span> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
            @foreach ($speedy as $key=>$value)
                <li><a href="{{$key}}" class="ng-scope">{{$value}}</a></li>
            @endforeach
        </ul>
    </li>
</ul>
