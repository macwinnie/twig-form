<?php

if ( isset( $_REQUEST[ 'template' ] ) ) {
    /**
     * This part has to convert a template into a form definition JSON
     */
    echo json_encode( $_REQUEST[ 'template' ] );
}
elseif ( false ) {
    /**
     * This part has to convert a form definition JSON into HTML
     */
}
elseif ( false ) {
    /**
     * This part has to validate form data against a form definition JSON
     */
}
else {
    /**
     * here, nothing has to be done ...
     */
    echo 'Nothing to do.';
}
