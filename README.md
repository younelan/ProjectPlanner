# Issue Viewer

Simple Jira Viewer using the Database. This is not meant as a full project management tool but should at least allow to show issues.

## About
This project was started because Atlassian Stopped supporting on prem and forced to moved to their cloud server. I ended up with a defunct Jira Server which I had to take down because it was end of Life

This is what I like to release 0.02 , my 2 cents

(c) Youness El Andaloussi

As Is, No Warranties

## Install
At this point, it relies on a Jira Database in MySQL (maybe postgres, untested)
- Put the script in /var/www/html
- Copy config.php.default to config.php
- Edit config.php to point to your Jira Database
 

## Functionality
- View Project List
- View Issue List and associated Issues
- View Issue Details with link to associated Issues
- Basic Edit Issue
- Create/Delete Issue
- Simple Project Board/Backlog
  - show issues
  - Drag swim lane
  - tabbed view
- Sprints
  - create sprint
  - view sprint
  - swim lane for sprint 
- Issue Details
  - Show Comment
  - Task history
- Project
  - edit/view statistics


## Plans
- Add Auth
- Add template engine like simplicity
- Templates/ Themes
 

