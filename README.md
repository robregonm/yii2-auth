Auth Module
===========

Auth Module is a flexible user registration, authentication & RBAC module for Yii2. It provides user authentication, registration and RBAC support to your Yii2 site.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require robregonm/yii2-auth "*"
```

or add

```
"robregonm/yii2-auth": "*"
```

to the require section of your `composer.json` file.

## Usage

Once the extension is installed, modify your application configuration to include:

File config/web.php

```php
return [
	'modules' => [
	    ...
	        'auth' => [
	            'class' => 'auth\Module',
	            'layout' => '//homepage', // Layout when not logged in yet
	            'layoutLogged' => '//main', // Layout for logged in users
	            'attemptsBeforeCaptcha' => 3, // Optional
	            'supportEmail' => 'support@mydomain.com', // Email for notifications
	            'passwordResetTokenExpire' => 3600, // Seconds for token expiration
	            'superAdmins' => ['admin'], // SuperAdmin users
	            'signupWithEmailOnly' => false, // false = signup with username + email, true = only email signup
	            'tableMap' => [ // Optional, but if defined, all must be declared
	                'User' => 'user',
	                'UserStatus' => 'user_status',
	                'ProfileFieldValue' => 'profile_field_value',
	                'ProfileField' => 'profile_field',
	                'ProfileFieldType' => 'profile_field_type',
	            ],
	        ],
	    ...
	],
	...
	'components' => [
	    ...
		'authManager' => [
			'class' => '\yii\rbac\DbManager',
			'ruleTable' => 'AuthRule', // Optional
			'itemTable' => 'AuthItem',  // Optional
			'itemChildTable' => 'AuthItemChild',  // Optional
			'assignmentTable' => 'AuthAssignment',  // Optional
		],
		'user' => [
			'class' => 'auth\components\User',
			'identityClass' => 'auth\models\User', // or replace to your custom identityClass
			'enableAutoLogin' => true,
		],
	    ...
	]
];
```

File config/console.php
```php
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'user' => [
            'class' => 'auth\components\User',
        ],
        'db' => $db,
    ],

    'modules' => [
        'auth' => [
                'class' => 'auth\Module',
                'superAdmins' => ['admin'], // SuperAdmin users
        ],
    ],
```


And run migrations:

```bash
$ php yii migrate/up --migrationPath=@auth/migrations
```

## License

Auth module is released under the BSD-3 License. See the bundled `LICENSE.md` for details.

#INSTALLATION

./yii migrate/up --migrationPath=@auth/migrations

## URLs

* Login: `yourhost/auth/default/login`
* Logout: `yourhost/auth/default/logout`
* Sign-up: `yourhost/auth/default/signup`
* Reset Password: `yourhost/auth/default/reset-password`
* User management: `yourhost/auth/user/index`
* User profile: `yourhost/auth/profile/view`

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=robregonm&url=https://github.com/robregonm/yii2-auth&title=Yii2-PDF&language=&tags=github&category=software) 
