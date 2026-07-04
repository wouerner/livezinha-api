Feature: Public Questions Listing
  As a spectator
  I want to see approved and active questions for a live stream
  So that I can follow the discussion

  Background:
    Given there is an active live stream with title "Live BDD"

  Scenario: Returns approved and active questions for a live
    Given there is a question with status "approved" and text "Pergunta Aprovada"
    Given there is a question with status "active" and text "Pergunta Ativa"
    Given there is a question with status "pending" and text "Pergunta Pendente"
    When I request GET "/api/lives/1/questions/public"
    Then the response status should be 200
    And the response should have 2 items

  Scenario: Returns empty when no approved or active questions
    Given there is a question with status "pending" and text "Pendente"
    When I request GET "/api/lives/1/questions/public"
    Then the response status should be 200
    And the response should be empty

  Scenario: 404 when live does not exist
    When I request GET "/api/lives/999/questions/public"
    Then the response status should be 404
