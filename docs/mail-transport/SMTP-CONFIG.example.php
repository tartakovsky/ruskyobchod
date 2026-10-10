<?php
// Copy outside the public web root with mode 0600. Never commit a real password.
return [
    'host' => 'smtp.m1.websupport.sk',
    'port' => 465,
    'encryption' => 'ssl',
    'username' => 'objednavky@gastronom-bratislava.sk',
    'password' => 'REPLACE_ON_SERVER',
    'from' => 'objednavky@gastronom-bratislava.sk',
    'name' => 'Gastronom',
];
