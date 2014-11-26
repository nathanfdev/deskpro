#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
DIR_VENDOR=$DIR_ROOT/app/vendor
DIR_WEB_VENDOR=$DIR_ROOT/web/vendor
DIR_WEB_BOWER=$DIR_ROOT/web/bower_components

########################################################################################################################
# PHP Vendors
########################################################################################################################

cd $DIR_VENDOR
find . -name .git -type d -exec rm -rf {} \;
find . -name .travis.yml -type f -exec rm -rf {} \;
find . -name phpunit.xml.dist -type f -exec rm -rf {} \;
find . -name composer.json -type f -exec rm -rf {} \;
find . -name composer.lock -type f -exec rm -rf {} \;
find . -name .gitignore -type f -exec rm -rf {} \;
find . -name .gitmodules -type f -exec rm -rf {} \;
find . -name .gitattributes -type f -exec rm -rf {} \;
find . -name build.xml -type f -exec rm -rf {} \;
find . -name CHANGELOG -type f -exec rm -rf {} \;
find . -name CHANGELOG.md -type f -exec rm -rf {} \;
find . -name README -type f -exec rm -rf {} \;
find . -name README.md -type f -exec rm -rf {} \;
find . -name README.markdown -type f -exec rm -rf {} \;
find . -name CONTRIBUTING.md -type f -exec rm -rf {} \;
find . -name build.properties -type f -exec rm -rf {} \;
find . -name build.properties.dev -type f -exec rm -rf {} \;
find . -name .coveralls.yml -type f -exec rm -rf {} \;
find . -name package.xml -type f -exec rm -rf {} \;
find . -name .scrutinizer.yml -type f -exec rm -rf {} \;
find . -name phpdoc.dist.xml -type f -exec rm -rf {} \;

cd $DIR_VENDOR/aws/aws-sdk-php
echo "Cleaning $(pwd)"
rm -rf build/ docs/ tests
rm -rf NOTICE.md phpunit.functional.xml.dist test_services.json.dist UPGRADING.md

cd $DIR_VENDOR/behat/mink
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf CHANGES.md phpdoc.ini.dist

cd $DIR_VENDOR/behat/mink-browserkit-driver
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/behat/mink-goutte-driver
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/behat/mink-selenium2-driver
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/codeception/codeception
echo "Cleaning $(pwd)"
rm -rf docs/ package/ tests/
rm -rf readme.md

cd $DIR_VENDOR/codeception/codeception
echo "Cleaning $(pwd)"
rm -rf docs/ package/ tests/
rm -rf readme.md

cd $DIR_VENDOR/doctrine/annotations
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/deskpro/deskpro-api-php
echo "Cleaning $(pwd)"
rm -rf examples/

cd $DIR_VENDOR/doctrine/cache
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/doctrine/collections
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/doctrine/common
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf UPGRADE_TO_2_1 UPGRADE_TO_2_2

cd $DIR_VENDOR/doctrine/data-fixtures
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf UPGRADE

cd $DIR_VENDOR/doctrine/dbal
echo "Cleaning $(pwd)"
rm -rf tests/ docs/
rm -rf UPGRADE

cd $DIR_VENDOR/doctrine/doctrine-bundle/Doctrine/Bundle/DoctrineBundle
echo "Cleaning $(pwd)"
rm -rf Tests
rm -rf Changelog.md

cd $DIR_VENDOR/doctrine/doctrine-fixtures-bundle/Doctrine/Bundle/FixturesBundle
echo "Cleaning $(pwd)"
rm -rf README.markdown

cd $DIR_VENDOR/doctrine/inflector
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/doctrine/migrations
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/doctrine/orm
echo "Cleaning $(pwd)"
rm -rf docs/ tests/
rm -rf README.markdown run-all.sh UPGRADE.md

cd $DIR_VENDOR/eluceo/ical
echo "Cleaning $(pwd)"
rm -rf examples/ tests/

cd $DIR_VENDOR/erusev/parsedown
echo "Cleaning $(pwd)"
rm -rf docs/ tests/ test/

cd $DIR_VENDOR/fabpot/goutte
echo "Cleaning $(pwd)"
rm -rf Goutte/Tests
rm -rf box.json README.rst

cd $DIR_VENDOR/facebook/php-sdk
echo "Cleaning $(pwd)"
rm -rf examples/ tests/
rm -rf changelog.md readme.md

cd $DIR_VENDOR/facebook/webdriver
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf example.php

cd $DIR_VENDOR/guzzle/guzzle
echo "Cleaning $(pwd)"
rm -rf tests/ phing/ docs/
rm -rf phar-stub.php UPGRADING.md

cd $DIR_VENDOR/imagine/imagine
echo "Cleaning $(pwd)"
rm -rf docs/ tests/
rm -rf Rakefile

cd $DIR_VENDOR/instaclick/php-webdriver
echo "Cleaning $(pwd)"
rm -rf doc/ test/
rm -rf README.rst

cd $DIR_VENDOR/jdorn/sql-formatter
echo "Cleaning $(pwd)"
rm -rf examples/ tests/
rm -rf .gitignore .travis.yml composer.json composer.lock phpunit.xml.dist README.md

cd $DIR_VENDOR/kriswallsmith/assetic
echo "Cleaning $(pwd)"
rm -rf tests/ docs/
rm -rf .gitattributes .gitignore .travis.yml CHANGELOG-1.0.md CHANGELOG-1.1.md composer.json Gemfile package.json phpunit.xml.dist README.md

cd $DIR_VENDOR/leth/ip-address
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/lightopenid/lightopenid
echo "Cleaning $(pwd)"
rm -rf provider/example-mysql.php provider/example.php example-google.php example.php

cd $DIR_VENDOR/mockery/mockery
echo "Cleaning $(pwd)"
rm -rf docs/ examples/ tests/

cd $DIR_VENDOR/monolog/monolog
echo "Cleaning $(pwd)"
rm -rf doc/ tests/
rm -rf CHANGELOG.mdown README.mdown

cd $DIR_VENDOR/pda/pheanstalk
echo "Cleaning $(pwd)"
rm -rf scripts/

cd $DIR_VENDOR/pdepend/pdepend
echo "Cleaning $(pwd)"
rm -rf scripts/ src/test
rm -rf .gitignore .gitmodules .travis.yml build.properties build.xml CHANGELOG composer.json pdepend.xml.dist phpunit.xml.dist

cd $DIR_VENDOR/phpmd/phpmd
echo "Cleaning $(pwd)"
rm -rf src/test
rm -rf .gitignore .gitmodules .travis.yml AUTHORS.rst build.properties build.xml CHANGELOG composer.json composer.lock phpunit.xml.dist README.rst

cd $DIR_VENDOR/phpunit/php-code-coverage
echo "Cleaning $(pwd)"
rm -rf build/ Tests/
rm -rf .gitattributes .gitignore .travis.yml build.xml composer.json CONTRIBUTING.md package.xml phpunit.xml.dist README.md

cd $DIR_VENDOR/phpunit/php-file-iterator
echo "Cleaning $(pwd)"
rm -rf build/
rm -rf .gitattributes .gitignore build.xml ChangeLog.markdown composer.json package.xml README.markdown

cd $DIR_VENDOR/phpunit/php-text-template
echo "Cleaning $(pwd)"
rm -rf build/
rm -rf .gitattributes .gitignore build.xml ChangeLog.md composer.json package.xml README.md

cd $DIR_VENDOR/phpunit/php-timer
echo "Cleaning $(pwd)"
rm -rf build/ Tests/
rm -rf .gitattributes .gitignore build.xml composer.json package.xml phpunit.xml.dist README.md

cd $DIR_VENDOR/phpunit/php-token-stream
echo "Cleaning $(pwd)"
rm -rf build/ Tests/
rm -rf .gitattributes .gitignore build.xml composer.json package.xml phpunit.xml.dist README.md

cd $DIR_VENDOR/phpunit/phpunit
echo "Cleaning $(pwd)"
rm -rf build/ Tests/
rm -rf .gitattributes .gitignore .travis.yml build.xml composer.json CONTRIBUTING.md package.xml phpdox.xml.dist phpunit.bat phpunit.xml.dist phpunit.xsd README.md

cd $DIR_VENDOR/phpunit/phpunit-mock-objects
echo "Cleaning $(pwd)"
rm -rf build/ Tests/
rm -rf .gitattributes .gitignore .travis.yml build.xml ChangeLog.markdown composer.json CONTRIBUTING.md package.xml phpunit.xml.dist

cd $DIR_VENDOR/psr/log/Psr
echo "Cleaning $(pwd)"
rm -rf .gitignore composer.json README.md

cd $DIR_VENDOR/satooshi/php-coveralls
echo "Cleaning $(pwd)"
rm -rf build/ tests/
rm -rf .gitignore .travis.yml build.xml CHANGELOG.md composer.json phpunit.xml.dist README.md travis.phpunit.xml

cd $DIR_VENDOR/squizlabs/php_codesniffer
echo "Cleaning $(pwd)"
rm -rf .gitattributes .gitignore composer.json README.markdown

cd $DIR_VENDOR/squizlabs/php_codesniffer
echo "Cleaning $(pwd)"
rm -rf .gitattributes .gitignore composer.json README.markdown

cd $DIR_VENDOR/swiftmailer/swiftmailer
echo "Cleaning $(pwd)"
rm -rf doc/ notes/ test-suite/ tests/
rm -rf .gitattributes .gitignore .travis.yml build.xml CHANGES composer.json create_pear_package.php package.xml.tpl README README.git VERSION

cd $DIR_VENDOR/symfony/icu/Symfony/Component/Icu
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/monolog-bundle/Symfony/Bundle/MonologBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore .travis.yml composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/monolog-bundle
echo "Cleaning $(pwd)"
rm -rf Tests/

cd $DIR_VENDOR/symfony/swiftmailer-bundle/Symfony/Bundle/SwiftmailerBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore .travis.yml composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony
echo "Cleaning $(pwd)"
rm -rf .editorconfig .gitignore .travis.yml autoload.php.dist CHANGELOG-2.2.md CHANGELOG-2.3.md CHANGELOG-2.4.md composer.json CONTRIBUTING.md CONTRIBUTORS.md phpunit.xml.dist README.md UPGRADE-2.1.md UPGRADE-2.2.md UPGRADE-2.3.md UPGRADE-2.4.md UPGRADE-3.0.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/Doctrine
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/Monolog
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/Propel1
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/ProxyManager
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/Swiftmailer
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bridge/Twig
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist README.md

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bundle/FrameworkBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bundle/FrameworkBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bundle/SecurityBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bundle/TwigBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle
echo "Cleaning $(pwd)"
rm -rf Tests/
rm -rf .gitignore CHANGELOG.md composer.json phpunit.xml.dist

cd $DIR_VENDOR/symfony/symfony/src/Symfony/Component
echo "Cleaning $(pwd)"
rm -rf */Tests
rm -rf */.gitignore */CHANGELOG.md */composer.json */composer.lock */phpunit.xml.dist */README.md

cd $DIR_VENDOR/tedivm/fetch
rm -rf tests/
rm -rf .coveralls.yml .gitignore .travis.yml composer.json CONTRIBUTING.md phpunit.xml.dist README.md

cd $DIR_VENDOR/twig/twig
echo "Cleaning $(pwd)"
rm -rf doc/ test/
rm -rf .editorconfig .gitignore .travis.yml CHANGELOG composer.json phpunit.xml.dist README.rst

cd $DIR_VENDOR/zendframework/zend-queue
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf .gitignore .travis.yml composer.json README.md

cd $DIR_VENDOR/zendframework/zendframework
echo "Cleaning $(pwd)"
rm -rf tests/
rm -rf CHANGELOG.md composer.json CONTRIBUTING.md INSTALL.md README.md README-GIT.md

cd $DIR_VENDOR/zendframework/zendoauth
echo "Cleaning $(pwd)"
rm -rf tests/

cd $DIR_VENDOR/friendsofsymfony/elastica-bundle
echo "Cleaning $(pwd)"
rm -rf Tests/ Resources/doc
rm -rf .travis.yml CHANGELOG-2.0.md CHANGELOG-2.1.md CHANGELOG-3.0.md composer.json LICENSE.txt phpunit.xml.dist README.md UPGRADE-3.0.md

cd $DIR_VENDOR/ruflin/elastica
echo "Cleaning $(pwd)"
rm -rf test/
rm -rf .coveralls.yml .gitignore .travis.yml build.xml changes.txt composer.json LICENSE.txt phpdoc.xml.dist README.markdown Vagrantfile

cd $DIR_VENDOR/zircote/swagger-php
echo "Cleaning $(pwd)"
rm -rf Examples/ tests/
rm -rf .travis.yml CHANGELOG.md composer.json package.xml phpunit.xml.dist readme.md swagger.phar VERSION

########################################################################################################################
# JS Vendors
########################################################################################################################

cd $DIR_WEB_BOWER
find . -name bower.json -type f -exec rm -rf {} \;
find . -name .bower.json -type f -exec rm -rf {} \;
find . -name package.json -type f -exec rm -rf {} \;
find . -name README.md -type f -exec rm -rf {} \;

cd $DIR_WEB_BOWER/ace-builds
rm -rf demo/ kitchen-sink/ src/ src-min/ src-noconflict/ textarea/
rm -rf ChangeLog.txt editor.html kitchen-sink-req.html kitchen-sink.html scrollable-page.html

cd $DIR_WEB_BOWER/angular
rm -rf angular-csp.css angular.min.js.gzip README.md

cd $DIR_WEB_BOWER/angular-grid
rm -rf config/ lib/ plugins/ scripts/ src/ test/ workbench/
rm -rf CHANGELOG.md conf.js GruntFile.js

cd $DIR_WEB_BOWER/angular-moment
rm -rf CHANGELOG.md Gruntfile.js karma.conf.js tests.js

cd $DIR_WEB_BOWER/angular-slider
rm -rf src/
rm -rf build.sh

cd $DIR_WEB_BOWER/angular-ui-router
rm -rf config/ lib/ release/doc sample/ src/ test/
rm -rf Gruntfile.js

cd $DIR_WEB_BOWER/angular-ui-select2
rm -rf docs/ test/
rm -rf CONTRIBUTING.md Gruntfile.js

cd $DIR_WEB_BOWER/angular-ui-sortable
rm -rf component.json

cd $DIR_WEB_BOWER/animate.css
rm -rf source/
rm -rf Gruntfile.js

cd $DIR_WEB_BOWER/bootstrap
rm -rf _includes/ _layouts/ dist/ docs-assets/ examples/ js/tests
rm -rf _config.yml about.html browserstack.json CNAME components.html composer.json CONTRIBUTING.md css.html customize.html DOCS-LICENSE getting-started.html Gruntfile.js index.html javascript.html

cd $DIR_WEB_BOWER/font-awesome
rm -rf scss/ src/
rm -rf _config.yml component.json composer.json CONTRIBUTING.md	Gemfile Gemfile.lock

cd $DIR_WEB_BOWER/jquery
rm -rf component.json composer.json jquery-migrate.js jquery-migrate.min.js

cd $DIR_WEB_BOWER/jquery-ui
rm -rf ui/*.js ui/i18n/
rm -rf AUTHORS.txt composer.json MANIFEST

mv ui/minified/jquery-ui.min.js /tmp/jquery-ui.min.js
mv ui/minified/i18n/jquery-ui-i18n.min.js /tmp/jquery-ui-i18n.min.js
rm -rf ui/minified/*.js
rm -rf ui/minified/i18n/*.js
mv /tmp/jquery-ui-i18n.min.js ui/minified/i18n/jquery-ui-i18n.min.js
mv /tmp/jquery-ui.min.js ui/minified/jquery-ui.min.js

cd $DIR_WEB_BOWER/jquery-ui/themes
rm -rf black-tie/ blitzer/ cupertino/ dark-hive/ dot-luv/ eggplant/ excite-bike/ flick/ hot-sneaks/ humanity/ le-frog/ mint-choc/ overcast/ pepper-grinder/ redmond/ smoothness/ south-street/ start/ sunny/ swanky-purse/ trontastic/ ui-darkness/ vader/

cd $DIR_WEB_BOWER/moment
rm -rf readme.md

cd $DIR_WEB_BOWER/requirejs
rm -rf dist/ docs/ tests/
rm -rf index.html package.json tasks.txt testBaseUrl.js updatesubs.sh

cd $DIR_WEB_BOWER/stacktrace
rm -rf gradle/ test
rm -rf build.gradle componen.json CONTRIBUTING.md gradlew gradlew.bat jshint.json stacktrace-bookmarklet.js

cd $DIR_WEB_BOWER/underscore
rm -rf docs/ test/
rm -rf CNAME CONTRIBUTING.md favicon.ico index.html Rakefile


