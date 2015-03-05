'use strict';

var LoginPage = function(type) {
  this.getEmailInput   = function() { return driver.findElement(by.id('email')); }
  this.getPassInput    = function() { return driver.findElement(by.id('password')); }
  this.getSubmitButton = function() { return driver.findElement(by.css('form [type="submit"]')); };

  this.get = function() {
    if (type == 'admin') {
      driver.get('http://localhost:8888/agent/login?_testlogin&return=/admin/');
    } else if (type == 'agent') {
      driver.get('http://localhost:8888/agent/login?_testlogin');
    } else if (type == 'user') {
      driver.get('http://localhost:8888/login?_testlogin')
    }
  };

  this.loginAs = function(email, password) {
    this.getEmailInput().sendKeys(email);
    this.getPassInput().sendKeys(password);
    this.getSubmitButton().click();

    driver.wait(function() {
      return driver.getCurrentUrl().then(function(url) {
        return !/_testlogin/.test(url);
      });
    });

    expect(element(driver.isElementPresent(by.css('form .error')))).toBeFalsy();
  };
};

module.exports = LoginPage;