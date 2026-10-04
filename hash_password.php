<?php

$password = 'potenciana24';

$hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

echo $hash;