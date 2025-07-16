# Lyricist (Backend)

## Contents
- [Lyricist (Backend)](#lyricist-backend)
  - [Contents](#contents)
  - [Tools](#tools)
    - [Built With](#built-with)
  - [Imeplementation](#imeplementation)
    - [Modules](#modules)
      - [Login](#login)
      - [Profile](#profile)
      - [Access Control](#access-control)
        - [User](#user)
        - [Roles](#roles)
          - [Super Admin Role:](#super-admin-role)
          - [Users Role:](#users-role)
        - [Permissions](#permissions)
  - [Deployment](#deployment)
    - [Public domains](#public-domains)
    - [Version check](#version-check)
    - [For local Development](#for-local-development)
    - [Cron Scheduler](#cron-scheduler)
    - [important](#important)
    - [Database](#database)
      - [Database change](#database-change)
    - [Log Viewer](#log-viewer)
    - [Docker](#docker)
      - [Docker:](#docker-1)
      - [Network : External Network](#network--external-network)
      - [Access container to container](#access-container-to-container)
      - [Command: docker](#command-docker)
      - [Command: docker  : Services](#command-docker---services)
      - [Browser:](#browser)
      - [Workbench:](#workbench)
  - [Improvements](#improvements)
    - [Issue](#issue)
  - [GIT commit types](#git-commit-types)
  - [Documentation](#documentation)
  - [Project details](#project-details)
    - [Service](#service)
    - [Hotkey](#hotkey)
    - [Sub Hotkey](#sub-hotkey)
- [](#)



## Tools

### Built With

<!-- What things you need to install the software and how to install them -->

| Build With                | Version | Server |
| ------------------------- | ------- | ------ |
| php                       | 7.4.29  |        |
| Composer                  |         |        |
| MySQL                     | 5.1     |        |
| Laravel                   | 8.83.27 |        |
| passport                  |         |        |
| spatie/laravel-permission |         |        |
| logviwer                  |         |        |


## Imeplementation

This projects has been divided into several modules to make the development of the projectis much more easier.  Such are -

### Modules

| Modules               |
| --------------------- |
| ⛨⛨ **Admin Panel** ⛨⛨ |
| Login                 |
| Profile               |
| 🛂 **Super Admin**     |
| Users                 |
| Roles                 |
| Permissions           |
| 🗐  **Dashboard**      |
| Dashboard             |
| 🗐  **Reports**        |
| Audit Log             |
| Usage Report          |


#### Login

**Token Accept Ways**

1. Header Authorization Bearer
   1. Authorization = Bearer token
2. Query params
   - **URL**
       - www.abc.com/api/v1/user?access_token=token
       - here,  access_token will be set to header automatically,
         - Authorization = Bearer access_token


#### Profile

- Update user own information

- search user own permission


#### Access Control

##### User

- user list
- user create
  - add role
- user update
  - manage role

##### Roles

- role list
- role create
  - add permissions
- role update
  - manage permissions

###### Super Admin Role:
- Can create access for the Service OPS users.
- Access will be created as preset i.e DB Web Level 1, DB Push Level 2 etc.
- While creating presets the super admin can set preset wise DB, Table, Column and row limit.
- DB Query: This is the main module of this phase. In this module the user can perform a search in the Database without accessing it. And the user will get only that data which h/she has permission. No download can't be performed.
- Only Super Admin and management can view the Audit log. this audit log is for tracking the user's journey to through the panel. His/her every movement is recorded here.

###### Users Role:
1.	Can see his/her access level.
2.	DB Query: This is the main module of this phase. In this module the user can perform a search in the Database without accessing it. And the user will get only that data which h/she has permission. No download can't be performed.

##### Permissions

- permission list
- permission create
- permission update
- role list
- role create
- role update
- user list
- user create
- user update

## Deployment

### Public domains



### Version check

```
php .\artisan --version
```


### For local Development

```
    git clone repo

    composer install
    or
    composer install  --ignore-platform-reqs
    or
    composer install --no-cache --ignore-platform-reqs
    or
    composer install --optimize-autoloader --dev

    cp .env.example .env   
    change content of .env file


============passport==========
    php artisan key:generate  
    or
    php artisan passport:install
    php artisan passport:keys 
============passport==========

    php artisan serve 
    or
    php artisan serve --port=8001


    composer dump-autoload

    ==========Cache clear===============
    php artisan cache:clear
    php artisan route:cache
    php artisan config:cache
    php artisan view:clear
    php artisan optimize
    php artisan config:clear
    php artisan route:clear
    composer dump-autoload
    php artisan log:clear
    yes

    php artisan serve --port=8001
    ==========Cache clear===============


php.ini size check
php -i | findstr /C:"upload_max_filesize" /C:"post_max_size"

```

### Cron Scheduler
```
every minute
/usr/local/bin/php /home/winedsco/ppsapi.wineds.com/artisan schedule:run
```

### important
```
for file system always use like this

/var/www

    not

/var/www/
```

### Database 

#### Database change 
```
ALTER TABLE roles DROP COLUMN id 
ALTER TABLE roles ADD id INT IDENTITY(1,1)


ALTER TABLE users
ADD CONSTRAINT PK_users_id PRIMARY KEY (id);




-- Disable foreign key constraints referencing the 'id' column in the 'roles' table
EXEC sp_MSforeachtable 'ALTER TABLE ? NOCHECK CONSTRAINT ALL';

ALTER TABLE users DROP CONSTRAINT PK_users_id;
ALTER TABLE users DROP COLUMN id ;
ALTER TABLE users ADD id INT IDENTITY(1,1);

ALTER TABLE roles DROP CONSTRAINT PK_roles_id;
ALTER TABLE roles DROP COLUMN id ;
ALTER TABLE roles ADD id INT IDENTITY(1,1);

ALTER TABLE permissions DROP CONSTRAINT PK_permissions_id;
ALTER TABLE permissions DROP COLUMN id ;
ALTER TABLE permissions ADD id INT IDENTITY(1,1);

-- Re-enable foreign key constraints
EXEC sp_MSforeachtable 'ALTER TABLE ? WITH CHECK CHECK CONSTRAINT ALL';
```

### Log Viewer
* http://localhost:801/log_cmp_(today date y-m-d)
  * http://localhost:801/log_cmp_2022-06-09
* 

### Docker
#### Docker: 

#### Network : External Network
```
sudo docker network create lyricist_wintel_network
sudo docker network create local_central_db_network

docker exec -it lyricist-admin-api-container sh
```

#### Access container to container
```
docker exec ms-rmq-container ping lyricist-admin-api-container
docker exec ms-rmq-container ping cmp-db-container

docker exec lyricist-admin-api-container ping ms-rmq-container 
docker exec lyricist-admin-api-container telnet ms-rmq-container 5673
docker exec lyricist-admin-api-container ping cmp-db-container 

docker exec cmp-db-container ping ms-rmq-container
docker exec cmp-db-container ping lyricist-admin-api-container
```




#### Command: docker 
```
sudo docker-compose down
sudo docker-compose build && docker-compose up -d
```

#### Command: docker  : Services
```
docker-compose exec export sh
```

#### Browser: 
- browser: http://localhost:801

#### Workbench:
```
host: 127.0.0.1 (System/LocalHost port)
port: 4310 (System/LocalHost PC Port)
username: root
pass: root
``` 

## Improvements


### Issue 

```
file data reading only now file_path
but in real scenario we have to pass other data like server ip, 
```





## GIT commit types
```
feat: New feature for the user.
fix: Bug fix.
style: Code Style Changes.
refactor: Code Refactoring.
build: Build System Changes.
ci: Continuous Integration Changes.
perf: Performance Improvements.
revert: Revert a Previous Commit.
docs: Documentation changes.
test: Adding or modifying tests.
chore: Routine tasks, maintenance, or housekeeping.
```



## Documentation
- https://github.com/alexandr-mironov/php-smpp


## Project details

### Service
### Hotkey
### Sub Hotkey


http://esoftbd.org/rpt/rptan.aspx?item=embeded_details&startdate=01/01/2024&enddate=12/10/2024



# 
```
TRUNCATE events;
TRUNCATE home_main_slider;
TRUNCATE members;
TRUNCATE success_stories;
TRUNCATE product;
```
