# TIBER-BE Project
- Dev branch contains development demo that can run
- feat/"feature name" contains development features

## How To Run

### 0. Requirements
Make sure you have these things ready and working:
- PHP
- Laravel
- A database server or you can simply run it locally using XAMPP, laragon, etc.

### 1. Adding The .env File
Copy the existing .env.example file and modify it according to your needs
Here are the example

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiber
DB_USERNAME=root
DB_PASSWORD=
```

Save it and rename it to just .env

### 2. Generate The Autoload Files
To generate autoload files on terminal simply, go to the directory of this file is in and run

```bash
composer update
```

And wait until it is finished

### 3. Running The Project
Then finally run this to start

```bash
php artisan serve
```

## Changes, bug and fixes:
- Ksatria : Created users and personalizations migrations
- Ksatria : Install the sanctum component on Laravel via composer
- Nasrul : Added 2 new tables and deleted laravel default tables
- Nasrul : Modified 2 tables to include user_id and diasabled $timestamp field list
- Nasrul : Added models for each tables, personalization, users_stats, medication_log are child tables to users
- Ksatria : Added Auth API Login and Logout
- Nasrul : Added Auth register API
- Nasrul : Added dashboard summary & confirm medication API
- Nasrul : Modified personalization table for last checkup and next checkup date to be initialized with register API
- Nasrul : fixed user stats table
- Nasrul : Added Profile changing password and full name API
- Nasrul : Fixed API file structures & fixed bugs on Dashboard API
- Nasrul : Bug fixes on DashboardController (days_passed,last_checkup, next_checkup).
- Nasrul : Added Edge cases on confirm_medication (now only accept if it is on the same day)
- Nasrul : Changed naming scheme enum on personalization table migration
- Nasrul : Added new get personalization API
- Nasrul : Added new get activity overview  
- Ksatria : Added new get activity monthly calendar
- Ksatria : Fix code of model MedicationLogs.php
- Ksatria : Added expire token up to 1 hour
- Ksatria : Added error unauthorized if token is wrong or expired
- Nasrul : fixed personalization start_date field table
- Nasrul : fixed days_passed, now follows according to start_date
- Nasrul : fixed error 401 login message
- Nasrul : fixed get activity overview
- Nasrul : Added weekly history logged
- Ksatria : fix expire token up to 3 days
- Nasrul : Added weekly history logged
- Nasrul : fixed register user edge case enumeration time_category & control_freq_unit
- Nasrul : Temporary edge case on ActivityHistory if medication logs returns null (empty), it returns 404 error
- Nasrul : Added new table for new feature
- Nasrul : Added new feature Checkup Note Get, Put, Post
- Ksatria : Added update medication and checkup reminder (Personalization)
- Ksatria : Fix expire token up to 3 days
- Ksatria : Fix message error token in monthly calendar
- Nasrul : Fixed edge cases on UpdatePersonalization
- Nasrul : Added highest streak response on /api/activity/overview/
- Nasrul : Fixed activity overview, now show response even though data is empty on the database
- Ksatria : Fix update medication and checkup reminder (Personalization)
- Nasrul : Fixed activity History, WeeklyLogs part. Now showed response even when medication logs empty
- Nasrul : Added next_checkup_date on updatePersonalization
- Nasrul : checkup note now changed into just a regular note
