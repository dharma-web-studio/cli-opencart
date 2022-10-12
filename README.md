<h1 align="center">dharmawebstudio/cli-opencart</h1>
<div align="center">
  <p>Dharma Web Studio's CLI for OpenCart 4, intended to be used only in development environment</p>
  <img src="https://img.shields.io/badge/opencart-4.0.1.1-blue" alt="Supported OpenCart Versions" />
  <a href="https://opensource.org/licenses/MIT" target="_blank"><img src="https://img.shields.io/badge/license-MIT-blue.svg" /></a>
</div>

This is a CLI tool compatible with OpenCart 4.x.x.x, and inspired in [oc_cli](https://github.com/iSenseLabs/oc_cli) by [iSenseLabs](https://isenselabs.com/).

## Prerequisites

- This setup assumes you are running OpenCart 4.X.X.X
- This project has been tested on Mac & Linux.
- Is recommended to use it together with [dharmawebstudio/docker-opencart](https://github.com/dharmawebstudio/docker-opencart).

## Usage

### Quick Setup

```
git clone https://github.com/dharmawebstudio/cli-opencart  ./cli-opencart
mv ./cli-opencart/admin/controller/cli ./REPLACE-WITH-ADMIN-DIRECTORY-NAME/controller/cli
mv ./cli-opencart/system/config/cli.php ./system/config/cli.php
mv ./cli-opencart/system/library/cli.php ./system/library/cli.php
mv ./cli-opencart/system/cli-framework.php ./system/cli-framework.php
mv ./cli-opencart/cli.php ./cli.php
rm -rf ./cli-opencart
```
