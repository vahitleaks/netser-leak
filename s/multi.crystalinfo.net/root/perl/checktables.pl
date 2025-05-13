#!/usr/bin/perl

#use Apache2;
$path = "/usr/local/wwwroot/www.crystalinfo.net/root/";
system "/usr/local/mysql/bin/mysqlcheck -u root -pkerem39 --repair MCRYSTALINFO > $path/repair.txt";
system "/usr/local/mysql/bin/mysqlcheck -u root -pkerem39 --optimize MCRYSTALINFO > $path/optimize.txt";

