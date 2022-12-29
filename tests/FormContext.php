<?php

namespace macwinnie\TwigFormTests;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FormContext extends HeadlessBrowserContext {

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @Then the JSON should contain key-tree :keytree with :count sub-elements
     */
    public function theJsonShouldContainKeyTreeWithSubElements( $keytree, $count ) {
        $json  = json_decode( $this->lastResponseBody(), true );
        $value = getArrayValue( $json, $keytree );
        Assert::assertEquals( $count, count( $value ) );
    }

    /**
     * @Then the JSON should contain not-NULL key-tree :keytree
     */
    public function theJsonShouldContainNotNullKeyTree( $keytree ) {
        $json    = json_decode( $this->lastResponseBody(), true );
        $value   = getArrayValue( $json, $keytree );
        Assert::assertNotNull( $value );
    }

    /**
     * @Then the JSON should have value :value at key-tree :keytree
     */
    public function theJsonShouldHaveValueAtKeyTree( $val, $keytree ) {
        $json  = json_decode( $this->lastResponseBody(), true );
        $wildcard_rx = '/\.\*\.?/';
        $kts = new \ArrayObject( preg_split( $wildcard_rx, $keytree ) );
        $ktsi = $kts->getIterator();
        $haystack = [ $json ];
        while( $ktsi->valid() ) {
            $kt = $ktsi->current();
            $ktsi->next();
            $nextHaystack = [];
            foreach ( $haystack as $h ) {
                $result = getArrayValue( $h, $kt );
                // there still exists another key-tree to check ...
                if ( is_array( $result ) ) {
                    $nextHaystack = array_merge( $nextHaystack, $result );
                }
                else {
                    $nextHaystack[] = $result;
                }

            }
            $haystack = $nextHaystack;
        }
        Assert::assertContains( $val, $haystack );
    }

}
