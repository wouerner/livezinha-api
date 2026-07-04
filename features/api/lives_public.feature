Feature: Public Lives Listing
  As a visitor
  I want to see all live streams
  So that I can browse the schedule

  Scenario: Lists all lives ordered by scheduled_at desc
    Given there is an active live stream with title "Live Ativa"
    Given there is a scheduled live stream with title "Live Futura"
    Given there is a finished live stream with title "Live Passada"
    When I request GET "/api/lives"
    Then the response status should be 200
    And the response should have 3 items

  Scenario: Returns empty array when no lives exist
    When I request GET "/api/lives"
    Then the response status should be 200
    And the response should be empty

  Scenario: Ping endpoint returns 200
    When I request GET "/api/ping"
    Then the response status should be 200
