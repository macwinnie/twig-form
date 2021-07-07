<?php
/**
 * Twig extension for `form` functions
 */

namespace macwinnie\TwigForm\Twig;

/**
 * Twig helper for working with TwigForm
 */
class Helper {

    protected static $templates;

    /**
     * constructor function to load TwigForm specific Twig templates
     */
    public function __construct () {
        $twigLoader = new \Twig\Loader\FilesystemLoader( __DIR__ . '/../../templates' );
        $twig       = new \Twig\Environment( $twigLoader, [
            'debug' => true,
        ]);
        $twig->addExtension( new Extension() );

        static::$templates = [
            'base'       => $twig->load( 'base.twig' ),
            'form'       => $twig->load( 'form.twig' ),
            'formhtml'   => $twig->load( 'formhtml.twig' ),
            'form.macro' => $twig->load( 'macros/form.twig' ),
        ];
    }

    /**
     * function to fetch the specific twig templates
     * @return array list of Twig templates
     */
    public static function getTemplates() {
        return static::$templates;
    }
}
