#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
DIR_VENDOR=$DIR_ROOT/app/vendor
DIR_WEB_BOWER=$DIR_ROOT/web/bower_components
DIR_WEB_NODE=$DIR_ROOT/web/node_modules

########################################################################################################################
# PHP Vendors
########################################################################################################################

cd $DIR_VENDOR

find . -type d \( \
    -name ".git" \
    -o -iname "docs" \
    -o -iname "doc" \
    -o -iname "examples" \
    -o -iname "Tests" \
\) -exec rm -rfv {} \;

find . -type f \( \
    -name ".gitignore" \
    -o -name ".gitmodules" \
    -o -name ".gitattributes" \
    -o -name ".gitkeep" \
    -o -name ".editorconfig" \
    -o -name ".travis.yml" \
    -o -name ".coveralls.yml" \
    -o -name ".scrutinizer.yml" \
    -o -name "composer.json" \
    -o -name "composer.lock" \
    -o -name "build.xml" \
    -o -name "build.properties" \
    -o -name "build.properties.dev" \
    -o -name "package.xml" \
    -o -name "phpdoc.dist.xml" \
    -o -name "phpdoc.ini.dist" \
    -o -name "phpunit.functional.xml.dist" \
    -o -name "phpunit.xsd" \
    -o -name "phpunit.bat" \
    -o -name "phpunit.xml.dist" \
    -o -iname "CHANGELOG" \
    -o -iname "CHANGELOG.md" \
    -o -iname "CHANGELOG.mdown" \
    -o -iname "CHANGES.md" \
    -o -iname "CONTRIBUTING" \
    -o -iname "CONTRIBUTING.md" \
    -o -iname "NOTICE.md" \
    -o -iname "README" \
    -o -iname "README.md" \
    -o -iname "README.mdown" \
    -o -iname "README.txt" \
    -o -iname "README.rst" \
    -o -iname "README.markdown" \
    -o -iname "UPGRADE" \
    -o -iname "UPGRADING" \
    -o -iname "UPGRADING.md" \
    -o -iname "UPGRADING.md" \
    -o -iname "Rakefile" \
    -o -iname "Gemfile" \
    -o -iname "Vagrantfile" \
\) -exec rm -rfvv {} \;

cd $DIR_VENDOR/aws/aws-sdk-php
rm -rfv build/
rm -rfv test_services.json.dist

cd $DIR_VENDOR/codeception/codeception
rm -rfv package/

cd $DIR_VENDOR/codeception/codeception
rm -rfv package/

cd $DIR_VENDOR/doctrine/common
rm -rfv UPGRADE_TO_2_1 UPGRADE_TO_2_2

cd $DIR_VENDOR/doctrine/orm
rm -rfv run-all.sh

cd $DIR_VENDOR/fabpot/goutte
rm -rfv box.json

cd $DIR_VENDOR/facebook/webdriver
rm -rfv example.php

cd $DIR_VENDOR/guzzle/guzzle
rm -rfv phing/
rm -rfv phar-stub.php

cd $DIR_VENDOR/kriswallsmith/assetic
rm -rfv CHANGELOG-1.0.md CHANGELOG-1.1.md

cd $DIR_VENDOR/lightopenid/lightopenid
rm -rfv provider/example-mysql.php provider/example.php example-google.php example.php

cd $DIR_VENDOR/pda/pheanstalk
rm -rfv scripts/

cd $DIR_VENDOR/pdepend/pdepend
rm -rfv scripts/

cd $DIR_VENDOR/phpmd/phpmd
rm -rfv AUTHORS.rst

cd $DIR_VENDOR/phpunit/php-code-coverage
rm -rfv build/

cd $DIR_VENDOR/phpunit/php-file-iterator
rm -rfv build/

cd $DIR_VENDOR/phpunit/php-text-template
rm -rfv build/

cd $DIR_VENDOR/phpunit/php-timer
rm -rfv build/

cd $DIR_VENDOR/phpunit/php-token-stream
rm -rfv build/

cd $DIR_VENDOR/phpunit/phpunit
rm -rfv build/

cd $DIR_VENDOR/phpunit/phpunit-mock-objects
rm -rfv build/

cd $DIR_VENDOR/satooshi/php-coveralls
rm -rfv build/

cd $DIR_VENDOR/swiftmailer/swiftmailer
rm -rfv notes/ test-suite/
rm -rfv create_pear_package.php package.xml.tpl README.git

cd $DIR_VENDOR/symfony/symfony
rm -rfv autoload.php.dist CHANGELOG-2.2.md CHANGELOG-2.3.md CHANGELOG-2.4.md CONTRIBUTING.md CONTRIBUTORS.md UPGRADE-2.1.md UPGRADE-2.2.md UPGRADE-2.3.md UPGRADE-2.4.md UPGRADE-3.0.md

cd $DIR_VENDOR/zendframework/zendframework
rm -rfv README-GIT.md

cd $DIR_VENDOR/friendsofsymfony/elastica-bundle
rm -rfv Resources/doc
rm -rfv CHANGELOG-2.0.md CHANGELOG-2.1.md CHANGELOG-3.0.md UPGRADE-3.0.md

cd $DIR_VENDOR/zircote/swagger-php
rm -rfv swagger.phar

########################################################################################################################
# JS Vendors
########################################################################################################################

cd $DIR_WEB_BOWER
find . -type d \( \
    -name ".git" \
    -o -iname "docs" \
\) -exec rm -rfv {} \;

find . -type f \( \
    -name "bower.json" \
    -o -name ".bower.json" \
    -o -name ".editorconfig" \
    -o -name ".gitignore" \
    -o -name ".jshintrc" \
    -o -name "jshint.json" \
    -o -name ".travis.yml" \
    -o -name ".npmignore" \
    -o -name "tests.js" \
    -o -name "package.json" \
    -o -name "component.json" \
    -o -name "composer.json" \
    -o -iname "README.md" \
    -o -iname "CHANGELOG.md" \
    -o -iname "CHANGELOG.txt" \
    -o -iname "CONTRIBUTING.md" \
    -o -iname "AUTHORS.txt" \
    -o -name "Gruntfile.md" \
    -o -name "Gruntfile.js" \
    -o -name "npm-debug.log" \
    -o -name "karma.conf.js" \
    -o -name "Gemfile" \
    -o -name "Gemfile.lock" \
    -o -name "Rakefile" \
    -o -name "build.gradle" \
    -o -name "gradlew" \
    -o -name "*.php" \
    -o -name "*.sh" \
    -o -name "*.bat" \
    -o -name "CNAME" \
    -o -name "components.html" \
\) -exec rm -rfv {} \;

cd $DIR_WEB_BOWER/ace-builds
rm -rfv demo/ kitchen-sink/ src/ src-min/ src-noconflict/ textarea/
rm -rfv editor.html kitchen-sink-req.html kitchen-sink.html scrollable-page.html

cd $DIR_WEB_BOWER/angular
rm -rfv angular-csp.css angular.min.js.gzip README.md

cd $DIR_WEB_BOWER/angular-grid
rm -rfv config/ lib/ plugins/ scripts/ src/ test/ workbench/
rm -rfv conf.js

cd $DIR_WEB_BOWER/angular-slider
rm -rfv src/

cd $DIR_WEB_BOWER/angular-ui-router
rm -rfv config/ lib/ release/doc sample/ src/ test/

cd $DIR_WEB_BOWER/angular-ui-select2
rm -rfv docs/ test/

cd $DIR_WEB_BOWER/animate.css
rm -rfv source/

cd $DIR_WEB_BOWER/bootstrap
rm -rfv _includes/ _layouts/ dist/ docs-assets/ examples/
rm -rfv _config.yml about.html browserstack.json css.html customize.html DOCS-LICENSE getting-started.html index.html javascript.html

cd $DIR_WEB_BOWER/font-awesome
rm -rfv scss/ src/
rm -rfv _config.yml

cd $DIR_WEB_BOWER/jquery
rm -rfv jquery-migrate.js jquery-migrate.min.js

cd $DIR_WEB_BOWER/jquery-ui
mv ui/minified/jquery-ui.min.js /tmp/jquery-ui.min.js
mv ui/minified/i18n/jquery-ui-i18n.min.js /tmp/jquery-ui-i18n.min.js
rm -rfv ui/minified/*.js
rm -rfv ui/minified/i18n/*.js
rm -rfv ui/*.js
rm -rfv ui/i18n
mv /tmp/jquery-ui-i18n.min.js ui/minified/i18n/jquery-ui-i18n.min.js
mv /tmp/jquery-ui.min.js ui/minified/jquery-ui.min.js

cd $DIR_WEB_BOWER/jquery-ui/themes
rm -rfv black-tie/ blitzer/ cupertino/ dark-hive/ dot-luv/ eggplant/ excite-bike/ flick/ hot-sneaks/ humanity/ le-frog/ mint-choc/ overcast/ pepper-grinder/ redmond/ smoothness/ south-street/ start/ sunny/ swanky-purse/ trontastic/ ui-darkness/ vader/

cd $DIR_WEB_BOWER/requirejs
rm -rfv dist/
rm -rfv index.html tasks.txt testBaseUrl.js

cd $DIR_WEB_BOWER/stacktrace
rm -rfv gradle/ test
rm -rfv stacktrace-bookmarklet.js

cd $DIR_WEB_BOWER/underscore
rm -rfv docs/ test/
rm -rfv favicon.ico index.html

cd $DIR_WEB_BOWER/ng-clip
rm -rfv example

########################################################################################################################
# node
########################################################################################################################

# distro doesnt need any node modules
rm -rfv $DIR_WEB_NODE/