# TODO

## MUST DO FIRST
- [ ] Think what to do if the database has no users (first run of the app) - maybe a page with create admin user?.
- [ ] Implement a migration script to migrate the old csv to new database structure?.
- [ ] Specify what admin/editor/viewer can do.
- [ ] redirect from email verification must take the port from WEB_PORT (to avoid mismatch)
- [ ] think about avatars, if users are not being shared between each 'deploy', avatars shouldn't be?.
- [ ] check styles: actions a must be equally displayed, user-profile...
- [ ] security & design: saving the path in the database, null values...

## HIGH

- [x] Form to add user (user_create and form)
- [x] List users (user_index)
- [x] Delete users (user_delete)
- [x] Add see, edit and delete in list users.
- [x] Create user info (user_info)
- [x] Edit user info (user_edit)
- [x] Change how is saved the avatar in csv 
- [x] Check what is better: enums, classes or the constant defined in config.php
- [x] Fix media-queries
- [x] Check JS and how sidebbar is handled. Keep the state (collapsed or not)
- [x] Add error handling and exceptions
- [x] Check docker image (if it could be included or user must create it)
- [ ] Migrate to database.
- [ ] Add tests.
- [ ] Implement authentication.

## LOW

- [x] Add confirmation to sensible operations 
- [x] Validation to create and edit
- [x] See how to handle that each single user_* is opening the file. Maybe a util??
- [x] Handle file upload
- [x] Remove sidebar-toggle in mobile devices and add the style to mobile-toggle
- [x] Check if smoother animations are working on other browsers/devices
- [x] Check why sometimes it creates an Array in editing. Case => edited an user without avatar, added one, and a new user with Array in each field is created (did not check if it was in the csv). **RESOLVED: Was a false alarm - never actually occurred. Added defensive type checking in sanitization functions to prevent similar issues.**
- [x] Generate documentation.
- [x] Format correctly the documents.
- [x] Check deprecated.
- [x] Style input type file.
- [x] Add GitHub Icon in footer.
