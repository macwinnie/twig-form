<?php

error_reporting( E_ALL );

require __DIR__ . '/../../vendor/autoload.php';

$twigLoader = new \Twig\Loader\FilesystemLoader( __DIR__ . '/../../templates' );
$twig       = new \Twig\Environment( $twigLoader, [
    'debug' => true,
]);
$twig->addExtension( new macwinnie\TwigForm\Twig\Extension() );


$templates = [
    'base' => $twig->load( 'base.twig' ),
    'form' => $twig->load( 'form.twig' ),
];

$example_form = [
        'create' => [
            'action'  => '/tests/helper/?',
        ],
        'buttons' => [
            [
                "text"  => "submit",
                "class" => "btn btn-primary",
                "name"  => "submitbutton",
                "value" => "submit_val",
            ],
        ],
        "rows" => [
            [
                "name"        => "text",
                "placeholder" => "ph text",
                "type"        => "text",
                "title"       => "single line textfield",
            ],
        ],
    ];


if ( isset( $_REQUEST[ 'template' ] ) ) {
    /**
     * This part has to convert a template into a form definition JSON
     */
    echo json_encode($example_form);
}


elseif ( isset( $_REQUEST[ 'json2form' ] ) ) {
    /**
     * This part has to convert a form definition JSON into HTML
     */
    echo $templates[ 'form' ]->render([
        'headline'  => 'JSON to HTML form',
        'form_data' => $example_form
    ]);
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
