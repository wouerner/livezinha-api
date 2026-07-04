Feature: Question Stacking
  As a viewer
  I want to see the active questions on the current live
  So that I can follow along with the discussion

  Background:
    Given there is an active live stream with title "Live BDD"

  Scenario: Returns empty array when no active live exists
    Given there is an active live stream with title "Outra Live"
    When I request GET "/api/lives/active/question"
    Then the response status should be 200
    And the response should be empty

  Scenario: Returns up to three active non-hidden questions ordered by displayed_at
    Given there are 3 active questions for the live
    And there is a question with status "active" and text "Pergunta Oculta"
    When I request GET "/api/lives/active/question"
    Then the response status should be 200
    And the response should have 3 items

  Scenario: When a fourth question is activated, the oldest is archived
    Given I am authenticated as an admin
    Given there are 3 active questions, oldest displayed 15 minutes ago
    And there is a question with status "approved" and text "Quarta Pergunta"
    When I request PUT "/api/questions/4" with body:
      """
      {"status": "active"}
      """
    Then the response status should be 200
    And the response should contain "status" with value "active"

    When I request GET "/api/lives/active/question"
    Then the response status should be 200
    And the response should have 3 items

  Scenario: Cannot activate a question on a non-existent live
    Given I am authenticated as an admin
    When I request PUT "/api/questions/999" with body:
      """
      {"status": "active"}
      """
    Then the response status should be 404
