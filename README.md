# Laravel 5 CDN Views
A Basic integration for pull zone CDN's for laravel, it works with both blade and phtml files.




# Composer Installation
Add/integrate the following to your project's composer.json file. You'll have to have your SSH key attached to your github account:

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
and enable blade_views to enable cdn helper on all blade views or add 
```php
 Genentech\CdnViews\Middleware\UseCDN to your request Kernel. 
```

root relative urls will be transformed,
//:resource urls will not 
not root relative urls will be left intact and logged

You can also invoke the CDN helper yourself and convert urls using
```php
$cdn_helper =  new CdnHelper($request, CDN_URL, $this->validTags, SSL_Enabled);
$cdn_url = $cdn_helper->convertUrl('/foo.txt');
```

