@new
Feature: Content categories

  Background:
    Given I'm authenticated as admin
    And no Phrase records exist
    And only the following Language records exist:
      | #  | Sys Name |
      | l1 | Lang 1   |
      | l2 | Lang 2   |
      | l3 | Lang 3   |

  Scenario Outline: I set translations
    Given the following <entity> records exist:
      | #  | title    | slug     |
      | c1 | Category | category |

    When I send a PUT request to "/api/v2/<endpoint>/{c1}" with body:
    """
{
  "title_translations": [
    {
      "language": ~l1~,
      "value": "title lang 1"
    },
    {
      "language": ~l2~,
      "value": "title lang 2"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<endpoint>/{c1}"
    Then the JSON node "data.title_translations" should have 3 elements
    And the JSON node "data.title_translations[0].language" should be equal to "{l1}"
    And the JSON node "data.title_translations[0].value" should be equal to "title lang 1"
    And the JSON node "data.title_translations[1].language" should be equal to "{l2}"
    And the JSON node "data.title_translations[1].value" should be equal to "title lang 2"
    And the JSON node "data.title_translations[2].language" should be equal to "{l3}"
    And the JSON node "data.title_translations[2].value" should be equal to "Category"

    Examples:
      | entity           | endpoint            |
      | ArticleCategory  | article_categories  |
      | DownloadCategory | download_categories |
      | NewsCategory     | news_categories     |

  Scenario Outline: I unset translations
    Given the following <entity> records exist:
      | #  | title    | slug     |
      | c1 | Category | category |
    And only the following Phrase records exist:
      | #  | Name                          | Phrase       |
      | p1 | obj_<phrase_group>.~c1~_title | title lang 1 |
      | p2 | obj_<phrase_group>.~c1~_title | title lang 2 |

    When I send a PUT request to "/api/v2/<endpoint>/{c1}" with body:
    """
{
  "title_translations": [
    {
      "language": ~l2~,
      "value": "title lang 2"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<endpoint>/{c1}"
    Then the JSON node "data.title_translations" should have 3 elements
    And the JSON node "data.title_translations[0].language" should be equal to "{l1}"
    And the JSON node "data.title_translations[0].value" should be equal to "Category"
    And the JSON node "data.title_translations[1].language" should be equal to "{l2}"
    And the JSON node "data.title_translations[1].value" should be equal to "title lang 2"
    And the JSON node "data.title_translations[2].language" should be equal to "{l3}"
    And the JSON node "data.title_translations[2].value" should be equal to "Category"

    Examples:
      | entity           | endpoint            | phrase_group     |
      | ArticleCategory  | article_categories  | articlecategory  |
      | DownloadCategory | download_categories | downloadcategory |
      | NewsCategory     | news_categories     | newscategory     |

  Scenario Outline: I check that translations are removed on entity delete
    Given the following <entity> records exist:
      | #  | title    | slug     |
      | c1 | Category | category |
    And only the following Phrase records exist:
      | #  | Name                          | Phrase       |
      | p1 | obj_<phrase_group>.~c1~_title | title lang 1 |
      | p2 | obj_<phrase_group>.~c1~_title | title lang 2 |

    When I send a DELETE request to "/api/v2/<endpoint>/{c1}"
    Then the response status code should be 200

    Examples:
      | entity           | endpoint            | phrase_group     |
      | ArticleCategory  | article_categories  | articlecategory  |
      | DownloadCategory | download_categories | downloadcategory |
      | NewsCategory     | news_categories     | newscategory     |
