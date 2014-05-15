Auth Module
===========

Auth Module is a flexible user registration, authentication & RBAC module for Yii2. It provides user authentication, registration and RBAC support to your Yii2 site.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require robregonm/yii2-auth "dev-master"
```

or add

```
"robregonm/yii2-auth": "dev-master"
```

to the require section of your `composer.json` file.

## Usage

Once the extension is installed, modify your application configuration to include:

```php
return [
	'modules' => [
	    ...
	        'auth' => [
	            'class' => 'auth\Module',
	            'layout' => '//homepage', // Layout when not logged in yet
	            'layoutLogged' => '//main', // Layout for logged in users
	            'attemptsBeforeCaptcha' => 3, // Optional
	            'superAdmins' => ['admin'], // SuperAdmin users
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
	    'user' => [
	        'class' => 'auth\components\User',
	    ],
	    ...
	]
];
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
