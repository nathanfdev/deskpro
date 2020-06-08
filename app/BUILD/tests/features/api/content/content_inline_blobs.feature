@new
Feature: Content inline blobs

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And there are no Blob records in the DB
    And I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"

  Scenario: I add an article with inline blobs
    Given the following ArticleCategory records exist:
      | #   | parent | is_agent | is_book | template_suffix | title          | slug           | display_order | depth | root |
      | ac1 |        | 1        | 0       | 0               | First Category | first_category | 0             | 0     | ac1  |
    When I send a POST request to "/api/v2/articles" with body:
    """
{
  "title": "Test Article",
  "content": "<p>Some fake article content <img class=\"dp-embed-blob-img-AAAAAAAAAAAAAAAAAA\" src=\"url\" /></p>",
  "person":  ~admin~,
  "status": "hidden.draft",
  "content_input_type": "rte",
  "categories": [~ac1~]
}
    """
    Then the response status code should be 201
    And blob AAAAAAAAAAAAAAAAAA should not be temp
    And blob BBBBBBBBBBBBBBBBBB should be temp

  Scenario: I add a news post with inline blobs
    When I send a POST request to "/api/v2/news" with body:
    """
{
  "title": "Test Article",
  "content": "<p>Some fake news post content <img class=\"dp-embed-blob-img-AAAAAAAAAAAAAAAAAA\" src=\"url\" /></p>",
  "person":  ~admin~,
  "status": "hidden.draft",
  "content_input_type": "rte"
}
    """
    Then the response status code should be 201
    And blob AAAAAAAAAAAAAAAAAA should not be temp
    And blob BBBBBBBBBBBBBBBBBB should be temp

  Scenario: I add a download with inline blobs
    When I send a POST request to "/api/v2/downloads" with body:
    """
{
  "title": "Test Article",
  "content": "<p>Some fake download content <img class=\"dp-embed-blob-img-AAAAAAAAAAAAAAAAAA\" src=\"url\" /></p>",
  "person":  ~admin~,
  "status": "hidden.draft",
  "content_input_type": "rte"
}
    """
    Then the response status code should be 201
    And blob AAAAAAAAAAAAAAAAAA should not be temp
    And blob BBBBBBBBBBBBBBBBBB should be temp
