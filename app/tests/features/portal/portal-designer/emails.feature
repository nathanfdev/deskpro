Feature: Search users by emails
  To preview portal changes on behalf of any user
  As a DeskPRO admin
  I want to search people by their emails

  Scenario: I search for users
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/emails?target=user&term=a"
    Then the response status code should be 200

  Scenario: I search for agents
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/emails?target=agent&term=a"
    Then the response status code should be 200

  # ToDo: fix portal controllers getUser() after "I am authenticated as admin"

#  Scenario: I retrieve my email
#    Given I am authenticated as admin
#    When I send a GET request to "/portal/api/me/email"
#    Then the response status code should be 200
