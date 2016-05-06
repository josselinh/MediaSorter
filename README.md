What is in the package?
- CliInterative is a class to interact with user in PHP_CLI environment
- MediaSorter is a class to get datetimes and to move files
- cli_sort_analyse to analyse files and retrieve dates
- cli_sort_execute to execute MediaSorter

What it does?
Analyse a file and extract different dates :
- Date in the filename IMG_20160506_203000.jpg
- Modified Date of the file
- Exif
- Directory 2016/05/06/example.jpg

How to use?
>php cli_sort_analyse.php
Options "input" : this is the directory you want to analyse
Options "output" : this is way the script outputs result (print => print, file => create csv file)
>Input? /home/josselin/Images/Dossier
>Output? (print, file) [print] file
Or you can do >php cli_sort_analyse.php /home/josselin/Images/Dossier file

>php cli_sort_execute.php
Options "input" : this is the csv file previously generated
Options "output" : this is the output directory (where files will be moved)
>Input? /home/josselin/Images/Dossier/analyse.csv
>Output? /home/josselin/Images/Sorted
Or you can do >php cli_sort_execute.php /home/josselin/Images/Dossier/analyse.csv /home/josselin/Images/Sorted