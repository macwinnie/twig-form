<?php

namespace macwinnie\TwigForm;

use Twig\TemplateWrapper;

use macwinnie\TwigForm\Twig\Helper as TwigHelper;
use macwinnie\TwigForm\Template;

/**
 * Form class defining Forms by macwinnie\TwigForm.
 *
 * <hr/><hr/>
 *
 * ### Twig Macro `form.twig`
 *
 * This class relies on the Twig macro `form.twig` bundled with this package.
 * To be able to use this class properly with that macro, it'll be described here.
 *
 * #### Creator
 *
 * <!--
 * `{` requires an escape sequence within phpDocumentor docblocks, which is `{{}` –
 * so the correct notation in docblock is `{{}`
 * -->
 * The macro constructor looks like `{% macro create( form, errors = {{} ) %}`.
 * In the following macro documentation, we'll have a look on all important
 * configuration options for the macro.
 *
 * <p><details><summary style="cursor: pointer;"><strong>▶ Click to show minimal Twig example code using the macro...</strong></summary>
 *
 * ```twig
 * {% import "macros/form.twig" as forms %}
 *
 * {% set form = {
 *     "create":
 *     {
 *         "method": "get"
 *     },
 *     "rows":
 *     [
 *         {
 *             "name": "formelement",
 *             "placeholder": "text shown as placeholder",
 *             "type": "text",
 *             "title": "single line textfield"
 *         }
 *     ]
 * } %}
 *
 * {% set errors = {
 *     'formelement': 'This error will be shown.'
 * } %}
 *
 * {{ forms.create( form, errors ) }}
 * ```
 *
 * Rendering this Twig template will result in this HTML snippet:
 *
 * ```html
 * <form method="GET" enctype="multipart/form-data" class="form-horizontal" >
 *     <div class="form-group row has-error" >
 *         <label for="formelement" class="col-md-3 col-form-label">single line textfield</label>
 *         <div class="col-md-9">
 *             <input id="formelement" class=" form-control" type="text" class="form-control" name="formelement" placeholder="text shown as placeholder" value="" />
 *             <span class="help-block errormsg">
 *                 <strong>This error will be shown.</strong>
 *             </span>
 *         </div>
 *     </div>
 *     <input type="submit" name="submit" value="submit" class="btn btn-primary" />
 * </form>
 * ```
 *
 * </details></p>
 *
 * <hr/>
 *
 *
 * The Macro can be initiated given an object `form`, which will be described in detail below.
 *
 * The optional dictionary `errors` is used to populate all input rows (and so fields) with errors
 * that occured e.g. while validating the sent form data. It has to be a dictionary with
 * the `row.name` as key and the error message to be displayed as value.
 *
 * #### The `form` object
 *
 * ```js
 * {
 *     "create": {{},
 *     "buttons": [],
 *     "submit": {{},
 *     "rows": []
 * }
 * ```
 *
 * The form object in general can consist out of three sub-objects: the `create` and the `rows`
 * objects are mandatory, the `buttons` object is optional.
 *
 * *All given attributes within this Macro documentation are case sensitive!*
 *
 * ##### The `create` sub-object
 *
 * This is a key-value dictionary that can consist out of those keys:
 * * `method` (default `POST` – has to be a valid HTTP Request method)
 * * `url`, `route` or `action` – those define the actual action path for the form.
 * * `enctype` – will be overridden if `files` is set `true` by `multipart/form-data`
 * * any other Attribute to be part of the `form` tag like `class`, `id`, ...
 *
 * ##### The `submit` sub-object or the (optional) `buttons` sub-list
 *
 * By default and if `buttons` sub-list is not defined, you'll get a simple `submit` input
 * form element at the end of your form. That element can be configured by the `submit`
 * sub-object of the whole form object with these attributes:
 *
 * * `name` – equivalent to the `<input>` attribute name, defaults to `submit`
 * * `value` – equivalent to the `<input>` attribute value, defaults to `submit`
 * * `class` – equivalent to the `<input>` attribute class, defaults to `btn btn-primary`
 *
 * As said above, if the `buttons` sub-list is defined, the `submit` sub-object is fully ignored.
 *
 * That's because of the fact, that the `buttons` sub-list tells the macro per definition,
 * that the form actions are all managed by those buttons. So the `buttons` sub-list is a list of
 * button definition dictionaries whith those attributes:
 *
 * * `name` – equivalent to the `<button>` attribute name
 * * `value` – equivalent to the `<button>` attribute value
 * * `class` – equivalent to the `<button>` attribute class
 * * `text`– equivalent to the (shown) value between opening and closing `<button>` tags
 *
 * For buttons, you have to define all those attributes since they'll stay empty otherwise.
 *
 * ##### The `rows` sub-list
 *
 * A form created by this form Twig macro is structured into different rows – each row
 * holds one field of the form.
 *
 * ###### common attributes
 *
 * * `name` – the name of the rows input field, turned into the `id` attribute and the `name` attribute. It's the only mandatory attribute for each single row.
 * * `type` – defines the type of current row form element. May be one out of `checkbox`, `radio`, `textarea`, `select` or `datalist` – every other value will cause an `<input>` tag with the `type` attribute set to given value. Value defaults to `text`.
 * * `value` – if no `<option>` tags are required, this attribute may set the value of the form element to be sent; defaults to already sent value if possible to retrieve
 * * `help` – HTML content to be shown beneath the form element to help the user to correctly fill the form.
 * * `title` - content of the rows `<label>` tag; defaults to `name`
 * * `noTitle` – turns off the showing of a label in / for current input row
 * * `class` – CSS class(es) of the form elements of the current row.
 * * `placeholder` – sets the placeholder attribute on `<textarea>` and regular `<input>` tags
 * * `autofocus` – if defined, the current element is provided the `autofocus` attribute. Don't provide, if you don't want to apply!
 * * `disabled` – if defined, option attribute `disabled` is set. Don't provide, if you don't want to apply!
 * * `hidden` – turns an `input` field (and the whole row) into a hidden row / input. Don't provide, if you don't want to apply!
 * * `readonly` – turns the form element of current row into a read-only one. Don't provide, if you don't want to apply!
 * * `plaintext` – if row is readonly, Bootstrap allows to view the form element as plain text instead of form element. This attributes allows you to apply that.  Don't provide, if you don't want to apply!
 * * `required` – activates the HTML / Browser check for required form elements: the element of current row has to be filled for being able to submit the form. Don't provide, if you don't want to apply!
 * * `label_attributes` – attributes that are applied to the label of the row
 * * `option_attributes` – default attributes to be added to all `<option>` tags belonging to the current row, if no `attributes` attribute defined within `options` sub-object item (see below)
 *
 * ###### `<textarea>` specific attributes
 *
 * * `cols` – columns of the textarea
 * * `rows` – rows of the thextarea
 *
 * ###### `<select>` specific attributes
 *
 * * `multiple` – if set, one can select multiple values from current row `<select>` element. Don't provide, if you don't want to apply!
 *
 * ###### the sub-object `options` with attributes for form elements with options
 *
 * Those form elements are `<select>`, `<input>` of types `checkbox` or `radio` and `<input>` in addition of a `<datalist>` tag.
 *
 * For the `<input>` tag in combination with the `<datalist>` tag only the marked `(*)` attributes are useable since `<datalist>`s children `<option>` are not (directly) visible.
 *
 * * `attributes` – override `option_attributes`. Has to be a key-value-dictionary, so the tag will be expanded to `<tag key="value">`. Double quotes `"` are replaced by `&quote;` within values. `(*)`
 * * `class` – CSS class(es) of the option; defaults to `class` attribute of current `row` (only `<input>` of types `checkbox` and `radio`)
 * * `description` – visible text fo the current opiton; defaults to `value` attribute.
 * * `disabled` – if defined, option attribute `disabled` is set. So if you don't want to disable an option, just don't define that attribute. `(*)`
 * * `selected` – if set, the `selected` attribute for current option is set. Is also set, if the value is set within `request_data` twig function for current row, which reflects `$_REQUEST` variable.
 * * `value` – the actual value sent with form submission. `(*)`
 *
 * <hr/><hr/>
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
