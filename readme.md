<div align="center">

  ![Basis](./assets/basis.png)

  # Basis

  A WordPress boilerplate. Get a local dockerized WordPress project up and running complete with secrets encryption, dependency management/compilation, and more by running a single command.

  [![packagist package version](https://img.shields.io/packagist/v/fivefifteen/basis.svg?style=flat-square)](https://packagist.org/packages/fivefifteen/basis)
  [![packagist package downloads](https://img.shields.io/packagist/dt/fivefifteen/basis.svg?style=flat-square)](https://packagist.org/packages/fivefifteen/basis)
  [![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/fivefifteen/basis?style=flat-square)](https://github.com/fivefifteen/basis)
  [![license](https://img.shields.io/github/license/fivefifteen/basis.svg?style=flat-square)](https://github.com/fivefifteen/basis/blob/main/license.md)

  <a href="https://fivefifteen.com" target="_blank"><img src="./assets/fivefifteen.png" /><br /><b>A Five Fifteen Project</b></a>

</div>


## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Usage](#usage)
- [Configuration](#configuration)
- [Related Projects](#related-projects)
- [License Information](#license-information)


## Features
 
- Dockerized development environment via [Lando] & [Docker]
- Secrets encryption via [SOPS]
- Intergrated deployment solution via [WordUp] & [Deployer]
- WordPress terminal control via [WP-CLI]
- WordPress core & plugins management using [Packagist] & [WordPress Packagist] as repositories via [Composer]
    - (*[Five Fifteen Plugins] & [Advanced Custom Fields] repositories are optionally pre-configured as well*)
- Theme boilerplate via [Primer]
    - Front-end dependency management using [GitHub] & [npm] as repositories via [Fetcher]
    - JavaScript & CSS/SCSS compilation/minification via [Piler]
    - [and more...](https://github.com/fivefifteen/primer)
- All of the above in a single command 👌


## Requirements

- [PHP] 8.1 or above
- [Composer]
- [Lando]


## Usage

Run the following command (*replacing "my-new-website" with your website's slug*):

```sh
composer create-project fivefifteen/basis my-new-website
```

The [setup script](setup.php) will ask you a few questions (*feel free to mash enter and leave everything with default values*) and when it's finished the URL to the local version of your new website will be displayed.

Visiting that URL will show you the WordPress setup screen where you can create your admin user account and then log in to the WordPress dashboard.

See your site's newly generated [readme.md](readme.template.md) on what to do from there.


## Configuration

The default values for any of the questions that are asked by the [setup script](setup.php) can be configured by setting an environment variable with the `BASIS_` prefix:

```sh
export BASIS_SOPS_AGE_KEY="age1ql3z7hjy54pw3hyww5ayyfg7zqgvc7w3j2elw8zmrj2kg5sfn9aqmcac8p"
```


## Related Projects

- [Primer] - A WordPress theme boilerplate. The perfect starting point for your custom WordPress theme.
- [WordUp] - A WordPress [Deployer] Recipe.


## License Information

GPL-2.0 (See the [license.md file](license.md) for more info)


[Advanced Custom Fields]: https://advancedcustomfields.com
[Composer]: https://getcomposer.org
[Deployer]: https://deployer.org
[Docker]: https://docker.com
[Fetcher]: https://github.com/fivefifteen/fetcher
[GitHub]: https://github.com
[Five Fifteen Plugins]: https://plugins.fivefifteen.com
[Lando]: https://lando.dev
[npm]: https://npmjs.com
[Packagist]: https://packagist.org
[PHP]: https://php.net
[Piler]: https://github.com/fivefifteen/piler
[Primer]: https://github.com/fivefifteen/primer
[SOPS]: https://getsops.io
[WordPress Packagist]: https://wpackagist.org
[WP-CLI]: https://wp-cli.org
[WordUp]: https://github.com/fivefifteen/wordup