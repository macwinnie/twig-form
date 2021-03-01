<?php

namespace macwinnie\TwigFormTests;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Behat\Behat\Tester\Exception\PendingException;

use macwinnie\TwigForm\Template;

/**
 * Defines application features from the specific context.
 */
class DefaultContext implements Context {

    private $template  = '';
    private $variables = [];
    private $blocks    = [];

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
    }

    /**
     * @Given the template
     */
    public function theTemplate( PyStringNode $string ) {
        $this->template  = new Template( $string->getRaw() );
        $this->variables = $this->template->getVariables();
        $this->blocks    = $this->template->getBlocks();
    }

    /**
     * @Then I should get :number variables
     */
    public function iShouldGetVariables( $number ) {
        Assert::assertEquals( intval( $number ), count( $this->variables ));
    }

    /**
     * @Then :name is one variable name
     */
    public function isOneVariableName( $name ) {
        Assert::assertContains( $name, $this->variables );
    }

    /**
     * @Then I should get :number blocks
     */
    public function iShouldGetBlocks( $number ) {
        Assert::assertEquals( intval( $number ), count( $this->blocks ));
    }

    /**
     * @Then variable :var has default value :value
     */
    public function variableHasDefaultValue( $var, $value ) {
        Assert::assertTrue( $this->template->checkDefaultValue( $var ) );
        Assert::assertEquals( $value, $this->template->defaultValue( $var ) );
    }

    /**
     * @Then default value for :var exists but is inherited
     */
    public function defaultValueForExistsButIsInherited( $var ) {
        Assert::assertTrue( $this->template->checkDefaultValue( $var ) );
        Assert::assertNull( $this->template->defaultValue( $var, false ) );
    }
}
