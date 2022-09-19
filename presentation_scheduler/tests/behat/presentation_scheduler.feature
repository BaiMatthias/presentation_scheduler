@block @block_presentation_scheduler @javascript
Feature: Block presentation_scheduler
  In order to check correct display of presentation_scheduler
  As a admin
  I can add the presentation_scheduler block on site homepage

  Background:


  Scenario: Add presentation_scheduler block to homepage
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Termine für Belegpräsentationen" block
    Then I should see "Keine Termine angelegt" in the "Termine für Belegpräsentationen" "block"


