# Scripts to help with common tasks

Inspired by https://github.com/github/scripts-to-rule-them-all.


## First run

- Install [DDEV](https://ddev.com/get-started/)
- `./script/setup`


## Starting / stopping

- `./script/server`: start ddev to allow running examples, linting, testing
- `./script/stop`: stop ddev


## Update

Run after pulling/merging git with changes from others (e.g. `git pull` or `git checkout`).
This will updates composer packages.

- `./script/update`


## Lint / fix / test

- `./script/test`: run all tests


## Console

- `./script/console <command>`: run a command (e.g. `./script/console composer require ...`)
- `./script/console`: get a bash console


## Restart

- `./script/reset`: remove and setup Docker again
