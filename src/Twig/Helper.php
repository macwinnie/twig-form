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
     * function to fetch the specific twig templates
     * @return array list of Twig templates
     */
    public static function getTemplates() {

        if ( static::$templates == NULL ) {
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

        return static::$templates;
    }
}
