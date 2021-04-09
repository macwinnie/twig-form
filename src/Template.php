<?php
/**
 * macwinnie\TwigForm
 */

namespace macwinnie\TwigForm;

use \macwinnie\RegexFunctions as rf;

use \Twig\Environment;
use \Twig\Loader\ArrayLoader;
use \Twig\Extension\StringLoaderExtension;
use \Twig\Node\Expression\AssignNameExpression;
use \Twig\Node\Expression\NameExpression;
use \Twig\Node\Node;
use \Twig\Node\ForNode;
use \Twig\Node\IfNode;

/**
 * Analyzer class for Twig templates
 */
class Template {

    /** @var Environment Twig Environment */
    private $twig;
    /** @var string      full template without extracted sub strings (blocks) */
    private $fullTemplate;
    /** @var array       list of templates by names */
    private $loader;
    /** @var array       tree of variables */
    private $variables;
    /** @var array       list of default values */
    private $defaults;
    /** @var boolean     status if set variables should be ignored */
    private $ignoreSet          = false;
    /** @var string      name of local main template */
    private $localTemplate      = 'localTemplate';
    /** @var string      prefix for block names */
    private static $blockPrefix = 'block.inc_';
    /** @var array       list of Twig reseverd variables */
    private static $twigReservedVars = [
        'loop.*',
        '_key',
    ];

    /**
     * create the Twig instance for our current template
     *
     * @param String $template_string template string to be translated
     */
    public function __construct ( $template_string ) {
        $this->loader = $this->analyzeTemplate( $template_string );
        $loader = new ArrayLoader( $this->loader );
        $this->twig = new Environment( $loader );
        $this->twig->addExtension(
            new StringLoaderExtension()
        );
        $this->fullTemplate = $template_string;
    }

    /**
     * function to start analyzing the template
     *
     * @param  string   $template_string template to be analyzed
     * @return array                     list of template and subtemplates / blocks
     */
    private function analyzeTemplate ( $template_string ) {
        $templates = static::extractBlocks( $template_string );
        $templates[ $this->localTemplate ] = $template_string;
        return $templates;
    }

    /**
     * function to get names of all defined variables
     *
     * @return array    list of variables
     */
    public function getVariables () {
        if ( $this->variables == null ) {
            $data = $this->basicVarFetch();
            $this->deepFetchVars( $data );
            $this->variables = $data;
        }
        return static::flattenVariables( $this->variables );
    }

    /**
     * create . representation of variable names
     *
     * @param  mixed    $variables variable array to work with
     * @return array               dot representation of variables
     */
    protected static function flattenVariables ( $variables ) {
        $vars = [];
        if ( ! empty( $variables ) ) {
            foreach ($variables as $key => $sub) {
                $var    = $key;
                if (
                    is_array( $sub ) and
                    ! empty( $sub )
                ) {
                    foreach ( static::flattenVariables( $sub ) as $subvar ) {
                        $vars[] = $var . '.' . $subvar;
                    }
                }
                $vars[] = $var;
            }
        }
        return $vars;
    }

    /**
     * function to get a list of all block names within this Object
     *
     * @return array list of block names
     */
    public function getBlocks() {
        $all_templates    = array_keys( $this->loader );
        $block_name_regex = rf\REGEX_DELIMITER . '^' . rf\delimiter_preg_quote( static::$blockPrefix ) . '.+$' . rf\REGEX_DELIMITER . 'im';
        $all_blocks       = preg_grep( $block_name_regex, $all_templates );

        return array_intersect_key( $this->loader, array_flip( $all_blocks ) );
    }

    /**
     * function that analyzes all defined templates for variables
     *
     * @param  string   $template_name name of template to analyze
     * @return array                   list of variable names
     */
    private function analyse ( $template_name ) {
        $src  = $this->twig->getLoader()->getSourceContext( $template_name );
        $tkn  = $this->twig->tokenize( $src );
        $ast  = $this->twig->parse( $tkn );

        $vars = $this->walkThrough( $ast );

        return $vars;
    }

    /**
     * helper function to walk throuh the Twig template
     *
     * @param  Node    $ast Ast object from template
     * @param  array   $for for array
     * @return array        list of variables
     */
    protected function walkThrough( Node $ast, $for = [] ) {
        $variables = [];
        switch ( get_class( $ast ) ) {
            case AssignNameExpression::class:
            case NameExpression::class:
                if (
                    $ast->hasAttribute( 'always_defined' ) &&
                    ! $ast->getAttribute( 'always_defined' )
                ) {
                    $variables = array_replace_recursive(
                        $variables, [
                            $ast->getAttribute('name') => [],
                        ]
                    );
                }
                break;

            case GetAttrExpression::class:
                $variables = array_replace_recursive(
                    $variables, $this->visitGetAttrExpression( $ast, null, $for )
                );
                break;

            default:
                if ( $ast->count() ) {
                    foreach ( $ast as $node ) {
                        $variables = array_replace_recursive(
                            $variables, $this->walkThrough( $node, $for )
                        );
                    }
                }
        }

        return $variables;
    }

    /**
     * Visit Object|Array
     *
     * @param Node        $ast
     * @param string|null $subKey sub object|array key
     * @param array       $for
     *
     * @return array
     */
    protected function visitGetAttrExpression(Node $ast, $subKey = null, $for = []) {
        $node = $ast->getNode('node');
        // current node attribute
        $attr = $ast->getNode('attribute')->getAttribute('value');
        if (get_class($node) === NameExpression::class) {
            $key = $node->getAttribute('name');
            // // special vars
            // if (in_array($key, ['loop', '_self'])) {
            //     return [];
            // }

            if ($subKey) {
                $subVar = [
                    $attr => [
                        $subKey => [],
                    ],
                ];
            } else {
                $subVar = [
                    $attr => [],
                ];
            }

            // for loop value
            $var = [];
            if ($for && $for[0] && $for[1] === $key) {
                $var = [
                    $for[0] => [
                        $subVar,
                    ],
                ];
            } elseif (! $for || ($for && ! empty($for[0]))) {
                $var = [
                    $key => $subVar,
                ];
            }

            return $var;
        }

        return $this->visitGetAttrExpression($node, $attr, $for);
    }

    /**
     * Get nested array deepest key
     * @param array       $array
     * @param string|null $key
     * @return string|null
     */
    private function getNestedKey( &$array, &$key = null ) {
        $keys = array_keys( $array );
        if ( count( $keys ) > 0 ) {
            $key   = $keys[ 0 ];
            $array = &$array[ $keys[ 0 ] ];
            $this->getNestedKey( $array, $key );
        }

        return $key;
    }

    /**
     * Set nested array key value
     *
     * @param $array
     * @param $key
     * @param $value
     */
    private function setNestedValue( &$array, $key, $value ) {
        $keys = array_keys( $array );
        if ( count( $keys ) > 0) {
            if ( $key === $keys[ 0 ] ) {
                $array[ $key ] = $value;
                return;
            }

            $array = &$array[ $keys[ 0 ] ];
            $this->setNestedValue( $array, $key, $value );
        }
    }

    /**
     * function to analyse the template and extract blocks to separate templates
     *
     * @param  string  &$template the template to be analyzed and the blocks
     *                            should be striped out of
     * @return array              the list of sub-templates
     */
    protected static function extractBlocks ( &$template ) {

        $include_tpl = '{%% include \'' . static::$blockPrefix . '%s\' %%}';

        // fetch block informations
        $bstarts = static::getBlockStarts( $template );
        $bends   = static::getBlockEnds( $template );

        $c         = count( $bstarts );
        $templates = [];

        if ( $c > 0 ) {

            list( $all_offsets, $all_marks ) = static::orderMarks( $bstarts, $bends );
            static::addPreAndPostToMarks ( $all_offsets, $all_marks, $template );

            $template   = null;
            $tpl_helper = [];

            while ( ! empty( $all_offsets ) ) {
                $first_offset = strval( reset( $all_offsets ) );
                $template     = static::extractSubTemplate ( $first_offset, $all_marks, $all_offsets, $tpl_helper, $template, $include_tpl, 'name' );
            }

            foreach ($tpl_helper as $key => $value) {
                $templates[ static::$blockPrefix . $key ] = $value;
            }
        }

        return $templates;
    }

    /**
     * function to sort and group found marks
     *
     * @param  array   $start_marks result of rf\getRegexOccurences
     * @param  array   $end_marks   result of rf\getRegexOccurences
     *
     * @return array                two arrays as array:
     *                               * first:  a list of all offsets
     *                               * second: all marks
     */
    private static function orderMarks ( $start_marks, $end_marks ) {

        if ( count( $start_marks ) != count( $end_marks ) ) {
            throw new Exception("Count of opening and closing marks do not match", 1);
        }

        // prepare the block data for further steps
        foreach ( [ 'start', 'end' ] as $var ) {

            $v1 = 'local_' . $var . '_marks';
            $v2 = $var . '_marks';

            $$v1 = [];

            foreach ( $$v2 as $bmark ) {
                $bmark[ 'type' ]           = $var;
                $$v1[ $bmark[ 'offset' ] ] = $bmark;
            }

            unset( $$v2 );
        }

        // no mark has the same offset / key as another one ...
        $all_marks = array_replace( $local_start_marks, $local_end_marks );
        // we need them sorted by their offset, so the key
        ksort( $all_marks );
        $all_offsets = array_keys( $all_marks );

        return [ $all_offsets, $all_marks ];
    }

    /**
     * add pre and post content to marks
     *
     * @param  array   $all_offsets list of offsets – the currently viewed mark
     * @param  array   &$all_marks   all marks by their offsets
     * @param  string  $template    template string to work on
     *
     * @return void
     */
    private static function addPreAndPostToMarks ( $all_offsets, &$all_marks, $template ) {

        $old_end_offset = 0;
        foreach ( $all_offsets as $i => $offset ) {

            $bmark       = $all_marks[ $offset ];
            $next_offset = ( $i + 1 < count( $all_offsets ) ) ? $all_offsets[ $i + 1 ] : strlen( $template );

            $all_marks[ $offset ]['snippets'] = [];

            // the pre-snippet
            $length = $bmark[ 'offset' ] - $old_end_offset;
            $all_marks[ $offset ]['snippets'][ 'pre' ] = mb_strcut( $template, $old_end_offset, $length );

            // the post-snippet
            $old_end_offset = $offset + $bmark[ 'length' ];
            $length = $next_offset - $old_end_offset;
            $all_marks[ $offset ]['snippets'][ 'post' ] = mb_strcut( $template, $old_end_offset, $length );
        }
    }

    private function getNestedFors (  ) {
        $fstarts = static::getForStarts( $this->fullTemplate );
        $fends   = static::getForEnds( $this->fullTemplate );
        $c       = count( $fstarts );

        if ( $c > 0 ) {
            list( $all_offsets, $all_marks ) = static::orderMarks( $fstarts, $fends );
            static::addPreAndPostToMarks ( $all_offsets, $all_marks, $template );
        }
    }

    /**
     * function to extract a sub template – like blocks and for loops
     *
     * @param  string      $key            current offset to start with
     * @param  array       &$all_marks     list of all existing block marks (starts and ends)
     * @param  array       &$all_offsets   list of all existing offsets
     * @param  array       &$templates     list of templates defined
     * @param  array|null  &$pre           null by default, string if something should be
     *                                     prepended to inner content of sub template
     * @param  string      $include_tpl    format string of how to replace the template within template
     * @param  string|null $template_name  "name" key of the current sub template
     *
     * @return string                      outer template containing replace string for inner template
     */
    protected static function extractSubTemplate ( $key, &$all_marks, &$all_offsets, &$templates, $pre = null, $include_tpl = '*||%s||*', $name_key = null ) {

        $cur_mark  = $all_marks[ $key ];

        if ( $name_key === null ) {
            $template_name = bin2hex( random_bytes( 32 ) );
        }
        else {
            $template_name = $cur_mark[ $name_key ];
        }
        $include_string = sprintf( $include_tpl, addslashes( $template_name ) );

        switch ( $cur_mark[ 'type' ] ) {
            case NULL:
                throw new Exception("Sub template not defined", 2);
                break;

            case ! 'start':
                throw new Exception("Closing of sub template found before one was opened", 3);
                break;
        }

        if ( isset( $templates[ $template_name ] ) ) {
            throw new Exception("Template " . $template_name . " seems to be defined twice", 4);
        }

        unset( $all_marks[ $key ] );
        unset( $all_offsets[ array_search( $key, $all_offsets ) ] );

        $next_key = strval( reset( $all_offsets ) );

        $content  = null;
        while ( $all_marks[ strval( $next_key ) ][ 'type' ] == 'start' ) {
            $content  = static::extractSubTemplate( $next_key, $all_marks, $all_offsets, $templates, $content, $include_tpl, $name_key );
            $next_key = reset( $all_offsets );
        }

        // from here, $next_key is the $closing_key
        $closing_key  = strval( $next_key );
        $closing_mark = $all_marks[ $closing_key ];

        unset( $next_key );
        unset( $all_marks[ $closing_key ] );
        unset( $all_offsets[ array_search( $closing_key, $all_offsets ) ] );

        if ( $content == null ) {
            if ( $cur_mark['snippets']['post'] == $closing_mark['snippets']['pre'] ) {
                $content = $cur_mark['snippets']['post'];
            }
            else {
                throw new Exception("Content missmatch in sub template ... cannot proceed", 5);
            }
        }

        $post     = $closing_mark[ 'snippets' ][ 'post' ];
        if ( $pre == null ) {
            $pre  = $cur_mark[ 'snippets' ][ 'pre' ];
        }

        $template = $pre . $include_string . $post;
        $content  = $cur_mark[ 'full' ] . $content . $closing_mark[ 'full' ];

        $templates[ $template_name ] = $content;

        return $template;
    }

    /**
     * function to fetch all Twig Block startings
     *
     * @param  string  $template the template string
     * @return array             see `\macwinnie\RegexFunctions\getRegexOccurences` – with `name` attribute
     */
    protected static function getBlockStarts( $template ) {
        $regex = rf\REGEX_DELIMITER . '\{%\s*block\s+(("([^"\\\\]*(\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(\\\\.[^\'\\\\]*)*)\')|([^\s]+))\s*%\}' . rf\REGEX_DELIMITER . 'im';
        return rf\getRegexOccurences( $regex, $template, [ 'name' => [ 3, 5, 7 ] ] );
    }

    /**
     * function to fetch all Twig Block endings
     *
     * @param  string  $template the template string
     * @return array             see `\macwinnie\RegexFunctions\getRegexOccurences`
     */
    protected static function getBlockEnds( $template ) {
        $regex = rf\REGEX_DELIMITER . '\{%\s*endblock\s*%\}' . rf\REGEX_DELIMITER . 'im';
        return rf\getRegexOccurences( $regex, $template );
    }

    /**
     * function to fetch all Twig For loop startings
     *
     * *recognized are these cases:*
     *   * `{% for element in array %}`
     *   * `{% for key, element in array %}`
     *   * `{% for (key, )element in array | filter %}`
     *   * `{% for element in from..to %}`
     *
     * @todo correct handling of filters with from..to along documentation of Twig
     *
     * @param  string  $template the template string
     *
     * @return array             see `\macwinnie\RegexFunctions\getRegexOccurences` – with different attributes
     */
    protected static function getForStarts( $template ) {
        $regex = rf\REGEX_DELIMITER . '\{\%\s*for\s+(([^\s,]+?)\s*,\s*)?([^\s]+?)\s+in\s+((([^\s]+?)\.\.([^\s]+?)|([^\s\|]+?))(\s*\|\s*([^\s]+?))?)\s*\%\}' . rf\REGEX_DELIMITER . 'im';
        $for_attributes = [
            'key'     => [  2 ],
            'element' => [  3 ],
            'from'    => [  6 ],
            'to'      => [  7 ],
            'array'   => [  8 ],
            'filter'  => [ 10 ],
        ];
        return rf\getRegexOccurences( $regex, $template, $for_attributes );
    }

    /**
     * function to fetch all Twig For loop endings
     *
     * @param  string  $template the template string
     * @return array             see `\macwinnie\RegexFunctions\getRegexOccurences`
     */
    protected static function getForEnds( $template ) {
        $regex = rf\REGEX_DELIMITER . '\{%\s*endfor\s*%\}' . rf\REGEX_DELIMITER . 'im';
        return rf\getRegexOccurences( $regex, $template );
    }

    /**
     * check if given variable has a default value
     *
     * @param  string  $var_name variable name
     * @return boolean           true if variable has a default value, false if not
     *
     * @throws Exception         if variable var_name not existent
     */
    public function checkDefaultValue ( $var_name ) {
        if ( ! in_array( $var_name, $this->getVariables() ) ) {
            throw new Exception("Variable \"" .$var_name. "\" does not exist", 6);
        }
        if ( $this->defaults == null ) {
            $this->checkDefaults();
        }
        return array_key_exists( $var_name, $this->defaults );
    }

    /**
     * check for default values of variables
     *
     * @return void
     */
    private function checkDefaults() {

        $vars       = $this->getVariables();
        $f_regex    = $re = rf\REGEX_DELIMITER . '\{\{\s*%s\s*\|\s*default\s*\((.*?)\)\s*\}\}' . rf\REGEX_DELIMITER . 'mi';
        $str_quotes = ['"', "'"];

        foreach ( $vars as $var ) {
            $regex     = sprintf( $f_regex, rf\delimiter_preg_quote( $var ) );
            $templates = array_keys( $this->loader );
            $i = 0;
            while ( ! isset( $this->defaults[ $var ] ) and $i < count( $templates ) ) {
                $default = rf\getRegexOccurences(
                    $regex,
                    $this->loader[ $templates[ $i ] ],
                    [ 'default' => [ 1 ] ]
                );

                $first = trim( $default[0][ 'default' ] );

                if ( ( $x = substr( $first , -1 ) ) == substr( $first, 0, 1) and in_array( $x, $str_quotes ) ) {
                    $this->defaults[ $var ] = substr( $first, 1, -1 );
                }
                else {
                    $this->defaults[ $var ] = [
                        'lookup' => $first,
                    ];
                }
                $i++;
            }
        }
    }

    /**
     * fetch default value of variable
     *
     * @param  string  $var_name name of variable
     * @param  boolean $lookup   defaults to true – false if only directly defined default
     *                           values should be returned
     * @return mixed             default value of variable defined within the template
     *
     * @throws Exception         if variable var_name not existent
     */
    public function defaultValue ( $var_name, $lookup = true ) {
        try {
            $break = ! $this->checkDefaultValue( $var_name );
        } catch (Exception $e) {
            throw $e;
        }
        if ( $break ) {
            return null;
        }
        $val = $this->defaults[ $var_name ];
        if (
            is_array( $val ) and
            array_key_exists( 'lookup', $val )
        ) {
            if ( $lookup ) {
                $val = $this->defaultValue( $val[ 'lookup' ] );
            }
            else {
                $val = null;
            }
        }
        return $val;
    }

    /**
     * function to ignore set variables
     *
     * @return void
     */
    public function ignoreSetVariables () {
        $this->ignoreSet = true;
        $this->variables = null;
        $this->getVariables();
    }

    /**
     * check if ignoring set variables is activ
     *
     * @return boolean
     */
    public function checkIgnoringSetVariables () {
        return $this->ignoreSet;
    }

    /**
     * basic variable fetch
     *
     * @return array   list of main variable names
     */
    private function basicVarFetch() {
        $all_templates = array_keys( $this->loader );
        $data = [];
        foreach ($all_templates as $template_name) {
            $data = array_replace( $data, $this->analyse( $template_name ) );
        }
        return array_keys( $data );
    }

    /**
     * deeply fetch variables from template(s)
     *
     * @param  array   &$vars array of variables
     * @return void
     */
    private function deepFetchVars( &$vars ) {

        # ignore set variables, if set
        $this->removeSetVarsIfSet( $vars );

        # until now, the variables should be values in
        # an array, now let's flip them
        $vars = array_map(
            function( $item ) {
                return is_array( $item ) ?: [];
            }, array_flip( $vars )
        );

        # fetch dictionary names
        foreach ( $vars as $var => &$subs ) {
            foreach ( $this->loader as $tpl ) {
                $this->fetchDictionaryNames( $var, $tpl, $subs );
            }
        }
        unset( $subs );
    }

    /** @var string part one of RegEx */
    private static $fetchDictionaryNamesRegex1 = rf\REGEX_DELIMITER . '\{\{\s*';
    /** @var string last part of RegEx */
    private static $fetchDictionaryNamesRegex2 = '\.([^\s]+)\s*\}\}' . rf\REGEX_DELIMITER . 'im';

    /**
     * fetch dictionary names from template
     *
     * @param  string $var   variable name to fetch subs from
     * @param  string $tpl   template string to work with
     * @param  array  &$subs sub keys of variable $var
     *
     * @return void
     */
    private function fetchDictionaryNames ( $var, $tpl, &$subs ) {
        $regex = static::$fetchDictionaryNamesRegex1 .
                 rf\delimiter_preg_quote( $var ) .
                 static::$fetchDictionaryNamesRegex2;
        $proceed = preg_match_all( $regex, $tpl, $matches );
        if ( $proceed === 1 ) {
            foreach ( $matches[1] as $match ) {
                $s = explode( '.', $match );
                $x = &$subs;
                foreach ( $s as $l ) {
                    if ( ! array_key_exists( $l, $x ) ) {
                        $x[ $l ] = [];
                    }
                    $x = &$x[ $l ];
                }
                unset( $x );
            }
        }
    }

    /**
     * if option is activated remove variables that are set from within the template
     *
     * @param  array.   &$vars list of main variable names
     * @return void
     */
    private function removeSetVarsIfSet ( &$vars ) {
        if ( $this->checkIgnoringSetVariables() ) {
            $regf1 = rf\REGEX_DELIMITER . '\{\%\s*set\s*';
            $regf2 = '\s*(=|\%\})' . rf\REGEX_DELIMITER . 'im';
            foreach ( $vars as $id => $var ) {
                $regex = $regf1 . rf\delimiter_preg_quote( $var ) . $regf2;
                foreach ( $this->loader as $tpl ) {
                    if ( preg_match( $regex, $tpl ) === 1 ) {
                        unset( $vars[ $id ] );
                        break;
                    }
                }
            }
        }
    }
}
