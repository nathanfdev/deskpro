Feature: PortalLogin
	In order to log in as a website user
	I need to have a valid username and password

	Scenario: Log in with an invalid username and password
		Given I am on "/login"
		 When I fill in "email" with "no_exist_user@example.com"
		  And I fill in "password" with "wrong_password"
		  And I press "Log In"
		 Then I should see "The email address or password you entered is invalid. Please try again."

	Scenario: Log in with a valid username and password
		Given I have a user "test_user@example.com" with password "password"
		Given I am on "/login"
		 When I fill in "email" with "test_user@example.com"
		  And I fill in "password" with "password"
		  And I press "Log In"
		 Then I should see "Edit your profile"