Feature: JSONP
  In order to use the API responses in my javascript
  As a developer
  I need to be able to recieve a JSONP callback

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I add "callback" to the query string and get JSONP
    When I send a GET request to "/api/v2/ticket_layouts/agent?callback=my_function"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/javascript"
    And the response should contain "/**/my_function("

  Scenario: Invalid callbacks result in an error
    When I send a GET request to "/api/v2/ticket_layouts/agent?callback=function"
    Then the response status code should be 400