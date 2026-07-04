Feature: Active Live Stream
  As a viewer
  I want to see the currently active live stream
  So that I know what to watch

  Scenario: Returns the active live when one exists
    Given there is an active live stream with title "Minha Live Ativa"
    When I request GET "/api/lives/active"
    Then the response status should be 200
    And the response should contain "title" with value "Minha Live Ativa"
    And the response should contain "status" with value "active"

  Scenario: Returns the next scheduled live when no active live exists
    Given there is a scheduled live stream with title "Live Futura"
    When I request GET "/api/lives/active"
    Then the response status should be 200
    And the response should contain "title" with value "Live Futura"
    And the response should contain "status" with value "scheduled"

  Scenario: Prefers active over scheduled when both exist
    Given there is an active live stream with title "Live Ativa"
    Given there is a scheduled live stream with title "Live Agendada"
    When I request GET "/api/lives/active"
    Then the response status should be 200
    And the response should contain "title" with value "Live Ativa"

  Scenario: Returns empty when no active or scheduled lives exist
    When I request GET "/api/lives/active"
    Then the response status should be 200
    And the response should be empty
