# Amazon affiliate products

Plugin to add products of amazon and updates the prices for affiliates with amazon API.
Create custom tables for comparing products.

  - Image
  - Price
  - Title
  - Details

### Version
1.0.0

### Tech

* [Wordpress] - web software you can use to create a beautiful website, blog, or app.
* [PHP] - a popular general-purpose scripting language that is especially suited to web development.
* [DataTables] - table plug-in for jQuery.

### Installation
Upload update-amazon-price folder in your /wp-content/plugins folder in your wordpress installation
Create a **config.php** file in route of plugin with your credentials:


```
 define('AWS_ACCESS_KEY_ID', 'YOUR_KEY_ID');
 define('AWS_SECRET_KEY', 'YOUR_SECRET_KEY');
 define('AMZ_PRODUCTS_TABLE', 'THE_PRODUCTS_TABLE_NAME'); // without wp_ extension
 define('AMZ_TABLES_TABLE', 'THE_TABLES_TABLE_NAME'); // without wp_ extension
 define('AFFILIATE_ID', 'YOUR_AFFILIATE_ID');
```

### License

MIT [LICENSE]

[LICENSE]:https://github.com/ToniChaz/update-amazon-price/blob/master/LICENSE
[Wordpress]:https://www.wordpress.com/
[PHP]:http://php.net/
[DataTables]:https://datatables.net/
