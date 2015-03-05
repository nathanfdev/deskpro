describe('admin homepage', function() {
  beforeEach(function() {
    dp.enableDatabaseSet('FreshDb');

    var LoginPage = require('../Common/Login/LoginPage.js');
    var login = new LoginPage('admin');

    login.get();
    login.loginAs('admin@example.com', 'pass');
  });

  it('should see getting help form', function() {
    dp.go('http://localhost:8888/admin/');
    //expect(element.all(by.css('h3')).getText()).toContain('Getting Help');
  });
});