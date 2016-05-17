# todo TicketDepartments controller was refactored,
# todo need to update cache service to use in crud controller and then enable test.
#

Feature: Api Caching
  This is feature to have some perfomance increase

  Background:
    Given I install the "api" data set
      And the setting "response.cache.enabled" is set to 1
      And my request is authenticated

  Scenario: I'm getting response without cache feature
    Given the setting "response.cache.enabled" is set to 0
    When I send a GET request to "/api/v2/departments"
    Then the response status code should be 200
#    And the header "X-DeskPRO-Cache" should not exist
#    And the header "X-DeskPRO-Cache-Store" should not exist
#
#  Scenario: I'm getting response with cache feature and response is stored
#    When I send a GET request to "/api/v2/ticket_departments"
#    Then the response status code should be 200
#    And the header "X-DeskPRO-Cache" should be equal to "store"
#    And the header "X-DeskPRO-Cache-Store" should not exist
#
#  Scenario: I'm getting response with cache feature and I was hit the cache
#    When I send a GET request to "/api/v2/ticket_departments"
#    Then the response status code should be 200
#    And the header "X-DeskPRO-Cache" should be equal to "hit"
#    And the header "X-DeskPRO-Cache-Store" should not exist
#
#  Scenario: I'm invalidating cache be performing update action
#    When I send a PUT request to "/api/v2/ticket_departments/1" with body:
#    """
#    {"name": "test_change"}
#    """
#    Then the response status code should be 500
#    When I send a GET request to "/api/v2/ticket_departments"
#    Then the response status code should be 200
#    And the header "X-DeskPRO-Cache" should be equal to "store"
#    And the header "X-DeskPRO-Cache-Store" should not exist