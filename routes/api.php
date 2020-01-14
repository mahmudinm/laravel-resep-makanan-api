<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {

    // Fungsi Auth
    $api->group(['prefix' => 'auth'], function(Router $api) {

        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');

    });


    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {

        $api->group(['middleware' => ['role:admin']], function(Router $api) {

            // Crud permissions
            $api->resource('permissions', 'App\\Api\\V1\\Controllers\\PermissionsController');

            // Crud Roles
            $api->get('roles/create', 'App\\Api\\V1\\Controllers\\RolesController@create');
            $api->get('roles/{role}/edit', 'App\\Api\\V1\\Controllers\\RolesController@edit');
            $api->resource('roles', 'App\\Api\\V1\\Controllers\\RolesController');

            // Crud Users
            $api->get('users/create', 'App\\Api\\V1\\Controllers\\UsersController@create');
            $api->get('users/{role}/edit', 'App\\Api\\V1\\Controllers\\UsersController@edit');
            $api->resource('users', 'App\\Api\\V1\\Controllers\\UsersController');

            // Crud Category
            $api->resource('category', 'App\\Api\\V1\\Controllers\\CategoryController');

            // Crud Category
            $api->resource('ingredient', 'App\\Api\\V1\\Controllers\\IngredientController');
        });

        $api->group(['middleware' => ['role:admin|staff']], function(Router $api) {

            // Crud Recipe
            $api->resource('recipe', 'App\\Api\\V1\\Controllers\\RecipeController');

        });

    });

});
