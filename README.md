# PrePublish Checks by Kgaurav
 A simple wordpress plugin which enforces variety of checks on a post before it could be published.

## Description
A simple plugin to enforce variety of checks before publishing any new post.Define minimum and maximum title length.
Make presence of a featured image compulsory.
Specify the minimum/maximum height and width for your featured images.
Bonus feature check for post slug to be in english.

Do you own a multi-author website,who keeps publishing posts with too small or too big title?
Or do you yourself keep forgetting to add featured image before clicking on that «publish» button?
Maybe people keep making posts on your website with featured images of such small resolution that they start looking blurry on your landing page?

This plugin will ensure that you can set custom conditions that need to be met before someone could publish a post.
If anyone clicks on the ‘publish’ button and one of the conditions are not met(For eg-if publish button is clicked without adding a featured image.) then the publish event will be intercepted,post will be saved as a draft instead and user will get an appropriate error showing what they did wrong and how they can correct their mistake before publishing.


### What checks and limits can be set on new posts publishing using this plugin?
You could check for these conditions(If any of these conditions are not met,Post will not be published) –
-Title Minimum Length(default 10 characters).
-Title Max Length(default 280 characters).
-Check to see if slug is in English(default yes).
-Make Featured Image Compulsory(default yes).
-Minimum Featured Image Width(default 500px).
(only works if ‘Make Featured Image Compulsory option’ above is selected).
-Minimum Featured Image Height(default 400px).
(only works if ‘Make Featured Image Compulsory option’ above is selected).
-Maximum Featured Image Width(default 4000px).
(only works if ‘Make Featured Image Compulsory option’ above is selected).
-Maximum Featured Image Height(default 4000px).
(only works if ‘Make Featured Image Compulsory option’ above is selected).


### How does it work technically?
This plugin is triggered on «transition_post_status» hook.
Everytime a post status is changed the plugin checks for all of the set conditions to be true and if any of the set conditions are not met,
then post status is changed to draft instead of publish.
Also user is shown an error with which conditions they didn’t meet.


### Is PrePublish Checks by Kgaurav free?
Yes! PrePublish Checks by Kgaurav’s core features are and always will be free.


### Where can I find the settings for this plugin?
Go to Settings -> Prepublish Checks. There you will find the options to change all the settings for this plugin.

