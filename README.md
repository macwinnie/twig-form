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
docker run -p80:80 -d --name twigform -v $(pwd):/var/www/html devopsansiblede/apache
docker exec -it -u www-data twigform composer install
docker exec -it -u www-data twigform vendor/bin/behat
```

## Licence

[CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/deed.en)
