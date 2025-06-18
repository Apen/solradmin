# solradmin

[![Latest Stable Version](https://img.shields.io/packagist/v/apen/recordsmanager?label=version)](https://packagist.org/packages/apen/solradmin)
[![Total Downloads](https://img.shields.io/packagist/dt/apen/recordsmanager)](https://packagist.org/packages/apen/solradmin)
[![TYPO3](https://img.shields.io/badge/TYPO3-12.4-orange.svg?style=flat-square)](https://typo3.org/)
[![TYPO3](https://img.shields.io/badge/TYPO3-13.4-orange.svg?style=flat-square)](https://typo3.org/)

>  With this module you can directly access your solr module, search records with an interface, delete some specifics records and do other operations (with multi core support)

## What does it do?

With this module you can directly access your solr module, search records with an interface, delete some specifics records.

Do not hesitate to contact me if you have any good ideas.

This extension work with the last LTS of TYPO3.

## Screenshots

![](https://raw.githubusercontent.com/Apen/solradmin/master/Resources/Public/Images/solradmin-list.png)

## Settings

Just install the extension and import the typoscript.
After, you can tweak the different variables and specify several solr connections :

```
module.tx_solradmin {
    settings {
        itemsPerPage = 10
        connections {
            dev {
                scheme = https
                host = 127.0.0.1
                port = 8983
                path = /solr/core_fr/
                fieldList = id,site,title,url
            }
        }
    }
}
```

