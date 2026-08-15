<?php
/*
$user_data="Emmanuel";
$salt=bin2hex(random_bytes(16));//generate a random salt
$pepper="AsecretPepperString";//not stored in the database, but hardcoded in the application
$dataToHash=$user_data.$salt.$pepper;
$hashed_data=hash('sha256',$dataToHash);
echo $hashed_data;*/
//password_hash() function is used to hash the password and store it in the database
$user_data="Emmanuel";
$options=[
    'cost'=>12
];
$hashedpwd=password_hash($user_data,PASSWORD_DEFAULT,$options);
echo $hashedpwd;