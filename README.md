# Datagrid/Datatable module for Zend Framework
[![Master Branch Build Status](https://secure.travis-ci.org/zfc-datagrid/zfc-datagrid.png?branch=master)](http://travis-ci.org/zfc-datagrid/zfc-datagrid)
[![Coverage Status](https://coveralls.io/repos/github/zfc-datagrid/zfc-datagrid/badge.svg?branch=master)](https://coveralls.io/github/zfc-datagrid/zfc-datagrid?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zfc-datagrid/zfc-datagrid/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zfc-datagrid/zfc-datagrid/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/zfc-datagrid/zfc-datagrid/v/stable.png)](https://packagist.org/packages/zfc-datagrid/zfc-datagrid)
[![Latest Unstable Version](https://poser.pugx.org/zfc-datagrid/zfc-datagrid/v/unstable.png)](https://packagist.org/packages/zfc-datagrid/zfc-datagrid)
[![License](https://poser.pugx.org/zfc-datagrid/zfc-datagrid/license.png)](https://packagist.org/packages/zfc-datagrid/zfc-datagrid)

[![Join the chat at https://gitter.im/zfc-datagrid/Lobby](https://badges.gitter.im/zfc-datagrid/Lobby.svg)](https://gitter.im/zfc-datagrid/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Total Downloads](https://poser.pugx.org/zfc-datagrid/zfc-datagrid/downloads.png)](https://packagist.org/packages/zfc-datagrid/zfc-datagrid)
[![Monthly Downloads](https://poser.pugx.org/zfc-datagrid/zfc-datagrid/d/monthly)](https://packagist.org/packages/zfc-datagrid/zfc-datagrid)

A datagrid for ZF where the data input and output can be whatever you want...:-)

Over ***330 tests and 1000 assertions*** testing the stability currently! 

If you need help, please use following ressources
- [Installation](https://github.com/zfc-datagrid/zfc-datagrid#installation) 
-  ["Getting started guide"](https://github.com/zfc-datagrid/zfc-datagrid/blob/master/docs/02.%20Quick%20Start.md)
- [Documentation](https://github.com/zfc-datagrid/zfc-datagrid/blob/master/docs/)
- [Code examples](https://github.com/ThaDafinser/ZfcDatagridExamples/tree/master/src/ZfcDatagridExamples/Controller/)
- [Issues/Help](https://github.com/zfc-datagrid/zfc-datagrid/issues)

If you want to help out on this project:
- seek through the [issues](https://github.com/zfc-datagrid/zfc-datagrid/issues)
- [documentation](https://github.com/zfc-datagrid/zfc-datagrid/blob/master/docs/)
- ...any other help

## Features
* Datasources: Doctrine2 (QueryBuilder + Collections), Zend\Db, PhpArray, ... (others possible)
* Output types: jqGrid, Bootstrap table, PDF, Excel, CSV, console, ... (others possible)
  *  Bootstrap table with Daterange Filter need to load manually js and css
* different column types
* custom formatting, type based formatting (string, date, number, array...)
* column/row styling for all or based on value comparison
* column filtering  and sorting
* external data can be included to the dataset (like gravator or any other)
* pagination
* custom toolbar / view
* ...

## Installation

Install it with ``composer``
```sh
composer require zfc-datagrid/zfc-datagrid -o
```

> NOTE: with 1.x we dropped support for other installation technics. Especially the ZF2 autoloading was dropped. You just need to switch to composer installation, which will make your life easier, since it comes with all needed features

Add `ZfcDatagrid` to your `config/application.config.php`

Finally create the folder: `data/ZfcDatagrid`

You can continue 

### Test if it works

> NOTE: This needs the additional module `ZfcDatagridExamples` https://github.com/ThaDafinser/ZfcDatagridExamples
####Browser####

> Attention! Only PhpArray works out of the box!
> For Zend\Db\Sql\Select and Doctrine2 you need to install DoctrineORMModule (Doctrin2 creates the database for Zend\Db\Sql\Select)

**PhpArray** http://YOUR-PROJECT/zfcDatagrid/person/bootstrap

**Doctrine2** http://YOUR-PROJECT/zfcDatagrid/personDoctrine2/bootstrap

**Zend\Db\Sql\Select** http://YOUR-PROJECT/zfcDatagrid/personZend/bootstrap


####Console####
If you just type `php index.php` a help for all commands will be shown
```sh
cd YOUR-PROJECT/public/
php index.php datagrid person
php index.php datagrid person --page 2
php index.php datagrid person --sortBys=age
php index.php datagrid person --sortBys=age,givenName --sortDirs=ASC,DESC
```
## Continue with your own datagrid

Please read [Documentation](https://github.com/zfc-datagrid/zfc-datagrid/blob/master/docs/)

You can also use the [zfc-data-grid-plugin](https://github.com/agerecompany/zfc-data-grid-plugin) to create columns with an array configuration, instead of objects!


## Screenshots
![ScreenShot](https://raw.github.com/zfc-datagrid/zfc-datagrid/master/docs/screenshots/ZfcDatagrid_bootstrap.jpg)
![ScreenShot](https://raw.github.com/zfc-datagrid/zfc-datagrid/master/docs/screenshots/ZfcDatagrid_console.jpg)


