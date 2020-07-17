<h1>
    <img align="left" align="bottom" width="40" height="40" src="resources/images/icons/icon.png" />
    PHP Backtester
</h1>

This package allows you to **test** you trading strategies. 

## Installation

You can install the package via composer:

```bash
composer require darvin/backtester
```

---
## Usage

#### Init
First, lets initialize backtester.

```php
$bt = new \Darvin\BackTester\BackTester();
```

#### Init
Next, lets feed backtester with some financial data.

| Plugin | README |
| ------ | ------ |
| Dropbox | [plugins/dropbox/README.md][PlDb] |
| GitHub | [plugins/github/README.md][PlGh] |
| Google Drive | [plugins/googledrive/README.md][PlGd] |
| OneDrive | [plugins/onedrive/README.md][PlOd] |
| Medium | [plugins/medium/README.md][PlMe] |
| Google Analytics | [plugins/googleanalytics/README.md][PlGa] |

```php
$bt->setData($records);
```



