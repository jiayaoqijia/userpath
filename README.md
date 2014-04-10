UserPath
========

### Introduction:

--------------------------------------

Once a web application authenticates a user, it loosely associates 
all resources owned by the user to the web session established.
Consequently, any scripts injected into the victim web session attains 
unfettered access to user-owned resources, including scripts that are 
committing malicious activities inside a web application. In this paper, we
establish the first explicit notion of user sub-origins to defeat such scripts.
Based on this notion, we propose a new solution called UserPath to 
establish an end-to-end trusted path between the web application users
and web servers. To evaluate our solution, we implement a prototype in
Chromium, and retrofit it to 20 popular web applications. UserPath reduces 
the size of client-side TCB that has access to user-owned resources
by 8x to 264x, with minimal developer effort.

--------------------------

###  Repo Structure:

--------------------------

web-app-mods: Summary of source code modification in case studies.

userpath-patch-chromium12.0.742.112: Patches to chromium and web apps.

Wiki Pages/DEMO Video URLs: UserPath demo videos.

techreport: The technical report for UserPath.
