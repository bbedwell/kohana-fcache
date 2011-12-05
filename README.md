Kohana Fcache
===

This module is an add-on for Kohana v3.2.0 that allows you to cache Facebook API queries and to invalidate on a specified table per table basis. Cache's by unique ID

Place into modules/mcache directory

Place the following code in bootstrap.php under Kohana::modules

	'fcache' => MODPATH.'fcace'	// Memcache API call caching for Facebook

Usage
---

### Setup

Wherever you instantiate Facebook change it to Fcache_Facebook

	$facebook = new Fcache_Facebook(
		...
	);

### Calling the API

This is an example of how to call the api using Kohana_Fcache's Query method

	$query = $facebook->query("/me")->execute();

If no table is specified, the default table is 'user'

To specify a table (or tables):

	$query = $facebook->query("/me")->set_table('user')->execute();

To invalidate all specified tables (on an insert, etc):

	$query = $facebook->query("/me")->set_table('user')->invalidate()->execute();

To force execute (and cache result)

	$query = $facebook->query("/me")->set_table('user')->execute(FALSE);
	
To set lifetime (in seconds)

	$query = $facebook->query("/me")->set_table('user')->set_lifetime(3600)->execute();