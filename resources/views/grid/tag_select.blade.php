<div class="form-inline pull-left">
    <div class="input-group input-group-sm">
        {{--<span class="input-group-addon"><strong>设置标签</strong></span>--}}
        <select class="form-control party-tag-select" style="width: 100%;" name="party-tag-select">
            <option value="">选择</option>
            @foreach($options as $select => $option)
                <option value="{{$select}}">{{$option}}</option>
            @endforeach
        </select>
    </div>
</div>

<script>
    $(document).ready(function () {

        var selectedRows = function () {
            var selected = [];
            $('.grid-row-checkbox:checked').each(function () {
                selected.push($(this).data('id'));
            });

            return selected;
        };

        var partyTagSelect = $(".party-tag-select");
        partyTagSelect.select2({
            placeholder: "{{$placeholder}}",
            allowClear: true
        });


        partyTagSelect.on("change", (function () {
//            console.log("change");

            var tagId = this.value;
//            console.log(tagId);
            var text = partyTagSelect.find("option:selected").text();
//            console.log(text);

            //调用投放接口进行投放
            layer.confirm('确认给选中用户添加标签:' + text + '吗?', {
                btn: ['确认', '取消'] //按钮
            }, function () {
                doAjax("{{$url}}", "POST", {
                    _token: LA.token,
                    ids: $.admin.grid.selected(),
                    tag_id: tagId
                }, function (data) {
                    $.pjax.reload('#pjax-container');
                    layer.msg('设置成功', {icon: 1});
//                    toastr.success("设置成功");
                });
            });

        }));

    });


</script>
