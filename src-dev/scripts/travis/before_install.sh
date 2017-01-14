#!/usr/bin/env bash

if [[ "${TRAVIS_OS_NAME}" == 'osx' ]]; then
    echo "Here's the OSX environment:"
    git --version
    sw_vers
    brew --version

    echo 'Updating brew...'
    brew update

    echo 'Updating git...'
    src-dev/scripts/osx.homebrew-install.sh 'git'
    export PATH="/usr/local/bin:$PATH"
    echo 'export PATH="/usr/local/bin:$PATH"' >> ~/.bash_profile
    git --version

    if [[ "${BREW_PHP}" == 'hhvm' ]]; then
        echo 'Adding brew HHVM dependencies...'
        brew tap hhvm/hhvm
    else
        echo 'Adding brew PHP dependencies...'
        brew tap homebrew/dupes
        brew tap homebrew/versions
        brew tap homebrew/homebrew-php

        src-dev/scripts/osx.homebrew-install.sh "${BREW_PHP}"
        src-dev/scripts/osx.homebrew-install.sh "${BREW_PHP}-xdebug"
    fi

    test -d "$HOME/bin" || mkdir "$HOME/bin"
    export PATH="$HOME/bin:$PATH"

    curl -s 'http://getcomposer.org/installer' | php
    ln -s "$(pwd)/composer.phar" "$HOME/bin/composer"
fi
