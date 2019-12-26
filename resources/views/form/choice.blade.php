<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="hidden" class="{{$class}}" name="{{$name}}"/>

        <div class="app_stage_section_cnt app_stage_section_cnt_Edit">
            <div class="apiApp_mod_rowList">
                <div class="apiApp_mod_rowItem apiApp_mod_basicInfoSection_scopeItem">
                    <div class="apiApp_mod_rowItem_cnt">
                        <div class="ww_groupSelBtn">
                            <a id="editVisible" href="javascript:" class="ww_groupSelBtn_add">添加</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin::form.help-block')
</div>
<div id="choice-dialog" class="x-window-mask light modal1 scrollable fadein" hidden style="z-index: 8022;">
    <div class="x-dialog" style="width: 610px;">
        <div class="dialog-header"><span class="title">选择</span><i class="icon-close-normal"></i></div>
        <div class="dialog-body has-padding has-footer">
            <div class="x-layout-table">
                <div class="x-layout-table-row">
                    <div class="x-layout-table-item fx_base_select fx_member_select" widgetname="memberSelect"
                         style="width: 570px; height: 450px;">
                        <ul id="mt-choice-selected" class="select-list">
                            @if(!is_null($value)|$value=json_decode($value,true))
                                @foreach($value as $item)
                                    <li class="select-item" data-id="{{$item['id']}}" data-type="{{$item["type"]}}">
                                        <i class="select-icon {{$item["type"]=="users"?'icon-member-normal':'icon-department'}} "></i>
                                        <span>{{$item["text"]}}</span>
                                        <span class="remove-btn"><i class="icon-close-large"></i></span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                        <div id="choice-select-menu" class="select-menu">
                            <i id="mt_search" class="icon-search"></i><i id="icon-refresh" class="fa fa-refresh"></i>
                            <div class="search-input">
                                <input id="search_content"><i id="mt_search2" class="icon-search"></i></div>
                            @if(!is_null($selects))
                                @foreach($selects as $type=>$text)
                                    <div class="mt-choice-select select-btn" data-type="{{$type}}">{{$text}}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="select-pane">
                            <div class="depart-pane">
                                <div class="fui_tree x-department-tree select-department">
                                    <ul id="tree" class="tree">

                                    </ul>
                                    <div id="mt_load" class="x-btn" style="height: 32px; line-height: 30px;">
                                        <span id="mt_load_text">点击加载更多</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dialog-footer">
            <div class="dialog-btn-row">
                <div class="dialog-btn cancel-btn x-btn style-white" widgetname="confirmBtnCancel"
                     style="height: 32px; line-height: 30px;">
                    <span>取消</span></div>
                <div class="dialog-btn ok-btn x-btn style-green" widgetname="confirmBtnOK"
                     style="height: 32px; line-height: 30px;">
                    <span>确定</span></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        //原始数据
        var originalValues = @json($value);
        //当前页面操作中的数据
        var currentValues = originalValues;
        if (typeof (currentValues) == "undefined" || currentValues == null) {
            currentValues = [];
        }


        //选项卡请求数据的url
        var dataUrl =@json($dataUrls);

        var clazz = "{{$class}}";
        var selectorClazz = '.' + clazz.replace(/ /g, ".");
        //用来保存数据
        var hiddenInput = $(selectorClazz).closest('.fields-group').find('input[name="{{$name}}"]');
        var currentPage = 1;

        var currentType;

        renderFormDatas();
        setValue();


        /**
         * 初始化选项卡数据
         */
        function initSelect() {
            //添加默认select
            var firstSelect = $("#choice-select-menu").find(".select-btn").first();
            firstSelect.addClass("select");

            currentType = firstSelect.attr("data-type");
            currentPage = 1;
            $("#mt_load_text").text("点击加载更多");
            //加载默认选中项的数据
            renderDatas();
        }


        /**
         * 选项卡的点击事件
         */
        $(".mt-choice-select").on("click", function () {
            // console.log("选项卡点击");
            //更改默认选中
            $(this).parent().children().removeClass("select");
            $(this).addClass("select");

            currentPage = 1;
            currentType = $(this).attr("data-type");
            $("#mt_load_text").text("点击加载更多");


            renderDatas();

        });


        $("#icon-refresh").on("click", function () {
            currentPage = 1;
            renderDatas();
        });


        /**
         * 加载更多数据
         */
        $("#mt_load_text").on("click", function () {
            // console.log("加载更多数据");
            currentPage = currentPage + 1;
            renderDatas();
        });


        /**
         * 渲染form上的数据
         */
        function renderFormDatas() {

            $(".ww_groupSelBtn_item").remove();

            if (typeof (currentValues) != "undefined" && currentValues != null) {
                var tempform = "";
                for (var i = 0; i < currentValues.length; i++) {
                    var data = currentValues[i];

                    tempform = tempform + '<div class="ww_groupSelBtn_item" data-id="' + data.id + '">' +
                        '              <span class="removeItem ww_commonImg ww_commonImg_GroupDelSel" type="visibleMembers"' +
                        '        item-id="' + data.id + '" item-type="' + data.type + '"><\/span>' +
                        '       <span class="ww_groupSelBtn_item_text">' + data.text + '<\/span><\/div>';


                }

                $(".ww_groupSelBtn").prepend(tempform);

                //添加按钮的点击事件
                /**
                 * 表单页面移除
                 */
                $(".removeItem").on("click", function () {
                    var itemId = $(this).attr("item-id");
                    var itemType = $(this).attr("item-type");
                    console.log(itemId);
                    console.log(itemType);

                    removeCurrentValues(itemId, itemType);
                    $(this).parent().remove();
                    console.log("remove");

                });

            }
        }


        $("#mt_search").on("click", function () {
            // console.log("search");
            //style="display: block;"
            $(".search-input").css("display", "block");
            $(".search-input input").focus();
        });

        $("#mt_search2").on("mousedown", function () {
            var text = $(this).parent().find("input").val();
            // console.log(text);
            //搜索
            renderDatas(text);
            $(".search-input").css("display", "");

        });

        $(".search-input input").keydown(function (e) {
            if (e.which == 13) {
                var text = $(this).parent().find("input").val();
                // console.log(text);
                //搜索
                renderDatas(text);
                $(".search-input").css("display", "");


            }
        });


        $(document).keydown(function (event) {
            switch (event.keyCode) {
                case 13:
                    return false;
            }
        });


        $(".search-input input").blur(function () {
            $(".search-input").css("display", "");
        });


        /**
         * 渲染选项卡对应的可选择的数据
         */
        var renderDatas = function (query, page) {

            // console.log(page);

            if (typeof (page) != "undefined" && page != null) {
                // console.log(page);

                currentPage = 1;
            } else {
                page = currentPage;
            }


            var queryData = {'per_page': 200, "page": page};

            if (typeof (query) != "undefined" && query != null) {
                queryData.q = query;
            }

            // console.log(queryData);

            doAjax(dataUrl[currentType], 'GET', queryData, function (responseData) {
                // console.log(responseData);
                var tree = $(".fui_tree #tree");

                var datas = responseData.data;

                if (typeof (query) != "undefined" && query != null) {
                    if (datas.length <= 0) {
                        tree.empty();
                        currentPage = 1;
                        $("#mt_load_text").text("无搜索结果");
                        return;
                    } else {
                        $("#mt_load_text").text("加载更多数据");
                    }
                } else {
                    if (datas.length <= 0) {
                        currentPage = currentPage - 1;
                        $("#mt_load_text").text("没有更多数据");
                        $("#clear_search").remove();
                        return;
                    } else {
                        $("#mt_load_text").text("加载更多数据");
                        $("#clear_search").remove();
                    }
                }


                var tempLi = "";
                for (var i = 0; i < datas.length; i++) {
                    var data = datas[i];

                    var tempIcon = "icon-member-normal";
                    if (currentType != "users") {
                        tempIcon = "ico_docu";
                    }

                    var select = "";

                    //同时比对已选数据,存在的则置为选中状态
                    if (typeof (currentValues) != "undefined" && currentValues != null) {
                        for (var j = 0; j < currentValues.length; j++) {
                            if ((currentValues[j].id == data.id) && (currentValues[j].type = currentType)) {
                                select = "select";
                            }
                        }
                    }

                    tempLi += '<li id="tree_1" class="choice-check-item level0" tabindex="0" hidefocus="true" >' +
                        '                <a data-text="' + data.text + '" data-id="' + data.id + '" data-type="' + currentType + '" id="tree_1_a" class="level0" treenode_a="" onclick="" target="_blank">' +
                        '                        <span id="tree_check" class="button x-tree-check x-check ' + select + ' full" treenode_check="">' +
                        '                            <i class="x-iconfont"><\/i><\/span>' +
                        '                        <i class="button ' + tempIcon + ' x-iconfont" style="width:0px;height:0px;"><\/i>' +
                        '                        <span id="tree_span">' + data.text + '<\/span> <\/a> <\/li>';

                }

                tree.html(tempLi);


                /**
                 * 选项卡上顶部内容,带选择内容的点击事件
                 */
                $(".choice-check-item").on("click", function () {
                    // console.log("checkbox click");

                    var choiceItem = $(this).find("a").first();

                    var dataId = choiceItem.attr("data-id");
                    var dataType = choiceItem.attr("data-type");
                    var dataText = choiceItem.attr("data-text");

                    // console.log(dataId);
                    // console.log(dataType);


                    var span = choiceItem.find("span").first();


                    var selected = $("#mt-choice-selected");

                    if (span.hasClass('select')) {
                        span.removeClass("select");
                        //点击之后,改为未选中状态,并且移除数据从已选择框

                        selected.find("[data-id=" + dataId + "][data-type=" + dataType + "]").remove();

                        removeCurrentValues(dataId, dataType);
                    } else {
                        span.addClass("select");
                        //点击之后,改为选中状态,并且设置数据到已选择框

                        var tempIcon = dataType == "users" ? "icon-member-normal" : "icon-department";

                        var temp = "<li class=\"select-item\" data-id=\"" + dataId + "\" data-type=\"" + dataType + "\">\n" +
                            "                                        <i class=\"select-icon " + tempIcon + " \"></i>\n" +
                            "                                        <span>" + dataText + "</span>\n" +
                            "                                        <span class=\"remove-btn\"><i class=\"icon-close-large\"></i></span>\n" +
                            "                                    </li>";

                        selected.append(temp);

                        addCurrentValues(dataId, dataType, dataText)

                        //添加按钮的点击事件
                        /**
                         * 选项卡中 已选择条目的移除事件
                         */
                        $(".select-item .remove-btn").on("click", function () {
                            console.log("remove");
                            var parent = $(this).parent();

                            var dataId = parent.attr("data-id");
                            var dataType = parent.attr("data-type");

                            removeCurrentValues(dataId, dataType);

                            parent.remove();
                        });

                    }
                });
            });
        };


        /**
         * 对话框取消按钮
         */
        $(".dialog-btn.cancel-btn").on("click", function () {
            currentValues = originalValues;
            setValue();
            $("#choice-dialog").hide();
        });

        /**
         * 对话框确认按钮
         */
        $(".dialog-btn.ok-btn").on("click", function () {
            setValue();
            $("#choice-dialog").hide();
            //更新form表单的数据
            renderFormDatas();

        });

        /**
         * 对话框关闭按钮
         */
        $(".icon-close-normal").on("click", function () {
            currentValues = originalValues;
            setValue();
            $("#choice-dialog").hide();
        });

        /**
         * 表单页面 添加
         */
        $("#editVisible").on("click", function () {
            // console.log("add");
            $("#choice-dialog").show();

            initSelect();
        });

        /**
         * 选项卡中 已选择条目的移除事件
         */
        $(".select-item .remove-btn").on("click", function () {
            console.log("remove");
            var parent = $(this).parent();

            var dataId = parent.attr("data-id");
            var dataType = parent.attr("data-type");

            removeCurrentValues(dataId, dataType);

            parent.remove();
        });


        /**
         * 表单页面移除
         */
        $(".removeItem").on("click", function () {
            var itemId = $(this).attr("item-id");
            var itemType = $(this).attr("item-type");
            console.log(itemId);
            console.log(itemType);

            removeCurrentValues(itemId, itemType);
            $(this).parent().remove();
            console.log("remove");

        });


        var removeCurrentValues = function (id, type) {
            for (var i = 0; i < currentValues.length; i++) {
                if ((currentValues[i].id == id) && (currentValues[i].type = type)) {
                    currentValues.splice(i, 1);
                }
            }

            setValue();
        };


        var addCurrentValues = function (id, type, text) {

            var newValue = {
                "id": id,
                "type": type,
                "text": text
            };

            currentValues.push(newValue);
            setValue();
        };

        /**
         * 数据变动修改hiddenInput的值,用于最后提交数据
         */
        function setValue() {
            var tempValues = JSON.stringify(currentValues);
            // console.log(tempValues);

            hiddenInput.val(tempValues);
        };


    });


</script>


<style>

</style>
