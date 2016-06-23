Feature: Editing portal templates

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    
  Scenario: I get custom logo data
    Given I'm authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/templates"
    Then the response status code should be 200
    And the response should contain "Theme::layout.html.twig"

  Scenario: I get source of the Theme::layout.html.twig template
    Given I login with admin credentials from the login page
    When I send a GET request to "/portal/api/style/edit-theme-set/template-info?template=Theme::layout.html.twig"
    Then the response status code should be 200
    And the response should contain "% show section alerts %"

  Scenario: I modify Theme::layout.html.twig
    Given I'm authenticated as admin
    When I send a PUT request to "/portal/api/style/edit-theme-set/template-sources?template=Theme::layout.html.twig" with body:
    """
{
  "code": "Custom layout.html.twig"
}
    """
    Then the response status code should be 204

  Scenario: I retrieve modified Theme::layout.html.twig
    Given I'm authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/template-sources?template=Theme::layout.html.twig" with body:
    """
{
  "code": "Custom layout.html.twig content"
}
    """
    When I send a GET request to "/portal/api/style/edit-theme-set/template-info?template=Theme::layout.html.twig"
    Then the response status code should be 200
    And the response should contain "Custom layout.html.twig content"
