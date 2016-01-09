<?php namespace Controllers;

use Application\Registry;
use Application\Route;

/**
 * All routes are registered here
 */
Registry::getInstance()->router->addRoute(Route::get('Controllers\Home', 'home', '/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\TagSuggest', 'suggest', 'tagsuggest/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Image', 'image', 'image/([^/]+)/'));
Registry::getInstance()->router->addRoute(Route::post('Controllers\Upload', 'upload', 'upload/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Browse', 'album', 'album/([^/]+)/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Browse', 'search', 'search/'));
Registry::getInstance()->router->addRoute(Route::post('Controllers\Browse', 'performSearch', 'search/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Browse', 'tags', 'tags/([^/]+)/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Browse', 'tags', 'tags/([^/]+)/([0-9]+)/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Tags', 'tags', 'tags/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Tags', 'allTags', 'alltags/'));
Registry::getInstance()->router->addRoute(Route::post('Controllers\Login', 'login', 'login/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Login', 'logout', 'logout/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Image', 'download', 'download/([^/]+)/[^/]+/'));
Registry::getInstance()->router->addRoute(Route::get('Controllers\Image', 'delete', 'delete/([^/]+)/'));

?>