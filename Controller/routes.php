<?php namespace Controller;

use Application\Route;
use Application\Router;

/**
 * All routes are registered here
 */
Router::getInstance()->addRoute(Route::get('Controller\Home', 'home', '/'));
Router::getInstance()->addRoute(Route::get('Controller\TagSuggest', 'suggest', 'tagsuggest/'));
Router::getInstance()->addRoute(Route::get('Controller\Image', 'image', 'image/([^/]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Image', 'download', 'download/([^/]+)/[^/]+/'));
Router::getInstance()->addRoute(Route::get('Controller\Image', 'delete', 'delete/([^/]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'tags', 'tags/([^/]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'tags', 'tags/([^/]+)/([0-9]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'user', 'user/([^/]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'user', 'user/([^/]+)/([0-9]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'album', 'album/([^/]+)/'));
Router::getInstance()->addRoute(Route::get('Controller\Browse', 'search', 'search/'));
Router::getInstance()->addRoute(Route::post('Controller\Browse', 'performSearch', 'search/'));
Router::getInstance()->addRoute(Route::post('Controller\Upload', 'upload', 'upload/'));
Router::getInstance()->addRoute(Route::get('Controller\Tags', 'tags', 'tags/'));
Router::getInstance()->addRoute(Route::get('Controller\Tags', 'allTags', 'alltags/'));
Router::getInstance()->addRoute(Route::post('Controller\Login', 'login', 'login/'));
Router::getInstance()->addRoute(Route::get('Controller\Login', 'logout', 'logout/'));
Router::getInstance()->addRoute(Route::get('Controller\Partial', 'navbar', 'partial/navbar/'));
