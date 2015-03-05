'use strict';

// This util is assigned to the global 'dp' var
// in acceptance.js

var Util = {};

Util.enableDatabaseSet = function(dbset) {
  driver.get('http://localhost:8888/index.php?_sys=testmode&html&clean&setname=' + dbset).then(function() {
    expect(driver.findElement(by.id('status_code')).getText()).toContain('ok');
  });
};

Util.go = function(page) {
  driver.get(page);
  if (page.indexOf('/admin/')) {
    driver.get(page);
    driver.wait(function() {
      return driver.executeScript(function() {
        return window.DP_DONE_LOAD === true
          && window.DP_IS_BOOTED === true
          && window.DP_DIGEST_RUNNING === false
          && window.DP_AJAX_RUNNINGCOUNT === false;
      });
    });
  }
};

module.exports = Util;
