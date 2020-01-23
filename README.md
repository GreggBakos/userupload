# User upload script
This is a PHP script, that is executed from the command line, which accepts a CSV file as input and processes the CSV file. The parsed file data is to be inserted into a MySQL database.
# Setup
Clone the repo
No further action is required
# From the command line
View the file directives
```
php ./user_upload.php --help
```
Import a csv file
```
php ./user_upload.php --file [filename] -u [db user] -p [db password] -h [db host]
```
Perform a dry run
```
php ./user_upload.php --file [filename] --dry_run -u [db user] -p [db password] -h [db host]
```
Drop and create database table
```
php ./user_upload.php --file [filename] --create_table -u [db user] -p [db password] -h [db host]
```
### You are good to go!
