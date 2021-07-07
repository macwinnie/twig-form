<?php
/**
 * Twig extension for `form` functions
 */

namespace macwinnie\TwigForm\Twig;

use Twig\Extension\ExtensionInterface;
use macwinnie\PHPHelpers as mwh;

/**
 * Twig extension providing usefull functions for working with forms
 */
class Extension implements ExtensionInterface {

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig\TwigFunction[]
     *
     *
     * ##### Twig function `form_open`
     *
     * Twig function to open an HTML form by a provisioned `<form>` tag.
     *
     * Usage:
     * ```twig
     * {{ form_open( attributes ) }}
     * ```
     *
     * The parameter `attributes` is a dictionary (key-value-list) for
     * provisioning the `<form>` tag.
     *
     * Key-names can be all valid attribute names of the `<form>` tag,
     * see [w3schools.com](https://www.w3schools.com/html/html_forms_attributes.asp).
     *
     * Some specials do exist:
     *
     * * `method` defaults to `POST`; if not `GET` or `POST`,
     *    an additional `_method` input of `hidden` type will
     *    be added.
     * * You can add the additional attribute `token`, if you
     *   want to add a hidden input with the name `_token`
     *   e.g. to use your CSRF token within the form.
     * * If no `enctype` attribute is given, this extension
     *   uses `multipart/form-data` by default.
     * * Since this extension is part of TwigForm, which uses
     *   Bootstrap (version 5) for styling, our form `class`
     *   attribute defaults to `form-horizontal` – please
     *   adjust for your needs.
     * * You can also add only additional classes without
     *   overriding the already mentioned `class` attribute
     *   by using `add_class` attribute – it takes one string
     *   to be appended to the actual `class` attribute
     *
     *
     * ##### Twig function `form_close`
     *
     * Twig function that returns a `</form>` tag;
     * closing of `form_open` function.
     *
     * Usage:
     * ```twig
     * {{ form_close() }}
     * ```
     *
     * ##### Twig function `request_data`
     *
     * Twig function that returns a value out of the default global
     * `$_REQUEST`, `$_GET` or `$_POST` variables.
     *
     * Usage:
     * ```twig
     * {{ request_data( name [, default [, method ]] ) }}
     * ```
     *
     * * `name` is the string value of the name to be looked for
     * * `default` (optional) is a default value to be returned, if
     *   variable does not exist
     * * `method` (optional) represents the specific method to be
     *   looked into – `GET`, `POST` or `REQUEST`. If no value given,
     *   the function defaults it to `NULL` and then will use
     *   `$_REQUEST` for lookup.
     */
    public function getFunctions() {

        $functions = [];

          /*************/
         /* form_open */
        /*************/
        $functions[] = new \Twig\TwigFunction('form_open', function ( $attributes = [] ) {

            if ( $attributes == NULL ) {
                $attributes = [];
            }
            $attributes     = array_change_key_case ( $attributes , CASE_LOWER );

            // check method
            $method = strtoupper( mwh\getArrayValue( $attributes, 'method', 'POST' ) );
            // any other method than `GET` has to be `POST` within the `<form>` tag and the
            // form has to be provided an additional input to transfer the actual method.
            $attributes['method'] = $method !== 'GET' ? 'POST' : $method;

            // extract token
            $tkn = mwh\extractArrayValue( $attributes, 'token' );

            // enctype defaults to `multipart/form-data`
            if (
                 ! isset( $attributes[ 'enctype' ] )
                or in_array( $attributes[ 'enctype' ], [ '', NULL ] )
            ) {
                $attributes[ 'enctype' ] = 'multipart/form-data';
            }

            // build class attribute
            if (
                 ! isset( $attributes[ 'class' ] )
                or in_array( $attributes[ 'class' ], [ /*'',*/ NULL ] )
            ) {
                $attributes[ 'class' ] = 'form-horizontal';
            }
            if (
                      isset( $attributes[ 'add_class' ] )
                and ! in_array( $attributes[ 'add_class' ], [ '', NULL ] )
            ) {
                $attributes[ 'class' ] .= ' ' . $attributes[ 'add_class' ];
            }

            // build regular form opening
            $form = [];
            $form[] = '<form';
            foreach ( $attributes as $name => $value ) {
                $form[] = $name . '="' . str_replace( '"', '&quot;', $value ) . '"';
            }
            $form[] = '>';

            // Additional appendix to form opening:
            // #1 method
            if ( ! in_array( $method, [ 'GET', 'POST' ] ) ) {
                $form[] = "\n" . '<input type="hidden" name="_method" value="' . $method . '" />';
            }
            // #2 token
            if ( $tkn != NULL ) {
                $form[] = "\n" . '<input type="hidden" name="_token" value="' . $tkn . '" />';
            }

            // return the form opening
            return new \Twig\Markup( implode(' ', $form), 'UTF-8' );
        });

          /**************/
         /* form_close */
        /**************/
        $functions[] = new \Twig\TwigFunction('form_close', function () {
            return new \Twig\Markup( '</form>', 'UTF-8' );
        });

          /****************/
         /* request_data */
        /***************/
        $functions[] = new \Twig\TwigFunction('request_data', function ( $name, $default = NULL, $method = NULL ) {
            switch ( strtoupper( $method ) ) {
                case 'GET':
                    $checkVar = $_GET;
                    break;
                case 'POST':
                    $checkVar = $_POST;
                    break;
                default:
                    $checkVar = $_REQUEST;
                    break;
            }
            if ( ! isset( $checkVar[ $name ] ) ) {
                return $default;
            }
            return $checkVar[ $name ];
        });

        // Return the defined functions;
        return $functions;
    }

    /**
     * Returns the token parser instances to add to the existing list.
     * > Not used until now ...
     *
     * @return \Twig\TokenParser\TokenParserInterface[]
     */
    public function getTokenParsers() {
        return [];
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     * > Not used until now ...
     *
     * @return \Twig\NodeVisitor\NodeVisitorInterface[]
     */
    public function getNodeVisitors() {
        return [];
    }

    /**
     * Returns a list of filters to add to the existing list.
     * > Not used until now ...
     *
     * @return \Twig\TwigFilter[]
     */
    public function getFilters() {
        return [];
    }

    /**
     * Returns a list of tests to add to the existing list.
     * > Not used until now ...
     *
     * @return \Twig\TwigTest[]
     */
    public function getTests() {
        return [];
    }

    /**
     * Returns a list of operators to add to the existing list.
     * > Not used until now ...
     *
     * @return array<array> First array of unary operators, second array of binary operators
     */
    public function getOperators() {
        return [];
    }
}
