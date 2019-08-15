<?php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    'guards' => [
        'api' => [
            'driver' => 'jwt',                           #### 更改为JWT驱动
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => \App\Models\User::class,        #### 指定用于token验证的模型类
        ],
    ],
    'passwords' => [                                #### Lumen默认无session，所以该字段无意义
        //
    ],
];