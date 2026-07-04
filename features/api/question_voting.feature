Feature: Question Voting
  As a viewer
  I want to vote on questions
  So that I can express agreement or disagreement

  Background:
    Given there is an active live stream
    And there is a question with text "Devo aprender BDD?"

  Scenario: Vote like on a question
    When I request POST "/api/questions/1/vote" with data:
      """
      {"vote": "like"}
      """
    Then the response status should be 200
    And the response should contain "likes_count" with integer 1
    And the response should contain "dislikes_count" with integer 0

  Scenario: Vote dislike on a question
    When I request POST "/api/questions/1/vote" with data:
      """
      {"vote": "dislike"}
      """
    Then the response status should be 200
    And the response should contain "likes_count" with integer 0
    And the response should contain "dislikes_count" with integer 1

  Scenario: Voting with invalid vote type returns 422
    When I request POST "/api/questions/1/vote" with data:
      """
      {"vote": "invalid"}
      """
    Then the response status should be 422

  Scenario: Voting on a non-existent question returns 404
    When I request POST "/api/questions/999/vote" with data:
      """
      {"vote": "like"}
      """
    Then the response status should be 404
