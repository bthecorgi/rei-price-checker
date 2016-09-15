# REI Price Checker

Checks price for REI items. Run like this:

```sh
php index.php --file="PATH/TO/XLSX/FILE.xlsx"
```

### XLSX format

An example can be found in `resources/example.xlsx`:

| Title                   | Size | Color         | SKU        | Price   | URL                                |
|:-----------------------:|:----:|:-------------:|:----------:|:-------:|:----------------------------------:|
| Osprey Atmos 65 AG Pack | M    | Graphite Grey | 8784510008 | $259.95 | https://www.rei.com/product/878451 |


### Setting up a cronjob

Results of the script can be sent by email on a regular basis. To do this, run `crontab -e` and add the command to be executed with the desired schedule:

```sh
php PATH/TO/index.php --file="PATH/TO/XLSX/FILE.xlsx" --only-show-cheaper-items --no-color | mail -s "REI Price Checker" [EMAIL_ADDRESS]
```