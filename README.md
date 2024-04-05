## Projectdescription

This is an ongoing project for my portfolio, to show my backend skills in creating a PHP server connected to a MySQL-database, in a local Docker environment. For this project, I am currently building an Hp (Harry Potter) database, where future users will be able to create *CRUD* operations
on characters from the Hp-series.

Furthermore, I will add authentication, so that users can register, login and save their favourite characters in a list.
Stay tuned, as this is an ongoing project!

#### Tools and language
- PHP, with PDO
- MySQL(MariaDb)
- Docker


#### Setup instructions

This is a local environment used with Docker desktop, with settings in the Dockerfile and docker-compose.yml.
Docker desktop, Composer and the PHP instance is needed.

Run de container in Docker using `docker-compose up`
Navigate to `localhost:8080` to login to adminer. The database and tables should be set up correctly now.
Navigate to `localhost/hp.php`
Register or login and enjoy!