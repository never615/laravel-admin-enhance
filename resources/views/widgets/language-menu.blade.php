<li class="dropdown messages-menu" id="language-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-language"></i>
    </a>
    <ul class="dropdown-menu">
        <li>
            <!-- inner menu: contains the actual data -->
            <ul class="menu">
                @foreach($languages as $key => $language)
                    <li><!-- start message -->
                        <a class="language" href="javascript:void(0);" data-id="{{$key}}" data-lang="{{$language}}">
                            {{$language}}
                            @if($key == $current)
                                <i class="fa fa-check pull-right"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    </ul>
</li>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".language").click(function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let lang = $(this).data('lang');
            let $link = $(this);
            
            // 添加加载状态
            let originalHtml = $link.html();
            $link.html(lang + ' <i class="fa fa-spinner fa-spin"></i>');
            $link.closest('ul').find('a').addClass('disabled');
            
            $.post("{{ admin_url('/locale') }}", {locale: id})
                .done(function (response) {
                    // 成功后刷新页面
                    location.reload();
                })
                .fail(function (xhr) {
                    alert('语言切换失败: ' + xhr.responseText);
                    $link.html(originalHtml);
                    $link.closest('ul').find('a').removeClass('disabled');
                });
        });
    });
</script>