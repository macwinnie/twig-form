<?php

namespace macwinnie\TwigForm;

/**
 * Class that represents a row within the form.twig Macro of this package
 *
 * May have those attributes:
 *   * row."hidden"
 *   * row."noTitle"
 *   * row."title"
 *   * row."label_attributes"
 *   * row."checked"
 *   * row."rows"
 *   * row."cols"
 *   * row."multiple"
 *   * row."plaintext"
 *   * row."class"
 *   * row."readonly"
 *   * row."required"
 *   * row."autofocus"
 *   * row."disabled"
 *   * row."placeholder"
 *   * row."value"
 *   * row."attributes"
 *   * row."type"
 *   * row."options"
 *   * row."option_attributes"
 *   * row."help"
 */
class Row {

    private $name = NULL;
    private $attributes = [
        "type" => "text",
    ];

    protected static $possible_attributes = [
        "type",
        "hidden",
        "noTitle",
        "title",
        "label_attributes",
        "checked",
        "rows",
        "cols",
        "multiple",
        "plaintext",
        "class",
        "readonly",
        "required",
        "autofocus",
        "disabled",
        "placeholder",
        "value",
        "attributes",
        "options",
        "option_attributes",
        "help",
    ];

    /**
     * constructor of form row
     *
     * @param string  $ident may be either row name or JSON representation of row;
     *                       row name has to be non-empty and not `NULL`.
     * @param boolean $json  set to `true` if `$ident` is JSON representation ...
     */
    public function __construct ( $ident, $json = false ) {
        if ( $json ) {
            throw new \Exception( "Not jet implemented ..." );
        }
        elseif (
            $ident != NULL and
            trim( $ident ) != ''
        ) {
            $this->name = $ident;
        }
        else {
            throw new \Exception( "Row has to have a valid, non-empty name!", 12 );
        }
    }

    /**
     * function to transform Row into JSON String
     *
     * @return string JSON representation
     */
    public function __toString () {
        $stringify = array_merge (
            $this->attributes,
            [
                'name' => $this->name
            ]
        );
        $string    = json_encode( $stringify );
        return $string;
    }
}
