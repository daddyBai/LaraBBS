本来打算用 `mbp` 来写一遍课程一的。

不过我已经不算是初学者了，于是快速的过了一次课程二

当然这里的快速并不是指复制粘贴，本课我没有任何复制粘贴的部分

整个课程中我遇到了一些疑问，已经以问题的方式发到了问答区。

[发帖后重定向用 with ('message','发帖成功！') 无法存储到 session](<https://learnku.com/laravel/t/44334>)

当然也有在快速过课程中的一些小问题

比如：我使用的 `laravel v7.10.3` 

不支持 [summerblue/laravel-active](https://learnku.com/courses/laravel-intermediate-training/6.x/category-topics/5564) ，于是我采用了 [hieu-le/active](https://www.jianshu.com/p/2374f9e31023)

在后台管理方面也因为版本不支持而无法安装 [summerblue/administrator](https://learnku.com/courses/laravel-intermediate-training/6.x/admin-dashboard/5588) 而选择了 [laravel-admin](https://laravel-admin.org/) 来做后台管理。

在这一节中我体会到了 laravel 的精髓在于容器注入，写起来很优雅

# 下是我个人的学习总结（非指导）：
- 在 Model 层注入观察者 **Obsever**
    1. `created`,`creating`,`updating`,`updated`,`saving`,`saved`,`deleting`,`deleted`] 以及不常用的 [`restoring`,`restored`]
    2. 在进行数据层交互的时候，观察者模式会给予我很多帮助，例如格式转换，验证，和安全校验
- 在 Model 层使用 `use trait` 来注入功能以减轻 Model 的负担和耦合。
    1. 本章里我在 User 模型里使用 `use trait` 来增加活跃会员功能和最后活跃时间功能
- 在 Model 层使用继承的方式来增加功能
    1. 在 User 模型里通过继承获得了 **邮箱验证** 和 **权限认证**
    2. 疑问： 继承和 trait 都可以解耦功能，这两样有区别吗？
- 在 Model 层关联多种数据关系
    1. 多种 Model 关系 [`hasOne`,`hasMany`,`belongsTo`,`belongsToMany`]
    2. 在获取数据时可以用 `with('关系')` 关键字来做 left join 查询解决 N+1 的问题
    3. 使用 migration 增加数据表关系来解决数据一致性问题 `$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');`
- 在 Model 层使用 migration 和 DBSeeder 
- 在 controller 层里使用中间键 middleware
    1. laravel 框架默认使用了以下中间键
        - （ 全局 ）
        - 修正代理服务器后的服务器参数 `TrustProxies`
        - 检测应用是否进入维护模式 `CheckForMaintenanceMode`
        - 对 Request 请求的处理 `HandleCors`
        - 检测是否请求数据过大 `ValidatePostSize`
        - 对请求参数进行 trim 处理 `TrimStrings`
        - （ web 中间键 ）
        - 将提交请求参数中空字符串转为 null `ConvertEmptyStringsToNull`
        - Cookie 加密 `EncryptCookies`
        - 将 Cookie 加入 Response `AddQueuedCookieToResponse`
        - 开启 session 会话 `StartSession`
        - 将系统错误数据注入到视图变量 `ShareErrorsFromSession`
        - CSRF 保护 `VerifCsrfToken` ( [文档](https://learnku.com/docs/laravel/5.7/csrf) )
        - 处理路由绑定 `SubstituteBindings` ( [文档](https://learnku.com/docs/laravel/5.7/routing#route-model-binding) )
        - （ API 中间键 ）
        - 调度频率管理 `throttle:60,1`
    2. 本节课增加的中间键
        - 强制邮箱验证 `EnsureEmailIsVerified`
        - 记录用户最后活跃时间 `RecordLastActivedTime`
        - can 用户授权功能 `\Illuminate\Auth\Middleware\Authorize`
    3. 未来可以用中间键实习的功能
        - IP 管理
        - 关键字处理
- 在 controller 层里使用 Requests 数据过滤来实现 Validate 和消息提示
    1. 在 `rules` 方法里做数据格式要求，可以通过 `switch($this->mothod)` 来做区别
    2. 在 `message` 方法里做数据验证失败提示 `'title.min' => '标题必须至少两个字符',`
- 在 controller 层里使用 Policy 授权策略来做用户授权
    1. 将用户授权解耦出来，基本上都是一句 `return currentUser->id === $user->id;`
- 在 controller 层里使用 Jobs 配合 Horizon 来实现消息队列
    1. Jobs 需要数据库支持以存储任务日志，表名 `failed_jobs`
    2. Jobs 支持 数据库，Beanstalkd，Amazon SQS，Redis，和一个同步（本地使用）的驱动，还有一个名为 null 的驱动表明不使用队列任务。都需要在 `.env` 文件内配置
    3. Jobs 要避免使用 Eloquent 模型接口调用，否则会陷入死循环。
    4. 通过 dispatch 分发任务到队列里
    5. 队列系统需要单独启动 `php artisan queue:listen` 或者 `php artisan horizon` 并常驻系统
- 在 controller 层里使用 Listeners 事件监听
    1. 事件系统需要在 `EventServiceProvider` 里的 `$listen` 数组内注册
    2. 也可以在 `EventServiceProvider` 类的 `boot` 方法中注册闭包事件
        ```
        parent::boot();
        Event::listen('event.name', function ($foo, $bar) {
                //
            });
        ```
    3. 可以直接使用 `php artisan event:generate` 来生成 **监听器**
    4. 通过 `event(new FooEvent($foo))` 来使用事件系统
- 在 controller 层里使用 Notifications 消息通知
    1. 多种通知方式在 `via` 方法内定义
- 使用 laravel 自带的命令行管理器来做定时任务
    1. 需要在 crontab 里添加定时执行 Console/Kernel.php 的命令
- 好轮子 [代码生成器](https://learnku.com/courses/laravel-intermediate-training/6.x/code-generator/5559)

* 总结的比较凌乱，多多包涵 *
# 如有错误，望指正！

作业截图：

![实战进阶 项目完结总结及报告](https://cdn.learnku.com/uploads/images/202005/11/23163/JFmCviJmwW.png!large)

**bug** : `.env` 配置文件内 `APP_ENV` 设置为 `production` 后。 在 `AppServiceProvider.php` 内注册的
```
        // 本地开发加载
        if(app()->isLocal()){
            $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        }
```
依然生效
