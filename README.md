# ddeployer

Deploy Laravel applications via Github or Gitlab webhooks

## Installing

```shell
composer require ziorye/ddeployer
```

## Usage

1) Run the command below to publish the package config file `config/ddeployer.php`:

```shell
php artisan vendor:publish --provider="Ziorye\DDeployer\DDeployerServiceProvider"
```

2) Open your `.env` and add the `SECRET_TOKEN` to it:

```dotenv
SECRET_TOKEN=[you can use `Str::random()` to generate a random alpha-numeric string]
```

3) Add new GitHub webhook manually by using the following values:

- Payload URL: `config('app.url') . '/ddeployer/deploy'`
- Content Type: application/json
- Secret: the `SECRET_TOKEN` value your just set to .env
- Which events? Just the push event is enough.

## Upgrading

1) Run the command below to upgrade the package

```shell
composer update ziorye/ddeployer
```

2) Overwrite the existing package config file `config/ddeployer.php`: 

```shell
php artisan vendor:publish --provider="Ziorye\DDeployer\DDeployerServiceProvider" --force
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/ziorye/ddeployer/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/ziorye/ddeployer/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT
