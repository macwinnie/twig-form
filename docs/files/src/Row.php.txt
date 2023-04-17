<?php

namespace macwinnie\TwigForm;

/**
 * Class that represents a row within the form.twig Macro of this package
 */
class Row {

    private $name       = NULL;
    private $options    = [];
    private $attributes = [
        "type" => "text",
    ];

    protected static $option_types = [
        "select",
        "checkbox",
        "radio",
        "datalist",
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

        $stringify = [
            $this->attributes,
            [
                'name' => $this->name
            ]
        ];

        $stringify = array_merge(
            $stringify,
            $this->getOptions()
        );

        $stringify = call_user_func_array( 'array_merge', $stringify );
        $string    = json_encode( $stringify );
        return $string;
    }

    /**
     * return options if they are set
     *
     * @return array list of options
     */
    protected function getOptions() {

        $type = $this->attributes [ 'type' ];

        if ( in_array( $type, static::$option_types ) ) {
            if ( empty( $this->options ) ) {
                throw new \Exception( sprintf( 'Row of type “%s” needs at least one option!', $type ), 13 );
            }
            else {
                return [
                    'options' => $this->options,
                ];
            }
        }
        else {
            return [];
        }
    }
}
