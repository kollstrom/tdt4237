# TDT4237 Software Security - Group 21

## Tables that need modification
In order for the fixes to work there are a couple of columns in the user table that needs to be added to the database:
```
alter table users add column last_login_attempt varchar(255);
alter table users add column login_fails int(1);
alter table users add column locked boolean;
```

## Distribution of vulnerability fixing

### Emil
- Info leakage
- Lock out mechanism (includes implementation of throttle protection)
- Weak password policy
- Debug mode enabled
- Stored xss
- Make github project
- Git workflow description
- Clickable links warning

### Daniel
- Bypassing authentication
- Escalating account privs
- Csrf 
- Fix ShareLaTex project

### Olivier
- Session fixation
- Session timeout
- Weak hash
- File inclusion

### Eirik
- SQL injection
- Email verification

## Git Workflow

#### Branches
We will make branches for each of the vulnerabilities we are fixing. After cloning the repository, when we're about to start fixing an issue you make a new branch: `git checkout -b info-leakage`, where info-leakage is the branch name that corresponds to Emil's first area of responsibility. Use dashes `-` instead of spaces. 

#### Merging to master
Do not merge your branch straight to master after you're done with a fix. Make a pull request, and have someone review, test and merge it to master. 

#### Rebasing before PR
Rebase master into your local branch if there has been changes to the master branch after you branched out from it: `git rebase origin/master`. Do this before you make a pull request.

#### Interactive rebasing to clean commit history
If you want to clean up your messy commit history before you do a pull request, have a look at [interactive rebasing](https://robots.thoughtbot.com/git-interactive-rebase-squash-amend-rewriting-history). This is not a must.
