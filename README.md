# Todo manager
Works on Symfony 3

## How to install
1. clone project
2. change connection to DB in config.yml
3. `bin/console doctrine:schema:create`
4. `bin/console doctrine:schema:update --force`
5. `npm install`
6. updating assets: `./node_modules/.bin/encore`
7. create user `bin/console fos:user:create`
8. promote user to super(ROLE_SUPER) `bin/console fos:user:promote`
