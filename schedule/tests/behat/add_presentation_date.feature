@page @add_presentation_date @javascript
Feature: Page Presentation Scheduler
  In order to check correct display of elements on the page
  I can navigate to the "manage" page

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Termine für Belegpräsentationen" block

  Scenario: Navigate to "manage" page
    When I press "Verwalten"
    Then I should see "Termin erstellen"




Scenario: Fill Fields and create Presentation Date
  When I press "Verwalten"
  And I fill "date" with "13-08-2022"
  Then I should see "Termin erstellen"