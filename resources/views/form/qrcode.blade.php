<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="hidden" name="{{$name}}"/>

        <a class="btn btn-default btn-sm grid-qrcode" data-content="{{$qrcodeUrl}}" data-toggle='popover' tabindex='0'>
            <i class="fa fa-qrcode"></i>
        </a>

        @include('admin::form.help-block')

    </div>
</div>


<script>
    $(document).ready(function () {
        $('.grid-qrcode').popover({
            title: "扫描二维码",
            html: true,
            trigger: 'focus'
        });

    });
</script>

