Feature: Admin Departments List
	In order to manage departments
	As an administrator
	I need to log in and see the existing departments

	@javascript
	Scenario: Loading departments
		Given I am logged in as admin "admin@example.com"
		Given I am on "/admin/#/tickets/ticket_deps"
		When admin page is loaded
		When admin sections are loaded
		Then I should see "2 Departments"