# Page

[![Awesome9](https://img.shields.io/badge/Awesome-9-brightgreen)](https://awesome9.co)
[![Latest Stable Version](https://poser.pugx.org/awesome9/page/v/stable)](https://packagist.org/packages/awesome9/page)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/awesome9/page.svg)](https://packagist.org/packages/awesome9/page)
[![Total Downloads](https://poser.pugx.org/awesome9/page/downloads)](https://packagist.org/packages/awesome9/page)
[![License](https://poser.pugx.org/awesome9/page/license)](https://packagist.org/packages/awesome9/page)

<p align="center">
	<img src="https://img.icons8.com/nolan/256/page.png"/>
</p>

## 📃 About Page

This package provides ease adding pages to WordPress backend.

## 💾 Installation

``` bash
composer require awesome9/admin-page
```

## 🕹 Usage

First, you need to register options for your theme/plugin.

```php
$page = new Awesome9\Admin\Page(
	'awesome9_plugin_options',   // Unique id which is page slug
	esc_html__( 'Awesome Page', 'text-domain' )
	[
		'position'   => 40,
		'parent'     => 'awesome-parent',
		'capability' => 'manage_options',
		'render'     => 'some-view-in-file.php',
		'help'       => [
			'redirections-overview'       => [
				'title' => esc_html__( 'Overview', 'text-domain' ),
				'view'  => 'help-tab-overview.php',
			],
			'redirections-screen-content' => [
				'title' => esc_html__( 'Screen Content', 'text-domain' ),
				'view'  => 'help-tab-screen-content.php',
			],
			'redirections-actions'        => [
				'title' => esc_html__( 'Available Actions', 'text-domain' ),
				'view'  => 'help-tab-actions.php',
			],
			'redirections-bulk'           => [
				'title' => esc_html__( 'Bulk Actions', 'text-domain' ),
				'view'  => 'help-tab-bulk.php',
			],
		],
	]
);
```

## 📖 Changelog

[See the changelog file](./CHANGELOG.md)
