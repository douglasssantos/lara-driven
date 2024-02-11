**Lara-Driven**
> Lara-Driven is a package designed to create and organize layers using the Domain Drive Design methodology.

### This repository is only compatible with laravel: `7.*` to `11.*`


## Installation


First Step, execute the command.

```shell script
composer require larakeeps/lara-driven
```

lara-drive command has an argument called domain, where you can directly specify the name of the domain to be created.

```shell script
php artisan lara-make:driven {domain?}
```

To create a domain with the help of Lara-Driven, run the command below.

```shell script
php artisan lara-make:driven
```

After executing the command, simply pass the information requested by Lara-Driven into the terminal. Below is an example of information.

```shell script

Enter your domain name:
 > Company

 Do you want to keep the domain name as the folder name? [Company] (yes/no) [yes]:
 > 

 Do you want to create a [Model] for the domain? (yes/no) [yes]:
 > 

 Do you want to create [Migration], [Seed] or [Factory]? (yes/no) [yes]:
 > 

 Select one or more classes to manipulate your database. [Migration]:
  [0] Migration
  [1] Seed
  [2] Factory
  [3] All
 > 3

 Do you want to create a [Policy] for your model? (yes/no) [no]:
 > y

 Do you want to create an empty [Service]? (yes/no) [no]:
 > 

 Do you want to create an [Interface] for your service? (yes/no) [no]:
 > y

 Do you want to create the [Repository] to separate model actions from your service? (yes/no) [yes]:
 >

 Do you want to create an [Interface] for your repository? (yes/no) [no]:
 > y

 Do you want to create a [Controller] for your domain? (yes/no) [yes]:
 >

 Do you want to create a [Request] for processing and validation of your controller? (yes/no) [yes]:
 >

 Do you want to install [Routes] on your domain? (yes/no) [yes]:
 >

 Which routes do you want to install? [Web]:
  [0] Web
  [1] Api
  [2] Both
 > 2

 Do you want to assign the routes to the [Controller]? (yes/no) [yes]:
 > y

 Do you want to add [Middleware] to your routes? (yes/no) [yes]:
 > y
 
  INFO  Clearing cached bootstrap files.

  events .................................................................................................................................. 1ms DONE
  views ................................................................................................................................... 4ms DONE
  cache ................................................................................................................................... 2ms DONE
  route ................................................................................................................................... 2ms DONE
  config .................................................................................................................................. 1ms DONE
  compiled ................................................................................................................................ 1ms DONE


```


#### Don't forget to follow me on github and star the project.

<br>

>### My contacts</kbd>
> >E-mail: douglassantos2127@gmail.com
> >
> >Linkedin: <a href='https://www.linkedin.com/in/douglas-da-silva-santos/' target='_blank'>Acessa Perfil</a>&nbsp;&nbsp;<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linkedin/linkedin-original.svg" width="24">

