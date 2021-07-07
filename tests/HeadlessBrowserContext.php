<?php

namespace macwinnie\TwigFormTests;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Behat\Behat\Tester\Exception\PendingException;

use GuzzleHttp\Client as Guzzle;
use DOMDocument;
use DOMXPath;

/**
 * Defines basic functions for all browser (GuzzleHttp) using contexts
 */
class HeadlessBrowserContext implements Context {

    protected $setRequestOptions;
    protected $client;
    protected $requestPayload;
    protected $lastResponse;
    protected $lastResponseDOM;
    protected $requestOptions;

    /**
     * function to initialize a new context of this type
     */
    public function __construct() {
        $this->requestOptions = [];
        $this->requestPayload = [];
        $this->setBaseUrl();
    }

    /**
     * define base url
     *
     * @param string $url defaults to localhost without ssl
     */
    public function setBaseUrl( $url = "http://localhost" ) {
        $this->baseUrl = $url;
        $this->client = new Guzzle([
            'base_uri' => $this->baseUrl,
        ]);
    }

    /**
     * set request options – they will be merged
     *
     * @param array $options key value dictionary to add new
     *                       request options like headers, etc
     */
    public function setRequestOptions( $options ) {
        if ( is_array( $options ) ) {
            foreach ( $options as $key => $value ) {
                if ( isset( $this->requestOptions[ $key ] ) ) {
                    $this->requestOptions[ $key ] = array_merge( $this->requestOptions[ $key ], $value );
                }
                else {
                    $this->requestOptions[ $key ] = $value;
                }
            }
        }
    }

    /**
     * @Given I am on :uri
     */
    public function iAmOn( $uri ) {
        $this->lastResponse = $this->client->get( $uri );
        Assert::assertLessThan( 400, intval( $this->lastResponse->getStatusCode() ) );
    }

    /**
     * @Then I should see :content
     */
    public function iShouldSee( $content ) {
        Assert::assertStringContainsStringIgnoringCase( $content, $this->lastResponseBody() );
    }

    /**
     * function to retrieve the content body of last response
     *
     * @return string
     */
    protected function lastResponseBody() {
        return $this->lastResponse->getBody()->getContents();
    }

    /**
     * get DOM representation of last response
     *
     * @return DOMDocument last response as DOM
     */
    protected function lastResponseDom() {
        if ( $this->lastResponseDOM == NULL ) {
            $this->lastResponseDOM = new DOMDocument();
            // use `LIBXML_NOERROR` since HTML5 does nomore have DTD
            // and also XHTML is not always used ...
            $this->lastResponseDOM->loadHTML( $this->lastResponseBody(), LIBXML_NOERROR );
        }
        return $this->lastResponseDOM;
    }

    /**
     * get Xpath search of last response
     *
     * @return DOMXpath
     */
    protected function lastResponseXPath() {
        return new DOMXpath( $this->lastResponseDOM() );
    }

    /**
     * @Given I have the payload
     */
    public function iHaveThePayload( PyStringNode $payload ) {
        if ( $payload == '' ) {
            $payload = '{}';
        }
        $jsonObj    = json_decode( $payload, true );
        $json_error = json_last_error();
        switch ( $json_error ) {
            case JSON_ERROR_NONE:
                break;

            default:
                throw ( new \Exception ( "You have an error within your JSON payload:\n" . json_last_error_msg() ) );
                break;
        }

        $this->requestPayload = [];
        if ( !is_null( $jsonObj ) ) {
            $this->requestPayload = $jsonObj;
        }
    }

    /**
     * @When /^I request "(GET|PUT|POST|DELETE|PATCH) ([^"]*)"$/
     */
    public function iRequest( $rest, $uri ) {

        $this->lastResponse    = NULL;
        $this->lastResponseDOM = NULL;

        $options = $this->requestOptions;
        if ( !empty( $this->requestPayload ) ) {
            $options[ 'form_params' ] = $this->requestPayload;
        }

        $this->lastResponse = $this->client->request(
            strtoupper( $rest ),
            $uri,
            $options
        );
    }

    /**
     * @Then I should see a JSON response
     */
    public function iShouldSeeAJsonResponse() {
        $tmp = json_decode( $this->lastResponseBody() );
        Assert::assertEquals( JSON_ERROR_NONE, json_last_error() );
    }

    /**
     * @Then There should be a :tag tag with text :cnt
     */
    public function thereShouldBeATagWithText( $tag, $cnt ) {
        $xpath   = '//' . $tag . '/text()';
        $results = $this->lastResponseXPath()->query( $xpath );
        $texts   = [];
        if ( ! is_null( $results ) ) {
            foreach ( $results as $result ) {
                $texts[] = $result->nodeValue;
            }
        }
        Assert::assertContains( $cnt, $texts );
    }

    /**
     * @Then There should be a :tag tag with attribute :attr and value :val
     */
    public function thereShouldBeATagWithAttributeAndValue( $tag, $attr, $val ) {
        $xpath = 'count(//' . $tag . '[@' . $attr . '=\'' . str_replace("'", "\\'", $val) . '\'])';
        $results = $this->lastResponseXPath()->evaluate( $xpath );
        Assert::assertGreaterThan( 0, $results );
    }

}
