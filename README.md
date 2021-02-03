
这个扩展用来在同一个laravel项目中安装多个后台(项目),并且可以管理查看这些项目.这些项目拥有同一个后台地址，项目之间的后台账号不能重复。

如: 我们的业务是商场的crm和室内位置服务.客户(即:不同的商城人员)使用的是同一套后台,他们登录只能看到本商场的数据及内容.
我们作为项目拥有者,登录可以看到所有商城的数据及内容.

具体参见[Wiki](https://github.com/never615/laravel-admin-enhance/wiki)


```
需要添加下列代码到 ./app/Admin/bootstrap.php 中
\Mallto\Admin\Facades\AdminE::quickAccess();
\Mallto\Admin\Facades\AdminE::adminBootstrap();


```
