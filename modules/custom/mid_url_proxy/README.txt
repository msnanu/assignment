MID URL Proxy
--------------

This module provides the functionality to fetch the data from remote server and 
cache the data. Cache timeout is configurable. Same data is served to the client
requesting it. The cached data can also be served in blocks on the page. User 
can create block for each proxy URL configuration.

Configuration page path : /admin/mid-modules/mid_url_proxy/add

Configuration list page path : /admin/mid-modules/mid_url_proxy/list

User can Add, Edit, Delete these configurations. 

Some features and configurations provided by module:
1. URL to get the data.
2. Pretty URL without the "?" in query string.
3. Administrator can configure parameters to be passed  to the remote server 
   User can set the default values. Parameters can be skipped if no value is 
   specified in configuration with "[skip]".
4. Administrator can set the GET/POST method in configuration. 
5. Guzzle library is used for sending the CURL requests to remote source server.
6. Xpath is used to filter the data received from remote server.
7. XSLT can be applied to the XML data received from remote server after Xpath 
   operation.
8. Request timeout time can be set.
9. Error message can be set for client browser display.
10.Caching of processed data can be done. Time limit can also be set on caching.
11.Display processed data in block format on page.
12.Configuration can be Enable / Disable by checking the Active status checkbox.  
13.List view page for all configuration.
14.Create/Edit/Delete/Enable/Disable operation can be done on the proxy URL 
   configuration.

Configuration of multiple parameters:

1. To send the data to the remote source server, administrator can set the 
parameters in Request parameters field on configuration form.
2. Each parameter is declared on separate line.
Parameter name and parameter default value are separated by "~~" sign.
3. To skip some parameter from default value admin user can set it with "[skip]" 
value.


Configuration for Pretty URL format:

1. To send the pretty URL request, admin user have to add the parameters in same
   as above mentioned, and check the "Is Pretty URL" checkbox in configuration 
   form.


Guzzle library configuration:

Guzzle 6 library is used to send the CURL requests. It is fetched from server 
with following commands with compiler tool.

http://docs.guzzlephp.org/en/latest/overview.html

Wiki page: 
http://wiki.internal/wiki/index.php/MID_URL_Proxy



Form Fields Discription
------------------------

1. Source URL: It is used to store the data source URL. It is just external link. Do not add last '/' after domain. If URL is without parameters then add full url here.
2. Proxy URL key: This field stores the key string literal for accessing the data stored in cache. User can direct access the data with following url pattern.
/tools/mid-proxy-url/Proxy_URL_Key
3. Method : This field accepts the GET or POST method of request.
4. Is pretty URL : Checked if data source URL is in pretty URL format.
eg: http://test.com/country/@state/city/@user
parameters will be as follows
state~~gujarat
user~~pritam
Note: Token replacement is available only for pretty URL.

5. Request Parameters : This field stores the parameters in key value format. Key and value are separated by "~~" sign. each entry is done in new line. For pretty URL we need to pass the key value as 'p1~~value', where we are replacing the @p1 in URL by 'value'. We can use the [skip] value to skip the parameter from URL. User can set the default value for parameters with this field.
6. Request header item : This field is used to store the request headers. Each header is stored in new line. Header key and value are separated by "~~" sign.
7. Inner HTML only : If this check box is selected we can exclude the outer wrapper html tag in response.
8. Xpath : Value stored in this field is used to filter the response data. We can extract the required HTML/XML from response data.
9. Xpath exclude : We can exclude certain data from the response data.
10. XSLT text : This value is used to apply the styling to the XML extracted / received.
11. Request time in milliseconds : This is time interval in seconds used for requesting the data. Default 10000 milliseconds.
12. Error message : This message will be displayed to user if no data received from remote server.
13. Cache timeout in minutes : This is time interval in minutes for cache expire. Default value is 1 min.
