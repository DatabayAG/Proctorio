<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite1ae8bcb242e25c6de07f94f0328d793
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '0d59ee240a4cd96ddbb4ff164fccea4d' => __DIR__ . '/..' . '/symfony/polyfill-php73/bootstrap.php',
        '7b11c4dc42b3b3023073cb14e519683c' => __DIR__ . '/..' . '/ralouphie/getallheaders/src/getallheaders.php',
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Php73\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Contracts\\Service\\' => 26,
            'Symfony\\Component\\Console\\' => 26,
            'SebastianFeldmann\\Git\\' => 22,
            'SebastianFeldmann\\Cli\\' => 22,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Container\\' => 14,
        ),
        'I' => 
        array (
            'ILIAS\\Plugin\\Proctorio\\' => 23,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
        'C' => 
        array (
            'CaptainHook\\Plugin\\Composer\\' => 28,
            'CaptainHook\\App\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Php73\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php73',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Contracts\\Service\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/service-contracts',
        ),
        'Symfony\\Component\\Console\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/console',
        ),
        'SebastianFeldmann\\Git\\' => 
        array (
            0 => __DIR__ . '/..' . '/sebastianfeldmann/git/src',
        ),
        'SebastianFeldmann\\Cli\\' => 
        array (
            0 => __DIR__ . '/..' . '/sebastianfeldmann/cli/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'ILIAS\\Plugin\\Proctorio\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'CaptainHook\\Plugin\\Composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/captainhook/plugin-composer/src',
        ),
        'CaptainHook\\App\\' => 
        array (
            0 => __DIR__ . '/..' . '/captainhook/captainhook/src',
        ),
    );

    public static $classMap = array (
        'ILIAS\\Plugin\\Proctorio\\Administration\\Controller\\Base' => __DIR__ . '/../..' . '/classes/Administration/Controller/Base.php',
        'ILIAS\\Plugin\\Proctorio\\Administration\\GeneralSettings\\Settings' => __DIR__ . '/../..' . '/classes/Administration/GeneralSettings/Settings.php',
        'ILIAS\\Plugin\\Proctorio\\Administration\\GeneralSettings\\UI\\Form' => __DIR__ . '/../..' . '/classes/Administration/GeneralSettings/UI/Form.php',
        'ILIAS\\Plugin\\Proctorio\\Exception' => __DIR__ . '/../..' . '/classes/Exception.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\Base' => __DIR__ . '/../..' . '/classes/Frontend/Controller/Base.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\Error' => __DIR__ . '/../..' . '/classes/Frontend/Controller/Error.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\ExamLaunch' => __DIR__ . '/../..' . '/classes/Frontend/Controller/ExamLaunch.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\ExamSettings' => __DIR__ . '/../..' . '/classes/Frontend/Controller/ExamSettings.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\RepositoryObject' => __DIR__ . '/../..' . '/classes/Frontend/Controller/RepositoryObject.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\Dispatcher' => __DIR__ . '/../..' . '/classes/Frontend/Dispatcher.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\HttpContext' => __DIR__ . '/../..' . '/classes/Frontend/HttpContext.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\ViewModifier' => __DIR__ . '/../..' . '/classes/Frontend/ViewModifier.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\ViewModifier\\Base' => __DIR__ . '/../..' . '/classes/Frontend/ViewModifier/Base.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\ViewModifier\\ExamLaunch' => __DIR__ . '/../..' . '/classes/Frontend/ViewModifier/ExamLaunch.php',
        'ILIAS\\Plugin\\Proctorio\\Frontend\\ViewModifier\\ExamSettings' => __DIR__ . '/../..' . '/classes/Frontend/ViewModifier/ExamSettings.php',
        'ILIAS\\Plugin\\Proctorio\\Refinery\\Transformation\\UriToString' => __DIR__ . '/../..' . '/classes/Refinery/Transformation/UriToString.php',
        'ILIAS\\Plugin\\Proctorio\\UI\\Form\\Bindable' => __DIR__ . '/../..' . '/classes/UI/Form/Bindable.php',
        'ILIAS\\Plugin\\Proctorio\\Webservice\\Exception' => __DIR__ . '/../..' . '/classes/Webservice/Exception.php',
        'ILIAS\\Plugin\\Proctorio\\Webservice\\Rest' => __DIR__ . '/../..' . '/classes/Webservice/Rest.php',
        'ILIAS\\Plugin\\Proctorio\\Webservice\\Rest\\Impl' => __DIR__ . '/../..' . '/classes/Webservice/Rest/Impl.php',
        'JsonException' => __DIR__ . '/..' . '/symfony/polyfill-php73/Resources/stubs/JsonException.php',
        'ilProctorioConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilProctorioConfigGUI.php',
        'ilProctorioPlugin' => __DIR__ . '/../..' . '/classes/class.ilProctorioPlugin.php',
        'ilProctorioUIHookGUI' => __DIR__ . '/../..' . '/classes/class.ilProctorioUIHookGUI.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite1ae8bcb242e25c6de07f94f0328d793::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite1ae8bcb242e25c6de07f94f0328d793::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite1ae8bcb242e25c6de07f94f0328d793::$classMap;

        }, null, ClassLoader::class);
    }
}
