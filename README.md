**Lara-Driven**
> Lara-Driven is a package designed to create and organize layers using the Domain Drive Design methodology.

### This repository is only compatible with laravel: `7.*` to `11.*`


## Installation


First Step, execute the command.

```shell script
composer require larakeeps/lara-driven
```

Second step, run the migration to create the tables: ```auth_guard_otp_codes```
```shell script
php artisan migrate
```
Third step, publish the authguard configuration, to publish the configurations run the command below:
```shell script
php artisan vendor:publish --tag=authguard-otp-config 
```

**Generating otp code**
```php
use Larakeeps\LaraDriven\Facades\OTP;

/** 
 * 
 * The create method has 3 parameters, 1 mandatory and 2 optional.
 * 
 * The $reference parameter is used with the assertive condition function in code validation.
 * The $email parameter is used in the same way as the $reference parameter
 * 
 * @param string $phone : required
 * @param string $email : optional
 * @param string $reference : optional
 * 
 * @method static OTP create(string $phone, string|null $email, string|null $reference)
 * 
 * */

$createOTP = OTP::create('phone number', 'email', 'reference');

// To retrieve the method return, call the following methods below.

/** 
 * 
 * The then method returns 2 parameters, $data of type AuthGuard, and $response of type Collection.
 * 
 * @method then(Closure $destination)
 * 
 * */
 
$createOTP->then(function (AuthGuard|null $data, Collection $response){
       
       /*
        * the $data parameter returns the null or Authguard model containing the table columns.
        */
       
       /*
        * The $response parameter returns a collection containing the following data: 
        * number_digits, code, message, status, user.access_token, user.ip_address
        */
        
        return $data->expires_at; // returns a value of type Carbon::class;
        
        if($response->status){
            return $response->message;
        }
       
});

// OR through the get() method that returns a Collection

return $createOTP->get();

// OR like this

return OTP::get();


// OR through the getResponse() method that returns a Collection

return $createOTP->getResponse();

// OR like this

return OTP::getResponse();


// OR can be called individually using methods

return OTP::getData()->expires_at; // returns a value of type Carbon::class;

if(OTP::getStatus()){
    return OTP::getMessage();
}

return OTP::getAccessToken();
return OTP::getIpAddress();


```

**Checking for the existence of generated code and viewing the generated data.**

```php
use Larakeeps\LaraDriven\Facades\OTP;



/** 
 * 
 * Method to check if the generated code exists.
 * $phone parameter is used for better code verification assertiveness.
 * 
 * @method static bool hasCode(string $code, string|null $phone)
 * 
 * */

$hasCode = OTP::hasCode('154896');

if($hasCode){
    return "The code exist."
}


/** 
 * 
 * Method for finding and returning data.
 * $phone parameter is used for better code verification assertiveness.
 * 
 * @method static OTP getByCode(string $code, string|null $phone)
 * 
 * */

$authGuardFounded = OTP::getByCode('154896', '5521985642205');

if($authGuardFounded){
    return $authGuardFounded
}


```

**Confirm whether the code entered is valid and whether it was actually confirmed.**

```php
use Larakeeps\LaraDriven\Facades\OTP;


/** 
 * 
 * The create method has 3 parameters, 2 mandatory and 1 optional.
 * 
 * The $reference parameter is used with the assertive condition function in code validation.
 * 
 * @param string $code : required
 * @param string $phone : required
 * @param string $reference : optional
 * 
 * @method static OTP confirm(string $code, string $phone, string|null $reference)
 * 
 * */
 
$validateCode = OTP::confirm('154896', '5521985642205'); 

// To retrieve the method return, call the following methods below.

/** 
 * 
 * The then method returns 2 parameters, $data of type AuthGuard, and $response of type Collection.
 * 
 * @method then(Closure $destination)
 * 
 * */
 
$validateCode->then(function (null $data, Collection $response){
       
       /*
        * 
        * Within the confirm() method, the $data parameter will always return a null value.
        * 
        * The $response parameter returns a collection containing the following data: 
        * number_digits, code, message, status, user.access_token, user.ip_address
        */
        
        if($response->status && OTP::isConfirmed()){
            return $response->message;
        }
        
        return $response->message;
       
});

// OR through the getResponse() method that returns a Collection

return $createOTP->getResponse();

// OR like this

return OTP::getResponse();

// OR can be called individually using methods

if(OTP::getStatus()){
    return OTP::getMessage();
}

return OTP::getAccessToken();
return OTP::getIpAddress();


```

**Deleting an OTP code.**

```php
use Larakeeps\LaraDriven\Facades\OTP;

/** 
 * 
 * @method OTP deleteCode(string $code)
 * 
 * */
 
$deletedCode = OTP::deleteCode();

// To retrieve the method return, call the following methods below.

/** 
 * 
 * The then method returns 2 parameters, $data of type AuthGuard, and $response of type Collection.
 * 
 * @method then(Closure $destination)
 * 
 * */
 
$validateCode->then(function (null $data, Collection $response){
       
       /*
        * 
        * Within the deleteCode() method, the $data parameter will always return a null value.
        * 
        * The $response parameter returns a collection containing the following data: 
        * number_digits, code, message, status, user.access_token, user.ip_address
        */
        
        if($response->status){
            return $response->message;
        }
       
});

// OR through the getResponse() method that returns a Collection

return $createOTP->getResponse();

// OR like this

return OTP::getResponse();

// OR can be called individually using methods

if(OTP::getStatus()){
    return OTP::getMessage();
}

return OTP::getAccessToken();
return OTP::getIpAddress();


```


#### Don't forget to follow me on github and star the project.

<br>

>### My contacts</kbd>
> >E-mail: douglassantos2127@gmail.com
> >
> >Linkedin: <a href='https://www.linkedin.com/in/douglas-da-silva-santos/' target='_blank'>Acessa Perfil</a>&nbsp;&nbsp;<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linkedin/linkedin-original.svg" width="24">

