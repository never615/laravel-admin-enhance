<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="file" class="{{$class}}" name="file" {!! $attributes !!} />
        <input id="{{$id}}" type="hidden" name="{{$name}}" {!! $attributes !!}/>
        @include('admin::form.help-block')

    </div>
</div>

<script>

    $(document).ready(function () {

        var clazz = "{{$class}}";
        var selectorClazz = '.' + clazz.replace(/ /g, ".");


        var setValue = function () {
            var temp = $(selectorClazz).closest('.fields-group').find('input[name="{{$name}}"]');

            // console.log('set value');
            // console.log(files);

            if (files.length > 0) {
                // temp.val(JSON.stringify(files));
                temp.val(files.join(","));
            } else {
                temp.val("");
            }
//            console.log(temp.val());
        };

        var files = [];

        // var file = $(selectorClazz).closest('.fields-group').find('input[name="file"]');
        var file = $(selectorClazz).closest('.fields-group').find('input[class="{{$class}}"]');


        file.on('fileremoved', function (event, id, index) {
//            console.log('file remove');
//            console.log('id = ' + id + ', index = ' + index);
            files.splice(index, 1);
//            console.log(files);
            setValue();
        });


        file.on('filedeleted', function (event, key, jqXHR, data) {
//            console.log("file delete");
//            console.log('Key = ' + key);
            files = [];
            setValue();


        });

        file.on('fileuploaded', function (event, data, previewId, index) {
            var response = data.response;
//            console.log('File uploaded triggered');
//            console.log(response);
            files.push(response.key);
//            console.log(files);
            setValue();
        });
    });


</script>