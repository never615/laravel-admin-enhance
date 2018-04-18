## 功能

### 在laravel-admin的基础上扩展了以下功能:
* 多主体和多级主体支持（不同主体下的账号、角色、用户等数据隔离）
* 多级权限设置支持（分配权限的时候，支持分配父级权限）
* 自动权限校验（laravel-admin1.5也也增加了此功能，但是有所区别）
* 自动根据账号拥有的权限生成侧边栏菜单
* 日志记录支持阿里云日志（需要引入easy-tool库）
* 一些扩展组件,包括已有的一些组件的增强



## 安装
基于laravel-admin,所以需要先看[相关文档](http://laravel-admin.org/docs/#/zh/)

引入laravel-admin库需要使用修改过的[laravel-admin](https://github.com/never615/laravel-admin),
具体修改内容参考项目下的Change.md

安装完laravel-admin后，执行下列命令来安装laravel-admin-enhance

//todo 安装库命令

运行下面的命令来发布资源：
```
php artisan vendor:publish --provider="Mallto\Admin\ServiceProvider"
```
然后执行下面命令初始化基本模块的权限和菜单:

```
php artisan admin_enhance:install
```
此命令会生成相应的数据表并填充需要的内容，覆盖`app\Admin\routes.php`文件。 

此外安装命令会追加以下内容在`app/Admin/bootstrap.php`中：

```
\Encore\Admin\Form::extend('qiniuFile', \Mallto\Admin\Form\Field\QiniuFile::class);
\Encore\Admin\Form::extend('buttonE', \Mallto\Admin\Form\Field\Button::class);
\Encore\Admin\Form::extend('filePrivate', \Mallto\Admin\Form\Field\FilePrivate::class);
\Encore\Admin\Form::extend('selectE', \Mallto\Admin\Form\Field\Select::class);
\Encore\Admin\Form::extend('selectOrNew', \Mallto\Admin\Form\Field\SelectOrNew::class);
\Encore\Admin\Form::extend('editor2', \Mallto\Admin\Form\Field\WangEditor::class);


\Encore\Admin\Grid\Column::extend("expand", \Mallto\Admin\Grid\Displayers\ExpandRow::class);
\Encore\Admin\Grid\Column::extend("urlWrapper", \Mallto\Admin\Grid\Displayers\UrlWrapper::class);
\Encore\Admin\Grid\Column::extend("numberFormat", \Mallto\Admin\Grid\Displayers\NumberFomart::class);
\Encore\Admin\Grid\Column::extend("switchE", \Mallto\Admin\Grid\Displayers\SwitchDisplay::class);
\Encore\Admin\Grid\Column::extend("linkE", \Mallto\Admin\Grid\Displayers\Link::class);


\Encore\Admin\Admin::js('/vendor/laravel-adminE/clipboard/clipboard.min.js');
\Encore\Admin\Admin::js('/vendor/laravel-adminE/common.js');
\Encore\Admin\Admin::js('/vendor/laravel-adminE/layer-v3.0.3/layer/layer.js');
\Encore\Admin\Admin::js('/vendor/laravel-adminE/notify/notify.js');

```

注册中间件，在`app\Http\Kernel.php`中的$middlewareGroups添加
```
        'adminE' => [
            'admin.auth',
            'admin.pjax',
            'admin.bootstrap',
            'adminE.auto_permission',
            'admin.log',
            //'adminE.log', //easy-tool库提供，可以记录日志到阿里云日志
        ],
        'adminE_base' => [
            'admin.auth',
            'admin.pjax',
            'admin.bootstrap',
            'admin.log',
            //'adminE.log', //easy-tool库提供，可以记录日志到阿里云日志
        ],
```



然后在项目的`routes\web.php`添加：
````
AdminE::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => 'Admin',
    'middleware'    => ['adminE_base'],
], function (Router $router) {
    //需要覆盖默认的管理端首页，打开注释，编辑对应文件 app\Http\Controllers\Admin\HomeController.php
    //$router->get('/', 'HomeController@index')->name("dashboard");

    Route::group(['middleware' => ['adminE.auto_permission']], function ($router) { 
        //需要经过权限校验的路由
    });
});
````

## 使用说明
### 异常
本库抛出的异常均继承自`Symfony\Component\HttpKernel\Exception\HttpException`,包含响应码和错误信息

### 翻译
自己添加的项目翻译放在resoureces/lang中的admin2.php里面.
其中表名作为数组key的翻译,使用表名的复数形式(如果表名本身就是复数则直接使用表名),如:
```
    "coupons" => [
        "limit"          => "每人限领",
        'limit_day'      => "限领时间间隔",
        'member_level'   => "可领取的会员等级",
        'verify_subject' => "可核销主体",
        'verify_shop'    => "可核销店铺",
    ],
```

### 报表导出
在admin配置文件中,设置默认的导出处理者为`Mallto\Admin\Grid\Exporters\CsvExporter::class,`,
相比原库,支持关联数据导出,支持自动翻译字段.

如果需要更进一步的自定义数据,继承`Mallto\Admin\Grid\Exporters\CsvExporter::class,`复写`customData()`方法即可.

$records即为表格页面一样的数据源通过array_dot方法转换成了一维数组.
如果想忽略某属性转换成一维数组(该属性在数据库是json格式,通过orm查询变成了数组格式),可以复写:
```
    protected $ignore2Array = [
        "member_level",
        "verify_subject",
        "verify_shop",
    ];
```


forget方法可以传入关联数据的模型名来忽略全部,如传入member会忽略member所有的关联数据.
transform方法除了做已有数据转换外,还可以通过此方法添加新的字段,如示例中增加xxx字段.



更多使用及解释可以参考源码注释.

示例:

为了避免在执行transform的时候,数据已经被forget,所以建议先执行transform()方法,然后在执行forget(),
````
public function customData($records)
    {
        $records = $this->transform($records, [
            "member.sex",
            "member.member_level_id",
            "xxx",
        ], function ($record, $key) {
            switch ($key) {
                case "member.sex":
                    $record[$key] = isset($record[$key]) ? SelectConstants::GENGDER[$record[$key]] : "";
                    break;
                case  "member.member_level_id":
                    $record[$key] = isset($record[$key]) ? (MemberLevel::find($record[$key]))->name : "";
                    break;
                case  "xxx":
                    $record[$key] = 'yyy';
                    break;  
                 return $record;
            }
        });

        $records = $this->forget($records, [
            "avatar",
            "username",
            "email",
            "free_parking_time",
            "free_parking_time_weekly",
            "preferential_parking_times",
            "free_parking_money",
            "free_parking_money_weekly",
            "preferential_parking_times_weekly",
            "subject_id",
            "admin_user_id",
            "user_role",
            "top_subject_id",
            "updated_at",
            "member.id",
            "member.subject_id",
            "member.mobile",
            "member.consume_point",
            "member.period_point",
            "member.user_id",
            "member.vip_card_status",
            "member.deleted_at",
        ]);


        return $records;
    }
````

### 新增扩展说明

#### Form

* qiniuFile：支持直传文件到七牛,适合大文件上传,视频个是文件使用示例:
```
        $form->qiniuFile("url", "视频")
            ->options([
                'initialPreviewConfig'   => [
                    ['key' => 0, 'filetype' => 'video/mp4'],
                ],
                'initialPreviewFileType' => 'video',
                'allowedFileTypes'       => ['video'],
//                'dropZoneEnabled'         => false,
                'uploadLabel'             => '上传',
                'dropZoneTitle'          => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'              => 'https://up-z2.qbox.me/',
                'uploadExtraData'        => [
                    'token' => $this->getUploadTokenInter('upload/video/'.$this->currentId),
                ],
                'allowedFileExtensions'  => ['mp4'],
                'maxFileCount'           => 1, //同时上传的文件数量
            ])
            ->help("视频只支持mp4格式文件,添加视频后需点击上传按钮上传,只能上传一个");

```
* buttonE：修复laravel-admin，button的bug
* selectE/multipleSelectE: 增加ajaxLoad方法，和load方法类似，不过支持ajax动态分页加载数据
* editor2：集成wangEditor编辑器，开箱可用，支持七牛

#### Grid
* expand：支持点击下拉按钮，展示更多数据条模具
* urlWrapper：支持一键复制按钮和二维码预览
* numberFormat： 内部调用了number_format方法
* switchE：请求失败时的错误提示处理
* linkE： 支持回调方法，回调中可以获取当前条目数据，一般获取一些id来拼接url.使用示例:
```
->linkE(function () {
            return '/admin/study_banks/'.$this->row->study_bank_id;
        })
```


### 其他
#### AdminCommonController
管理端的实现类继承自`AdminCommonController`,提供了一些共有方法和实现了一些共有逻辑.
* 自动设置创建主体,使用:`$this->autoSubjectSet($form);`,需要关联的model有subject_id.
* 自动设置创建者,使用:`$this->autoAdminUser($form);`,需要关联的model有admin_user_id.


#### 在common.js中
封装过的ajax请求，内部异常统一处理,示例：
```
 doAjax("{{$url}}", "POST", {
                    _token: LA.token,
                    ids: selectedRows(),
                    tag_id: tagId
                }, function (data) {
                    $.pjax.reload('#pjax-container');
                    layer.msg('设置成功', {icon: 1});
//                    toastr.success("设置成功");
                });
```
X-editable初始化：
```
    $.fn.editable.defaults.error = function (response, newValue) {
        if (response.responseJSON && response.responseJSON.error) {
            return response.responseJSON.error;
        } else {
            return response.statusText + ":" + response.status
        }
    };
    $.fn.editable.defaults.emptytext="空";
```




## 功能详细说明（todo 更新）

### 权限
权限支持等级关系.
![](https://file.mall.mall-to.com/2017-09-04_59ad26a21bdf7.png)

一般的管理端模块权限分为以下三类,基于上图的基础:
* create/store/update :创建和修改权限
* destroy:删除权限
* index/edit/show:查看权限
通常情况下,一个模块只需要以上权限就够了.
如果有额外的接口,则需要创建额外的权限.


#### 权限的创建
权限的创建常用的有两种:
1. 直接使用管理端权限管理创建
2. 跟随业务库,在seeder中编写对应的权限.便于安装该库时自动生成相应的权限.
如:
!!! 为了防止seeder重复运行创建/插入重复数据,需要在创建之前判断是否已经存在.
```
 $parentId = $this->createPermissions("问题", "qa_questions", true, $parentId);
 Permission::create([
      'parent_id' => $parentId,
      'order'     => $this->order += 1,
     "name"      => "问题发布",
     "slug"      => "qa_questions.publish",
]);
```
说明:
`Mallto\Admin\Seedern\SeederMaker` trait中包含创建权限的基础方法,即上面调用的`$parentId = $this->createPermissions("问题", "qa_questions", true, $parentId);`则会创建:问题管理和相关的子权限(问题管理查看;问题管理创建/修改;问题管理删除)

`trait SeederMaker
{
    protected $routeNames = [
        "index"   => "查看",  //列表页/详情页/show
        "create"  => "创建/修改", //创建页/保存/修改
        "destroy" => "删除", //删除权限
    ];

    /**
     * @param      $name        ,权限名
     * @param      $slug        ,权限标识
     * @param bool $sub         ,是否生成子权限
     * @param int  $parentId    ,父权限id
     * @param bool $closeDelete ,是否关闭创建子权限之`删除`权限
     * @param bool $common      ,是否是所有主体都默认有的公共权限
     * @param bool $closeCreate ,是否关闭创建子权限之`创建/修改`权限
     * @return int
     */
    public function createPermissions(
        $name,
        $slug,
        $sub = true,
        $parentId = 0,
        $closeDelete = false,
        $common = false,
        $closeCreate = false
    ) {
      ...
    }
}
`


#### 自动校验权限
* 通过自动权限校验中间件(`AutoPermissionMiddleware`)来完成
* 项目拥有者(角色标识`owner`)拥有全部权限.
* 理论上管理端的所有接口都要创建对应的权限配置,否则该接口就没有人能访问.
* 因为全新有父子等级关系,所以拥有父权限即可通过所拥有的子权限的校验.


### 多级主体支持
#### 多主体需求
因为相同的业务有不同的使用方,支持多主体的话,便于管理和加快开发速度(当有一个新的甲方需要相关业务时,只需要简单配置即可使用).
* 每个主体数据独立:包括管理端账户/角色和主体配置;延伸到用户端,每个主体的用户也都是独立的;通过主体id`subject_id`来标识.

#### 多级主体需求
如:海山世界和花园城作为独立运营方拥有自己独立的面向用户的主体.但是他们都属于招商地产.所以需要他们的父级主体招商地产可以查看他们的所有数据.于是主体规划了多级的需求.
这种情况下:不同主体的所有数据都是独立的:除了上一点说的那些数据,还包括所有的业务数据的独立,比如:店铺/活动/积分商城等.
并且所有的主体都属于`项目拥有者`主体,该主体的账号会对其他主体进行初始化创建和配置.


#### 管理端数据查看
根据管理端账户所属的主体,可以查看该主体和所有子主体的数据.
此外,还可以单独设置某个账户的数据查看范围:如海上世界主体的账号,可以给他设置查看范围到招商地产.
