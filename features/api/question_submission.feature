Feature: Question Submission
  As a spectator
  I want to submit a question to the current live stream
  So that I can participate in the broadcast

  Background:
    Given there is an active live stream with title "Live BDD"

  Scenario: Spectator submits a question successfully
    When I request POST "/api/questions" with data:
      """
      {
        "live_stream_id": 1,
        "name": "Maria",
        "tiktok_handle": "@maria_dev",
        "question_text": "Como funciona o BDD?"
      }
      """
    Then the response status should be 201
    And the response should contain "name" with value "Maria"
    And the response should contain "question_text" with value "Como funciona o BDD?"
    And the response should contain "status" with value "pending"
    And the response should have field "passcode"
    And the response should contain "is_tagged" with boolean false

  Scenario: TikTok handle gets normalized with @ prefix
    When I request POST "/api/questions" with data:
      """
      {
        "live_stream_id": 1,
        "name": "Joao",
        "tiktok_handle": "joao_dev",
        "question_text": "Teste de handle?"
      }
      """
    Then the response status should be 201
    And the response should contain "tiktok_handle" with value "@joao_dev"

  Scenario: Submitting without required fields returns 422
    When I request POST "/api/questions" with data:
      """
      {"live_stream_id": 1}
      """
    Then the response status should be 422

  Scenario: Submitting to non-existent live returns 422
    When I request POST "/api/questions" with data:
      """
      {
        "live_stream_id": 999,
        "name": "Teste",
        "question_text": "Existe?"
      }
      """
    Then the response status should be 422
