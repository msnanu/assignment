ID SOCIAL MEDIA FEEDS
---------------------

This module supports multiple channels like "Twitter", "Facebook", "LinkedIn", 
"Instagram" etc. Each Channel is provided the configuration form separately.  
This module provides the list of all the configuration done for different 
channels. This module integrates the SWFeeds API in module. 

This module provide the block for each channel feeds as well as the block for 
mix of all feeds posts.

Twitter:
--------

This module allowing the single as well as multiple twitter account for the 
configuration.

Account configuration form have following fields.
1. Feed type to fetch: This have multiple option from twitter to fetch different 
   type of data from twitter. 
   a. User Time line.
   b. Home Time line.
   c. Mentions Time line.
2. Account Language: This is providing the option for the multilingual 
   functionality configuration. As twitter provides one account for each 
   language. we are allowing one configuration for each language, User can 
   alternatively set account for all languages available in site.
3. Twitter Hashtag(@): This is the username of twitter account.
4. Limit: The number of posts to be fetched from twitter.
5. Twitter Access Token: Twitter access token provided by Twitter.
6. Twitter Token Secret: Value provided by Twitter.
7. Twitter Consumer Key: Value provided by Twitter.
8. Twitter Consumer Secrete: Value provided by Twitter.




SM Feeds API Configuration:
---------------------------

Following fields are present on form.

1. SOAP Client URL: This is the text field used to accept the URL for the SMFeed
   API.
2. Domain Name: This is the domain name need to be passed while calling the sm 
   feeds API.
3. Error reporting email: This is the email id need to be passed to SMFeed API, 
   API will mail if any error comes in fetching data.



Brick configuration:
--------------------

For current functionality one brick item is configured with only once single 
text field used to receive the block machine name. This field need to be render 
only value in display. Created the twig template for the brick. In twig template
we are rendering the block name. 



