<?php
$baseURL = \env('APP_URL');

$emailTemp = [
    'validation_code' => []
];

$emailTemp['validation_code']['cn'] = <<<EOT
# 你好,
@panel
请使用下面提供的验证码去验证你的邮箱地址:
**{{code}}**
    
如果你意外收到了这个验证码，你可以忽略这条邮件，也许有其他人不小心写了你的邮箱地址。
@endpanel
@btn({$baseURL})
进入OneMoreMeal
@endbtn
感谢,
OneMoreMeal团队
EOT;

$emailTemp['validation_code']['en'] = <<<EOT
# Hello,
@panel
Please use the following verification code to verify your email:
**{{code}}**

If you didn't request this code, you can safely ignore this email. Someone else might have typed your email address by mistake.
@endpanel
@btn({$baseURL})
Enter OneMoreMeal
@endbtn
Thanks,
OneMoreMeal Team
EOT;

return [
        'email_config' => [
        'driver' => \env('EMAIL_CONFIG_DRIVER'),
        'host' => \env('EMAIL_CONFIG_HOST'),
        'port' => \env('EMAIL_CONFIG_PORT'),
        'username' => \env('EMAIL_CONFIG_USERNAME'),
        'from_address' => \env('EMAIL_CONFIG_FROM_ADDRESS'),
        'from_name' => \env('EMAIL_CONFIG_FROM_NAME'),
        'primary_color' => \env('EMAIL_CONFIG_PRIMARY_COLOR'),
        'btn_color' => \env('EMAIL_CONFIG_BTN_COLOR'),
        'h_color' => \env('EMAIL_CONFIG_H_COLOR'),
        'password' => \env('EMAIL_CONFIG_PASSWORD')
    ],
    'email_templates' => [
        'VALIDATION_CODE_EMAIL_TEMPLATE_ID' => [
            'name' => 'send_validation_code',
            'title_template' => [
                'en' => 'Your validation code | {{site_name}}',
                'cn' => '你的激活码 | {{site_name}}'
            ],
            'body_template' => [
                'en' => $emailTemp['validation_code']['en'],
                'cn' => $emailTemp['validation_code']['cn']
            ],
            'default_lang' => 'en'
        ]
    ],
    'user_types' => [
        'sponsor' => 0,
        'user' => 1,
        'authority' => 2
    ]
];
