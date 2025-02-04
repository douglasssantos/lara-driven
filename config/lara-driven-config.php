<?php

return [

    /*
     * Only change any settings if you are aware of what you are doing.
     */


    /*
     * Framework Load Settings
     */

    "basePath" => "",

    // this variable must be filled in with the path where the container file will be created and read.
    "containerFilePath"                => "containers.yaml",

    // Enable/disable configuration loading.
    "load-configs"                     => true,

    // Enable/disable the loading of translations.
    "load-translations"                => true,

    // Enable/disable command loading.
    "load-commands"                    => true,

    // Enable/disable the loading of routes.
    "load-routes"                      => true,

    // Enable/disable the loading of migrations.
    "load-migrations"                  => true,


    /*
     * Configurations for creating the structure
     */


    // enable creation of the model file and directory?
    "create-model"                     => true,

    // enable creation of the migration file and directory?
    "create-migration"                 => true,

    // enable creation of the seed file and directory?
    "create-seed"                      => true,

    // enable creation of the factory file and directory?
    "create-factory"                   => true,

    // enable creation of the policy file and directory?
    "create-policy"                    => true,

    // enable creation of the service file and directory?
    "create-service"                   => true,

    // enable creation of the service interface file and directory?
    "create-service-interface"         => false,

    // enable creation of the repository file and directory?
    "create-repository"                => true,

    // enable creation of the config file and directory?
    "create-repository-interface"      => true,

    // enable creation of the controller file and directory?
    "create-controller"                => true,

    // enable creation of the request file and directory?
    "create-request"                   => true,

    // enable creation of the middleware file and directory?
    "create-middleware"                => true,

    // enable creation of the route file and directory?
    "create-route"                     => true,

    // enable creation of the command file and directory?
    "create-command"                   => true,

    // enable creation of the config file and directory?
    "create-config"                    => true,

];