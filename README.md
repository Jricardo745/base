# base
Base repository to manage layer in Laravel

# To create files, on root from your project use

```
php freddiegar/base/src/Commands/create.php [NameModel] [aliasType] [columns,names?]
```

--By Example

```
php freddiegar/base/src/Commands/create.php User _ id,name,surname,email
```

This create in your project next files:

./app/Contracts/Repositories/UserRepository.php
./app/Entities/UserEntity.php
./app/Managers/UserManager.php
./app/Models/User.php
./app/Repositories/Eloquent/EloquentUserRepository.php

Also it will create folders if this not exists [Contracts,Entities,Managers,Models,Respositories]

If you wanna to create only one file in specific, you can use next types alias:

```
I        [interface]
E        [entity]
G        [manager]
M        [model]
R        [repository]
D        [datatable]
Q        [request]
C        [controller]
i        [_info]
c        [_create]
e        [_edit]
f        [_form]
x        [_index]
w        [_show]
a        [_actions]
l        [_lang]%  
```

All types that you can use are here, additional, this are shorcuts:

```
_ <= underscore       [IEGMR]
* <= asterisk         [IEGMRDQCicefxwal];
```

