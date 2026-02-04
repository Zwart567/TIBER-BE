# TIBER-BE Project
Berisikan Project Belum Final 
# Changes, bug and fixes:
- Ksatria : Created users and personalizations migrations
- Nasrul : Added 2 new tables and deleted laravel default tables
- Nasrul : Modified 2 tables to include user_id and diasabled $timestamp field list
- Nasrul : Added models for each tables, personalization, users_stats, medication_log are child tables to users
- Ksatria : Added Auth API Login and Logout
- Nasrul : Added Auth register API
- Nasrul : Added dashboard summary & confirm medication API
- Nasrul : Modified personalization table for last checkup and next checkup date to be initialized with register API
- Nasrul : fixed user stats table