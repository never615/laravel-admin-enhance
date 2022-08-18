<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
@endif

<!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page"
      @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>
<div class="login-box">
    <div class="login-logo">
        <a href="{{ admin_url('/') }}"><b>{{config('admin.name')}}</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">{{ trans('admin.login') }}</p>

        <form action="{{ admin_url('auth/login') }}" method="post" id="form_submit">
            <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

                @if($errors->has('username'))
                    @foreach($errors->get('username') as $message)
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}
                        </label><br>
                    @endforeach
                @endif
                <div id="verify-password" style="position: relative">
                    <input type="text" class="form-control" placeholder="{{ trans('admin.username') }}" name="username"
                           value="{{ old('username') }}" id="username">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                @if($errors->has('mobile'))
                    @foreach($errors->get('mobile') as $message)
                        <label class="control-label" for="inputError" style="color: #dd4b39"><i
                                class="fa fa-times-circle-o"></i>{{$message}}
                        </label><br>
                    @endforeach
                @endif
                <div id="verify-number" style="display: none; position: relative">
                    <input type="text" class="form-control" placeholder="手机号码"
                           onkeyup="value=value.replace(/[^\d]/g,'')" maxlength=11 id="mobile" name="mobile"
                           value="{{ old('phone') }}"
                           disabled>
                    <span class="glyphicon glyphicon-phone form-control-feedback"></span>
                </div>

            </div>
            <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

                @if($errors->has('password'))
                    @foreach($errors->get('password') as $message)
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}
                        </label><br>
                    @endforeach
                @endif
                <div id="verify-password1" style="position: relative">
                    <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}"
                           name="password" value="{{ old('password') }}" id="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                @if($errors->has('verify_number'))
                    @foreach($errors->get('verify_number') as $message)
                        <label class="control-label" for="inputError" style="color: #dd4b39"><i
                                class="fa fa-times-circle-o"></i>{{$message}}
                        </label><br>
                    @endforeach
                @endif
                <div id="verify-number1" style="display: none;">
                    <input type="text" class="form-control" style="width: 225px;float: left " placeholder="请输入验证码"
                           name="verify_number" id="verify_number">
                    <input type="button" id="send-sms" style="height: 34px; width: 95px" onclick="sendmsg()"
                           value="发送验证码">
                </div>
            </div>
            <!-- 在这里添加代码  start-->
            <div class="row">
                <div class="form-group has-feedback {!! !$errors->has('captcha') ?: 'has-error' !!}">
                    @if($errors->has('captcha'))
                        @foreach($errors->get('captcha') as $message)
                            <label class="control-label" for="inputError" style="margin-left: 15px"><i
                                    class="fa fa-times-circle-o">{{$message}}</i></label></br>
                        @endforeach
                    @endif
                    <input type="text" class="form-control" style="display: inline;width: 55%; margin-left: 15px"
                           placeholder="{{ trans('admin.captcha') }}" name="captcha" id="captcha">
                    <img class="captcha" src="{{ captcha_src('default') }}">
                </div>
            </div>
            <!-- 在这里添加代码  end-->
            <div class="row">
                <div class="col-xs-8">
                    @if(config('admin.auth.remember'))
                        <div class="checkbox icheck">
                            {{--                            <label>--}}
                            {{--                                <input type="checkbox" name="remember"--}}
                            {{--                                       value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>--}}
                            {{--                                {{ trans('admin.remember_me') }}--}}
                            {{--                            </label>--}}
                            <label>
                                <a style="color: #666" id="switch-mode" onclick="switchMode()">
                                    <span class="glyphicon glyphicon-phone" aria-hidden="true"></span>
                                    手机号登录
                                </a>

                                <a style="color: #666; display: none" id="switch-mode1" onclick="switchMode1()">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    账号密码登录
                                </a>
                            </label>
                        </div>
                    @endif
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="button" class="btn btn-primary btn-block btn-flat"
                            onclick="checkform_login()">{{ trans('admin.login') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>

<script src="{{ admin_asset("vendor/laravel-adminE/common.js")}}"></script>

<script src="{{ admin_asset("vendor/laravel-adminE/layer-v3.0.3/layer/layer.js")}}"></script>

<script src="{{ admin_asset("vendor/laravel-adminE/notify/notify.js")}}"></script>

<script src="{{ admin_asset("vendor/laravel-adminE/crypto-js.min.js")}}"></script>

<script type="text/javascript">
    $(function () {
        var url = $('img').attr('src');
        $('img').click(function () {
            $(this).attr('src', url + Math.random())
        });
    });

    function checkform_login() {
        var display = $('#switch-mode').css('display');
        if (display != 'none') {
            if ($("#username").val() == "") {
                $("#username").focus();
                alert("请输入您的账号！")
                return false
            } else if ($("#password").val() == "") {
                $("#password").focus();
                alert("请输入您的密码！")
                return false
            }else {
                var password = $("#password").val();
                if(password.length < 20)
                {
                    $("#password").val(js_encrypt($("#password").val()))
                }

            }
        }else{
            if ($("#mobile").val() == "") {
                $("#mobile").focus();
                alert("请输入您的手机号！")
                return false
            } else if ($("#verify_number").val() == "") {
                $("#verify_number").focus();
                alert("请输入手机验证码！")
                return false
            }
        }
        $("#form_submit").submit();
    }

    function js_encrypt() {
        text = $('#password').val();
        var key = CryptoJS.enc.Latin1.parse('1E390CMD585LLS4S');
        var iv = CryptoJS.enc.Latin1.parse('1104432290129056');
        var encrypted = CryptoJS.AES.encrypt(text, key, {
            iv: iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        }).toString();
        return encrypted;
    }
</script>
<script>
    var page_type = "{{ json_encode($errors->get('mobile')) }}";

    $(document).ready(function () {
        switchMode1();

        if (page_type.search("手机号") != -1) {
            switchMode();
        }

        //判断浏览器是否为谷歌浏览器
        isChrome();

        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });

    function switchMode() {
        // 切换手机验证码登录
        $('#username').attr("disabled", true);
        $('#mobile').attr("disabled", false);
        $("#verify-number").show();
        $("#verify-number1").show();
        $("#verify-password").hide();
        $("#verify-password1").hide();
        $("#switch-mode").hide();
        $("#switch-mode1").show();
    }

    function switchMode1() {
        // 切换账号密码登录
        $('#mobile').attr("disabled", true);
        $('#username').attr("disabled", false);
        $("#verify-number").hide();
        $("#verify-number1").hide();
        $("#verify-password").show();
        $("#verify-password1").show();
        $("#switch-mode").show();
        $("#switch-mode1").hide();
    }

    var send_sms = function (mobile) {
        doAjax("/admin/auth/send_sms", "POST", {
            'mobile': mobile
        }, function (data) {
            layer.msg('验证码发送成功！');

            var obj = $("#send-sms");
            settime(obj);
        });
    };

    //注意不要定义在$(function(){})里
    var countdown = 60;

    function sendmsg() {
        var mobile = $("#mobile");
        send_sms(mobile.val());
    }

    function settime(obj) { //发送验证码倒计时
        if (countdown == 0) {
            obj.attr('disabled', false);
            //obj.removeattr("disabled");
            obj.val("发送验证码");
            countdown = 60;
            return;
        } else {
            obj.attr('disabled', true);
            obj.val("重新发送(" + countdown + ")");
            countdown--;
        }
        setTimeout(function () {
                settime(obj)
            }
            , 1000)
    }

    function isChrome() {
        //取得浏览器的userAgent字符串
        var userAgent = navigator.userAgent;
        //判断Chrome浏览器
        var isChrome = userAgent.indexOf("Chrome") > -1
            && userAgent.indexOf("Safari") > -1;

        console.log(isChrome);
        if (!isChrome) {
            layer.alert(
                "<div style='text-align: center'><span style='font-size: 22px; color: rgba(245,139,152,1)'>*</span>" +
                "<span style='font-size: 16px'>本系统仅支持谷歌浏览器</span></div>" + "<br />" +
                "<div style='text-align: center'><span style='font-size: 16px'>请使用</span>" +
                "<a href='https://www.google.cn/chrome/' style='color: rgba(245,139,152,1); font-size: 16px'>谷歌浏览器</a>" +
                "<span style='font-size: 16px'>进入本系统</span>" +
                "</div>"
            )
        }
    }
</script>
</body>
</html>
