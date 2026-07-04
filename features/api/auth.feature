Feature: Authentication
  As an admin
  I want to log in and log out
  So that I can manage the platform securely

  Scenario: Admin can log in with valid credentials
    Given there is an admin with email "admin@test.com" and password "secret123"
    When I request POST "/api/login" with data:
      """
      {"email": "admin@test.com", "password": "secret123"}
      """
    Then the response status should be 200
    And the response should have field "token"
    And the response should have field "user"

  Scenario: Admin cannot log in with invalid password
    Given there is an admin with email "admin@test.com" and password "secret123"
    When I request POST "/api/login" with data:
      """
      {"email": "admin@test.com", "password": "wrong"}
      """
    Then the response status should be 422

  Scenario: Authenticated user can see their own profile
    Given I am authenticated as an admin
    When I request GET "/api/user"
    Then the response status should be 200

  Scenario: Unauthenticated user cannot see their profile
    Given I am not authenticated
    When I request GET "/api/user"
    Then the response status should be 401

  Scenario: Authenticated user can log out
    Given I am authenticated as an admin
    When I request POST "/api/logout"
    Then the response status should be 200
    And the response should contain "message" with value "Logout realizado com sucesso."

  Scenario: Unauthenticated user cannot log out
    Given I am not authenticated
    When I request POST "/api/logout"
    Then the response status should be 401
