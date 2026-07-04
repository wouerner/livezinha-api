Feature: Admin CRUD for Live Streams
  As an admin
  I want to create, view, update, and delete live streams
  So that I can manage the broadcast schedule

  Background:
    Given I am authenticated as an admin

  Scenario: Create a new live stream
    When I request POST "/api/lives" with data:
      """
      {"title": "Live de Teste", "streamer_name": "Joao", "scheduled_at": "2026-07-10 20:00:00"}
      """
    Then the response status should be 201
    And the response should contain "title" with value "Live de Teste"
    And the response should contain "streamer_name" with value "Joao"
    And the response should contain "status" with value "scheduled"

  Scenario: Create a live stream as active
    When I request POST "/api/lives" with data:
      """
      {"title": "Live Ativa", "status": "active", "scheduled_at": "2026-07-10 20:00:00"}
      """
    Then the response status should be 201
    And the response should contain "status" with value "active"

  Scenario: Cannot create a second active live stream
    Given there is an active live stream with title "Primeira Live"
    When I request POST "/api/lives" with data:
      """
      {"title": "Segunda Live", "status": "active", "scheduled_at": "2026-07-10 20:00:00"}
      """
    Then the response status should be 422
    And the response should contain "message" with value "Já existe uma live ativa. Finalize-a antes de ativar outra."

  Scenario: View a single live stream
    Given there is an active live stream with title "Live Visivel"
    When I request GET "/api/lives/1"
    Then the response status should be 200
    And the response should contain "id" with integer 1
    And the response should contain "title" with value "Live Visivel"

  Scenario: Update a live stream
    Given there is an active live stream with title "Live Original"
    When I request PUT "/api/lives/1" with data:
      """
      {"title": "Live Editada", "status": "finished"}
      """
    Then the response status should be 200
    And the response should contain "title" with value "Live Editada"
    And the response should contain "status" with value "finished"

  Scenario: Delete a live stream
    Given there is an active live stream with title "Live Deletada"
    When I request DELETE "/api/lives/1"
    Then the response status should be 200
    And the response should contain "message" with value "Live deletada com sucesso"

  Scenario: Unauthenticated user cannot create a live
    Given I am not authenticated
    When I request POST "/api/lives" with data:
      """
      {"title": "Hack", "scheduled_at": "2026-07-10 20:00:00"}
      """
    Then the response status should be 401

  Scenario: Unauthenticated user cannot delete a live
    Given I am not authenticated
    When I request DELETE "/api/lives/1"
    Then the response status should be 401
