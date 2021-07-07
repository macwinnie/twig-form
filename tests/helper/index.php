<?php

error_reporting( E_ALL );

require __DIR__ . '/../../vendor/autoload.php';

use macwinnie\TwigForm\Twig\Helper as TwigHelper;

$templates = ( new TwigHelper() )::getTemplates();


if ( isset( $_REQUEST[ 'template' ] ) ) {
    /**
     * This part has to convert a template into a form definition JSON
     */
    echo $templates[ 'base' ]->render([
        'headline' => 'Template rendering to form',
    ]);
}


elseif ( isset( $_REQUEST[ 'json2form' ] ) ) {
    /**
     * This part has to convert a form definition JSON given as `json2form` into HTML
     */
    $x = new macwinnie\TwigForm\Form();
    $x->selectTemplate( 'formhtml' );
    $x->loadJSON( $_REQUEST[ 'json2form' ] );
    $x->addRenderAttribute( 'headline', 'JSON to HTML form' );
    echo $x->renderForm();
}


elseif ( isset( $_REQUEST[ 'formvalidate' ] ) ) {
    /**
     * This part has to validate form data against a form definition JSON
     */
    echo $templates[ 'base' ]->render([
        'headline' => 'Form-Submit validation',
    ]);
}


else {
    /**
     * here, nothing has to be done ...
     */
    echo $templates[ 'base' ]->render([
        'headline' => 'Nothing to do.',
    ]);
}
