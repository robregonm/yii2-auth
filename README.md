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

Once the extension is installed, simply run migrations:

```bash
$ php yii migrate/up --migrationPath=@auth/migrations
```

And modify your application configuration as follows:

```php
return [
	'modules' => [
	    ...
		'user' => 'auth\Module',
		'layout' => '//homepage', // Layout when not logged in yet
		'layoutLogged' => '//main', // Layout for logged in users
		'superAdmins' = ['admin'], // SuperAdmin users
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

## License

Auth module is released under the BSD-3 License. See the bundled `LICENSE.md` for details.

#INSTALLATION

./yii migrate/up --migrationPath=@auth/migrations