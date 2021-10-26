<?php
/**
 * Form class defining Forms by macwinnie\TwigForm.
 */

namespace macwinnie\TwigForm;

use Twig\TemplateWrapper;

use macwinnie\TwigForm\Twig\Helper as TwigHelper;
use macwinnie\TwigForm\Template;

/**
 * Exception class for TwigForm
 */
class Form {

    private $templates;
    private $formDefinition   = NULL;
    private $selectedTemplate = 'form';
    private $formIdentifier   = 'form_data';
    private $renderAttributes = [];

    private $formButtons      = [];
    private $formRows         = [];
    private $formID           = NULL;

    private $formMethod       = 'POST';
    private $fieldIDs         = [];
    private $hiddenFields     = [];
    private $formAction       = [];
    private $formEnctype      = 'multipart/form-data';

    /**
     * constructor of Form element
     */
    public function __construct () {
        $this->templates = TwigHelper::getTemplates();
    }

    /**
     * Function to build and return JSON representation of the current form
     *
     * @return string JSON
     */
    public function __toString() {

        $this->setFormID( 'twigform', false );

        $string = json_encode( [
            # * `method` (default `POST` – has to be a valid HTTP Request method)
            # * `url`, `route` or `action` – those define the actual action path for the form.
            # * `enctype` – will be overridden if `files` is set `true` by `multipart/form-data`
            # * any other Attribute to be part of the `form` tag like `class`, `id`, ...
            'create'  => $this->getFormCreate(),
            'rows'    => [],
            'buttons' => [],
        ] );

        return $string;
    }

    /**
     * function to provide create part of form definition
     *
     * @return array create part of form definition
     */
    private function getFormCreate() {
        $create = [];
        // method
        $create['method'] = $this->formMethod;

        // action / url / route
        if ( !empty( $this->formAction ) ) {
            $create[ $this->formAction[ 'key' ] ] = $this->formAction[ 'value' ];
        }

        // enctype
        $create['enctype'] = $this->formEnctype;

        $create['id'] = $this->formGetIdent();
    }

    /**
     * Load a Form by JSON description
     *
     * @param  string $json JSON representation of a form
     *
     * @return void
     * @throws Exception    JSON not valid
     */
    public function loadJSON( $json ) {
        $json_decoded = json_decode( $json, true );
        if ( json_last_error() != JSON_ERROR_NONE ) {
            throw new Exception( "The JSON definition is not valid: " . json_last_error_msg(), 10 );
        }
        else {
            $this->formDefinition = $json_decoded;
        }
    }

    public function formGetIdent () {
        if ( $this->formID == NULL ) {
            $this->setFormID(  );
        }
        return $this->formID();
    }

    /**
     * Select another template to be rendered instead of `form`
     *
     * @param  string    $name           name of template
     * @param  string    $formIdentifier ident of the form_data parameter to be used
     *
     * @return void
     * @throws Exception                 Template not existent / loaded ...
     */
    public function selectTemplate( $name, $formIdentifier = 'form_data' ) {
        if ( isset( $this->templates[ $name ] ) ) {
            $this->selectedTemplate = $name;
            $this->formIdentifier   = $formIdentifier;
        }
        else {
            throw new Exception( "The template is not loaded (yet).", 8 );
        }
    }

    /**
     * Add additional Twig templates for being rendered;
     * is not meant to be used with instances of \macwinnie\TwigForm\Template
     *
     * @param string          $name     identifier of the new template
     * @param TemplateWrapper $template Twig template
     */
    public function addTwigTemplate( $name, TemplateWrapper $template ) {
        $this->templates[ $name ] = $template;
    }

    /**
     * Add a render attribute / TwigVariable
     *
     * @param string $name  name of the variable (top level only!)
     * @param mixed  $value value / array / ... to be assigned for rendering
     */
    public function addRenderAttribute( $name, $value ) {
        $this->renderAttributes[ $name ] = $value;
    }

    /**
     * Function for final rendering the selected Template with the given form data
     *
     * @return string HTML content of the selected template with the form
     */
    public function renderForm() {
        if ( $this->formDefinition == NULL ) {
            try {
                $this->formDefinition = (string) $this;
            } catch ( \Exception $e ) {
                throw new Exception( "No Form JSON defined.", 9 );
            }
        }
        $this->addRenderAttribute( $this->formIdentifier, $this->formDefinition );
        return $this->templates[ $this->selectedTemplate ]->render(
            $this->renderAttributes
        );
    }

    /**
     * add a hidden field to the current form
     *
     * @param string $name  identifier (name and id postfix) for the hidden field
     * @param string $value value for the hidden field
     */
    public function addHiddenField ( $name, $value = '' ) {
        // @ToDo: check to have each identifier only once
        $this->hiddenFields[] = [
            'name'  => $name,
            'value' => $value
        ];
    }

    /**
     * set the form identifier
     *
     * @param string  $new      new identifier for this form instance
     * @param boolean $override attribute to define if formID should be
     *                          overridden by $new; defaults to `true`
     */
    public function setFormID ( $new, $override = true ) {
        $new = trim( $new );
        if (
            ! in_array( $new, [ NULL, '' ] ) and
            (
                $this->formID == NULL or
                $override = true
            )
        ) {
            $this->formID = $new;
        }
    }

    /**
     * set the form method
     *
     * `GET` and `POST` are regular methods, specials are supported as
     * defined in RFC 7231
     * https://datatracker.ietf.org/doc/html/rfc7231#section-4.3
     *
     * @param string $method method name
     */
    public function setMethod ( $method ) {

        $method = strtoupper( $method );

        // prepare for “special methods” not being `GET` and `POST`
        $methods = [
            'PUT',
            'DELETE',
            'PATCH',
            'HEAD',
            'OPTIONS',
            'CONNECT',
            'TRACE',
        ];
        if ( in_array( $method, $methods ) ) {

            $this->addHiddenField( '_method', $method );

            $method = 'POST';
        }

        // set method
        if ( in_array( $method, [ 'GET', 'POST' ] ) ) {
            $this->formMethod = $method;
        }
        elseif ( getenv( 'DEBUG' ) ) {
            // Logger::error( 'Method `' . $method . '` is no valid method according to RFC 7231.' );
        }
    }

    /**
     * set enctype for form
     *
     * @param string  $enctype see here for allowed values:
     *                         https://wiki.selfhtml.org/wiki/HTML/Elemente/form
     * @param boolean $force   set enctype even if it's a value not allowed
     */
    public function setFormEnctype ( $enctype, $force = false ) {
        if (
            in_array( $enctype, [
                'application/x-www-form-urlencoded',
                'multipart/form-data',
                'text/plain'
            ]) or
            $force
        ) {
            $this->formEnctype = $enctype;
        }
    }

    /**
     * Basic transformation from Twig Template (analyzed,
     * so instance of `macwinnie\TwigForm\Template`) to
     * Form (of `static` class) object
     *
     * @param  Template $tpl template object to be transformed
     * @param  string   $id  identifier / name of form
     *
     * @return static        result of basic transformation
     */
    public static function transformTemplate ( Template $tpl, $id = NULL ) {
        $instance = new static();
        $instance->setFormID( $id );

        return $instance;
    }
}
