# System
alias ll='ls -alF';
alias la='ls -A';
alias l='ls -aCF';
alias h='history $@';
alias c='clear';
alias ..='cd ..';
alias ~='cd ~';

# Artisan
alias pa='docker compose exec php php artisan $@';
alias parl='docker compose exec php php artisan route:list $@';
alias parlg='docker compose exec php php artisan route:list | grep $@';
alias parc='docker compose exec php php artisan route:clear';
alias pam='docker compose exec php php artisan migrate $@';
alias pamds='docker compose exec php php artisan migrate db:seed';
alias pamr='docker compose exec php php artisan migrate:rollback $@';
alias pavp='docker compose exec php php artisan vendor:publish $@';

# Symfony
alias pbc='php bin/console $@'; 

# PHP
alias p='docker compose exec php $@';

# Composer
alias ci='docker compose exec php composer install $@';
alias cind='docker compose exec php composer install --no-dev $@';
alias crq='docker compose exec php composer require $@';
alias crqd='docker compose exec php composer require-dev $@';
alias cu='docker compose exec php composer update $@';
alias crm='docker compose exec php composer remove $@';
alias crmd='docker compose exec php composer remove --dev $@';
alias cdu='docker compose exec php composer dump-autoload';

# Git
alias gst="git status"
alias gb="git branch"
alias gc="git checkout"
alias gl="git log --oneline --decorate --color"
alias amend="git add . && git commit --amend --no-edit"
alias commit="git add . && git commit -m"
alias diff="git diff"
alias force="git push --force"
alias nuke="git clean -df && git reset --hard"
alias pop="git stash pop"
alias pull="git pull"
alias push="git push"
alias resolve="git add . && git commit --no-edit"
alias stash="git stash -u"
alias unstage="git restore --staged ."
alias wip="commit wip"


