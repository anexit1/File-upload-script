<?php
//
// ----------------------------------------------------------------------------
// --- UPLOADER SCRIPT
// ----------------------------------------------------------------------------
//
// Uploader script generates download links for the uploaded files. 
// It allows to set download expirations by date or by number of downloads.
//

// define directory separator
define('DS', DIRECTORY_SEPARATOR);

// ----------------------------------------------------------------------------
// --- REQUIRE FILES
// ----------------------------------------------------------------------------

// require composer autoloader
require 'app' . DS .'vendor' . DS . 'autoload.php';

// if settings file does not exist load install settings instead
if (file_exists('app' . DS . 'config' . DS . 'settings.php')) {
    require 'app' . DS . 'config' . DS . 'settings.php';
} else {
    require 'app' . DS . 'config' . DS . 'settings.install.php';
}

// require the token generator class
require 'app' . DS . 'src' . DS . 'token_generator.class.php';

// ----------------------------------------------------------------------------
// --- BOOTSTRAP APP
// ----------------------------------------------------------------------------

// create Slim app
$app = new Slim\Slim(array(
    'debug' => true,
    'templates.path' => 'app' . DS . 'templates',
    'files.path' => 'app' . DS . 'files'
));

/*
 * configure ORM
 */
ORM::configure(array(
    'connection_string' => 'mysql:host=' . $settings['db_hostname'] . ';dbname=' . $settings['db_name'],
    'username' => $settings['db_username'],
    'password' => $settings['db_password'],
    'return_result_sets' => true
));

// ----------------------------------------------------------------------------
// --- SLIM HOOKS
// ----------------------------------------------------------------------------

// add notFound callable to render the 404 page
$app->notFound(function() use ($app) {
    $app->render('404.php');
});

/**
 * add some default variables that are used in all templates
 */
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array(
        'base_url' => $app->urlFor('home'),
        'site_url' => $app->request()->getUrl()
    ));

    if(!file_exists('app' . DS . 'config' . DS .'settings.php')) {
        $installUrl = $app->urlFor('install');
        if($app->request()->getPath() != $installUrl) {
            $app->redirect($installUrl);
        }
    }
});

// ----------------------------------------------------------------------------
// --- HELPER FUNCTIONS
// ----------------------------------------------------------------------------

/**
 * middleware to check if user is admin, if it's not redirect to homepage.
 */
$isAdmin = function() use ($app) {
	session_cache_limiter(false);
	session_start();
	if(!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		$app->redirect($app->urlFor('home'));
	}
};

/**
 * validate application settings data
 * @param array $data
 */
$validateData = function($data) {
    $errors = array();
    if (empty($data['username'])) {
        $errors['username'] = 'You must enter a valid username';
    }
    return $errors;
};

// ----------------------------------------------------------------------------
// --- REQUIERE ROUTES
// ----------------------------------------------------------------------------
 
require 'app' . DS . 'src' . DS . 'installer.php';
require 'app' . DS . 'src' . DS . 'front.php';
require 'app' . DS . 'src' . DS . 'admin.php';

// ----------------------------------------------------------------------------
// --- RUN APPLICATION
// ----------------------------------------------------------------------------

$app->run();
