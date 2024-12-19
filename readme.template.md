# {{BASIS_PROJECT_NAME}}

This is a WordPress website created with [Basis].


## Table of Contents

- [Requirements](#requirements)
- [Setup](#setup)
- [Commands](#commands)
- [Useful Links](#useful-links)


## Requirements

- [PHP] 8.1 or above
- [Composer]
- [Lando]

Also:

- Your SSH key added to the staging and production servers in order to fetch the database/uploads as well as deploy
- The AGE private key used to encrypt the configuration files placed in `~/.config/sops/age/keys.txt` on your machine in order to decrypt those files


## Setup

1. Clone this repository:

```sh
git clone {{BASIS_PROJECT_REPOSITORY}} {{BASIS_PROJECT_SLUG}}
```

2. Start the container:

```sh
cd {{BASIS_PROJECT_SLUG}}
lando start
```

3. Optionally pull down the database and uploads from staging:

```sh
lando dep db:pull staging
lando dep uploads:pull staging
```


## Commands

| Command | Description |
| --- | --- |
| `lando` | Displays a list of available [Lando] commands |
| `lando build` | Compiles/Minifies Javascript & CSS/SCSS files via [Piler] |
| `lando composer` | Displays a list of available [Composer] commands |
| `lando composer install` | Installs the project dependencies |
| `lando dep` | Displays a list of available [WordUp]/[Deployer] commands |
| `lando dep db:pull [stage]` | Pulls remote database to localhost |
| `lando dep db:push [stage]` | Pushes local database to remote host |
| `lando dep deploy [stage]` | Deploys your WordPress project |
| `lando dep templates:render [stage]` | Renders mustache template files |
| `lando dep uploads:pull [stage]` | Pulls uploads from remote to local |
| `lando dep uploads:push [stage]` | Pushes uploads from local to remote |
| `lando dep wp:config:create [stage]` | Generates a wp-config.php file |
| `lando destroy` | Destroys your app |
| `lando poweroff` | Spins down all lando related containers |
| `lando rebuild` | Rebuilds your app from scratch, preserving data |
| `lando run` | Runs a script from composer.json's `scripts` section |
| `lando run decrypt` | Decrypts encrypted configuration files via [SOPS] |
| `lando run decrypt:<auth\|deploy>` | Decrypts `auth.encrypted.json` or `deploy.encrypted.yml` |
| `lando run encrypt` | Encrypts configuration files |
| `lando run encrypt:<auth\|deploy>` | Encrypts `auth.encrypted.json` or `deploy.encrypted.yml` |
| `lando theme` | Runs composer commands in the theme directory |
| `lando theme install` | Installs the theme dependencies |
| `lando theme run` | Runs a script from the theme's composer.json's `scripts` section |
| `lando theme fetcher` | Displays a list of available [Fetcher] commands |
| `lando theme fetcher install [...dependencies]` | Installs theme dependencies from [GitHub] or [npm] |
| `lando ssh` | Run commands inside of the container |
| `lando ssh-fix` | Fixes ssh auth sock permissions for MacOS users |
| `lando start` | Starts your app |
| `lando stop` | Stops your app |
| `lando wp` | Displays a list of available [WP-CLI] commands |

## Useful Links

- [GitHub's documentation on Generating a new SSH key and adding it to the ssh-agent](https://docs.github.com/en/github/authenticating-to-github/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)
- [Lando's documentation on Developing Offline](https://docs.lando.dev/guides/offline-dev.html)
- [Lando's documentation on Trusting the CA](https://docs.lando.dev/config/security.html#trusting-the-ca)


[Basis]: https://github.com/fivefifteen/basis
[Composer]: https://getcomposer.org
[Deployer]: https://deployer.org
[Fetcher]: https://github.com/fivefifteen/fetcher
[GitHub]: https://github.com
[Lando]: https://lando.dev
[npm]: https://npmjs.com
[PHP]: https://php.net
[Piler]: https://github.com/fivefifteen/piler
[SOPS]: https://getsops.io
[WP-CLI]: https://wp-cli.org
[WordUp]: https://github.com/fivefifteen/wordup