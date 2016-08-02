# Laravel 5 CDN Middleware
A Basic middleware integration for pull zone CDN's for laravel




# Composer Installation
Add/integrate the following to your project's composer.json file. 

```javascript
"repositories": [ 
	{
		"type": "git",
		"url": "git@github.com:Genentech/laravel5-cdn-views.git"
	}
],
"require": {
	"Genentech/laravel5-cdn-views": "dev-master"
}
```

# Configuration
After it's installed run
```bash
php artisan vendor:publish
```
And then configure the file at /config/laravel5-cdn-views.php

# Usage
add the following to your providers list
```php
Genentech\CdnViews\CdnViewServiceProvider
```
and add the following to app/Http/Kernel.php
```php
 Genentech\CdnViews\Middleware\UseCDN
```

root relative urls will be transformed, and //:resource urls will not 
not root relative urls will be left intact and logged

You can also invoke the CDN helper yourself and convert urls using
```php
$pullzone = 'https://cdn.com'; // or Config::get('laravel5-cdn-views.cdn_url');
$validTags = ['style', 'scripts']; // or Config::get('laravel5-cdn-views.tags');
$cdn_helper =  new CdnHelper(CDN_URL, $this->validTags);
$cdn_url = $cdn_helper->convertUrl('/foo.txt');
```

