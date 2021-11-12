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
        $value = getArrayValue( $json, $keytree );
        Assert::assertEquals( $val, $value );
    }

}
