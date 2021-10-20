<?php
/**
 * Form class defining Forms by macwinnie\TwigForm.
 */

namespace macwinnie\TwigForm;

use macwinnie\TwigForm\Twig\Helper as TwigHelper;

/**
 * Exception class for TwigForm
 */
class Form {

    private $templates;
    private $formDefinition   = NULL;
    private $selectedTemplate = 'form';
    private $formIdentifier   = 'form_data';
    private $renderAttributes = [];

    /**
     * constructor of Form element
     */
    public function __construct () {
        $this->templates = ( new TwigHelper() )::getTemplates();
    }

    /**
     * load a Form by JSON description
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

    /**
     * select another template to be rendered instead of `form`
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
            throw new Exception("The template is not loaded (yet).", 8);
        }
    }

    /**
     * add additional Twig templates
     *
     * @param string               $name     identifier of the new template
     * @param Twig\TemplateWrapper $template Twig template
     */
    public function addTemplate( $name, Twig\TemplateWrapper $template ) {
        $this->templates[ $name ] = $template;
    }

    /**
     * add a render attribute / TwigVariable
     *
     * @param string $name  name of the variable (top level only!)
     * @param mixed  $value value / array / ... to be assigned for rendering
     */
    public function addRenderAttribute( $name, $value ) {
        $this->renderAttributes[ $name ] = $value;
    }

    /**
     * function for final rendering the selected Template with the given form data
     *
     * @return string HTML content of the selected template with the form
     */
    public function renderForm() {
        if ( $this->formDefinition == NULL ) {
            throw new Exception("No Form JSON defined.", 9);
        }
        $this->addRenderAttribute( $this->formIdentifier, $this->formDefinition );
        return $this->templates[ $this->selectedTemplate ]->render(
            $this->renderAttributes
        );
    }
}