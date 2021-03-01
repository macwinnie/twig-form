<?php

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

class Template {

    private $twig;
    private $loader;
    private $localTemplate = 'localTemplate';
    private static $blockPrefix   = 'block.inc_';

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
    }

    /**
     * function to start analyzing the template
     *
     * @param  string   $template_string template to be analyzed
     * @return [string]                  list of template and subtemplates / blocks
     */
    private function analyzeTemplate ( $template_string ) {
        $templates = static::extractBlocks( $template_string );
        $templates[ $this->localTemplate ] = $template_string;
        return $templates;
    }

    /**
     * function to get names of all defined variables
     *
     * @return [string] list of variables
     */
    public function getVariables () {
        $all_templates = array_keys( $this->loader );
        $data = [];
        foreach ($all_templates as $template_name) {
            $data = array_replace( $data, $this->analyse( $template_name ) );
        }
        return array_keys( $data );
    }

    /**
     * function to get a list of all block names within this Object
     *
     * @return [string] list of block names
     */
    public function getBlocks() {
        $all_templates    = array_keys( $this->loader );
        $block_name_regex = '/^' . preg_quote( static::$blockPrefix ) . '.+$/im';
        $all_blocks       = preg_grep( $block_name_regex, $all_templates );

        return array_intersect_key( $this->loader, array_flip( $all_blocks ) );
    }

    /**
     * function that analyzes all defined templates for variables
     *
     * @param  string   $template_name name of template to analyze
     * @return [string]                list of variable names
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
     * @return [mixed]      list of variables
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
                    $variables = array_replace_recursive( $variables, [
                        $ast->getAttribute('name') => [],
                    ] );
                }
                break;

            case GetAttrExpression::class:
                $variables = array_replace_recursive(
                    $variables, $this->visitGetAttrExpression( $ast, null, $for )
                );
                break;

            case ForNode::class:
                $variables = array_replace_recursive(
                    $variables, $this->visitForNode( $ast, null, $for )
                );
                break;

            default:
                if ($ast->count()) {
                    foreach ($ast as $node) {
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
            // special vars
            if (in_array($key, ['loop', '_self'])) {
                return [];
            }

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
     * Visit ForNode vars
     *
     * @param Node         $ast
     * @param array|string $seq seqNode variable
     * @param array        $for parent for node info
     *
     * @return array
     */
    protected function visitForNode(Node $ast, $seq = null, $for = []) {
        $valNode  = $ast->hasNode( 'value_target' ) ? $ast->getNode( 'value_target' ) : null;
        $seqNode  = $ast->hasNode( 'seq' )          ? $ast->getNode( 'seq' )          : null;
        $bodyNode = $ast->hasNode( 'body' )         ? $ast->getNode( 'body' )         : null;

        $val = $valNode &&
               get_class( $valNode ) === AssignNameExpression::class ? $valNode->getAttribute( 'name' ) : null;

        $seq  = $seq ?: null;
        $vars = [];
        if ( $seqNode && get_class( $seqNode ) === NameExpression::class ) {
            $seq = $seqNode->getAttribute( 'name' );
        }

        // sub for
        if (
            ! $seq &&
            $seqNode &&
            get_class( $seqNode ) === GetAttrExpression::class
        ) {
            $attr = $this->visitGetAttrExpression( $seqNode );
            if ( isset( $attr[ $for[ 1 ] ] ) ) {
                $vars = [ $for[ 0 ] => [ $attr[ $for[ 1 ] ] ] ];
            }

            $key = $this->getNestedKey( $attr );
            $sub = $this->visitForNode( $ast, $key );
            if ( isset( $sub[ $key ] ) ) {
                $this->setNestedValue( $vars, $key, $sub[ $key ] );
                unset( $sub[ $key ] );
            }
            // merge sibling for
            $vars = array_replace_recursive( $vars, $sub );

            return $vars;
        }

        $vars = $this->walkThrough( $bodyNode, [ $seq, $val ] );

        return array_replace_recursive( $vars, [ $seq => [] ] );
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
     * @return [mixed]            the list of sub-templates
     */
    protected static function extractBlocks ( &$template ) {

        // fetch block informations
        $bstarts = static::getBlockStarts( $template );
        $bends   = static::getBlockEnds( $template );

        if ( ($c = count( $bstarts ) ) != count( $bends ) ) {
            throw new Exception("Count of block openings and closings do not match", 1);
        }

        $templates = [];

        if ( $c > 0 ) {

            // prepare the block data for further steps
            foreach ( [ 'start', 'end' ] as $var ) {

                $v1 = 'block_' . $var . '_marks';
                $v2 = 'b' . $var . 's';

                $$v1 = [];

                foreach ( $$v2 as $bmark ) {
                    $bmark[ 'type' ]           = $var;
                    $$v1[ $bmark[ 'offset' ] ] = $bmark;
                }

                unset( $$v2 );
            }

            // fetch template snippets – pre and post mark
            // – no mark has the same offset / key as another one ...
            $all_marks = array_replace( $block_start_marks, $block_end_marks );
            // we need them sorted by their offset, so the key
            ksort( $all_marks );
            $all_offsets = array_keys( $all_marks );

            $old_end_offset = 0;
            foreach ( $all_offsets as $i => $offset ) {

                $bmark       = $all_marks[ $offset ];
                $next_offset = ( $i + 1 < count( $all_offsets ) ) ? $all_offsets[ $i + 1 ] : strlen( $template );

                // $v1 = 'block_' . $bmark[ 'type' ] . '_marks';
                $v1 = 'all_marks';

                $$v1[ $offset ]['snippets']           = [];

                // the pre-snippet
                $length = $bmark[ 'offset' ] - $old_end_offset;
                $$v1[ $offset ]['snippets'][ 'pre' ] = mb_strcut( $template, $old_end_offset, $length );

                // the post-snippet
                $old_end_offset = $offset + $bmark[ 'length' ];
                $length = $next_offset - $old_end_offset;
                $$v1[ $offset ]['snippets'][ 'post' ] = mb_strcut( $template, $old_end_offset, $length );
            }

            $template = null;
            while ( ! empty( $all_offsets ) ) {
                $first_offset = strval( reset( $all_offsets ) );
                $template     = static::createBlockTemplate( $first_offset, $all_marks, $all_offsets, $templates, $template );
            }
        }

        return $templates;
    }

    /**
     * create block template – and call itself recursive if there exist nested blocks
     *
     * @param  string    $key          current offset to start with
     * @param  [mixed]   &$all_marks   list of all existing block marks (starts and ends)
     * @param  [integer] &$all_offsets list of all existing offsets
     * @param  [mixed]   &$templates   list of templates defined
     * @param  mixed     $pre          null by default, string if something should be
     *                                 prepended to block inner content
     *
     * @return string                  outer template containing include of content block
     */
    protected static function createBlockTemplate ( $key, &$all_marks, &$all_offsets, &$templates, $pre = null ) {

        $cur_mark  = $all_marks[ $key ];

        switch ( $cur_mark[ 'type' ] ) {
            case NULL:
                throw new Exception("Block not defined", 2);
                break;

            case ! 'start':
                throw new Exception("Closing of block found before one was opened", 3);
                break;
        }

        $template_name  = static::$blockPrefix . $cur_mark[ 'name' ];
        $include_string = '{% include \''
                        . addslashes( $template_name )
                        . '\' %}';

        if ( isset( $templates[ $template_name ] ) ) {
            throw new Exception("Template " . $template_name . " seems to be defined twice", 4);
        }

        unset( $all_marks[ $key ] );
        unset( $all_offsets[ array_search( $key, $all_offsets ) ] );

        $next_key = strval( reset( $all_offsets ) );

        $content  = null;
        while ( $all_marks[ strval( $next_key ) ][ 'type' ] == 'start' ) {
            $content  = static::createBlockTemplate( $next_key, $all_marks, $all_offsets, $templates, $content );
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
                throw new Exception("Content missmatch in block ... cannot proceed", 5);
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
     * @return [mixed]           see \macwinnie\RegexFunctions\getRegexOccurences – with `name` attribute
     */
    protected static function getBlockStarts( $template ) {
        $regex = '/\{%\s*block\s+(("([^"\\\\]*(\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(\\\\.[^\'\\\\]*)*)\')|([^\s]+))\s*%\}/im';
        return rf\getRegexOccurences( $regex, $template, [ 'name' => [ 3, 5, 7 ] ] );
    }

    /**
     * function to fetch all Twig Block endings
     *
     * @param  string  $template the template string
     * @return [mixed]           see \macwinnie\RegexFunctions\getRegexOccurences
     */
    protected static function getBlockEnds( $template ) {
        $regex = '/\{%\s*endblock\s*%\}/im';
        return rf\getRegexOccurences( $regex, $template );
    }

}
