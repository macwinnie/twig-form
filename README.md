# TwigForm

## Usage

This repo is meant to be included within other projects, so simply run this command to require it:

```sh
composer require macwinnie/twigform
```

Since the tool is also tested, please ensure to run `composer install --no-dev` in production stages, so no unnecessary tools are installed and autoloaded within your production environment!

## Testing

All functionalities are developed along the BDD (behaviour driven development) principles. Therefor, [Behat](https://docs.behat.org) is used to write Gherkin test scenarios and test them – all that takes part within the `/tests` folder.

To run the tests, you'll need the full composer installation with all dependencies, the production and development ones. For example use `devopsansiblede/apache:latest` to run everything that follows:

```sh
docker pull devopsansiblede/apache
docker run -p80:80 -d --name twigform -v $(pwd):/var/www/html devopsansiblede/apache
docker exec -it -u www-data twigform composer install
docker exec -it -u www-data twigform vendor/bin/behat
```

## Documentation

[Documentation current master state](https://macwinnie.github.io/twig-form/namespaces/macwinnie-twigform.html)

The functions within this repository are documented with DocBlock style. To visualize the documentation, the project is using [phpDocumentor](https://phpdoc.org/) to generate a viewable website with the documentation within the directory `/docs`.

To create the latest documentation, simply run the following Docker command:

```sh
docker pull phpdoc/phpdoc:3
docker pull macwinnie/md2rst:latest
rm -rf docs
docker run --rm -v $( pwd )/guides:/data -t macwinnie/md2rst:latest
docker run --rm -v $( pwd ):/data phpdoc/phpdoc:3 --sourcecode
cat <<EOF >> docs/css/base.css

code,
code.prettyprint {
    background: var(--primary-color-lighten);
    border: 1px solid var(--code-border-color);
    border-radius: var(--border-radius-base-size);
    padding: 0.1em 0.4em;
    margin: 0.1em 0.2em;
    font-size: 0.9em !important;
}
pre.prettyprint {
    font-size: 0.8em !important;
}
EOF
```

As long as `md` isn't supported officially by phpDocumentors Guides, we need to translate the additional `md` Documentations to `rst` format. For that, the additional docker run is used.

*ATTENTION:* The phpDocumentor tag `latest` from Docker is somehow a very old one – one wants to use a version tag like the `:3` above.

## Licence

[CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.en)
