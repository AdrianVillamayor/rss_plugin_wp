##  RSS SD

A simple rss reader with the possibility of using keywords with a shortcode assembly.

Contributors: [Adrii](https://github.com/AdrianVillamayor/)

## Installation
Installing "RSS SD" can be done either by searching for "RSS SD" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
1. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
1. Activate the plugin through the 'Plugins' menu in WordPress

## Usage
   /**
    * @param string url => Url where you point to mount all the content
    * @param string keyword => Filter the news by showing only the ones that match. Use commas to use more than one.
    */

    [rss-sd url = "https://blog.socialdiabetes.com/feed/" keyword="diabetes,control"]
```

* To load an url with or withou personalized information

```php

/**
  * @param string url   => Url where you point to mount all the content
  * @param string title => Custom title
  * @param string desc  => Custom description
  * @param string time  => Custom date
  *
*/

 [url-sd url = "https://blog.socialdiabetes.com/en/how-to-connect-with-your-hcp/"]

 [url-sd url = "https://blog.socialdiabetes.com/en/how-to-connect-with-your-hcp/" title="How To Connect With Your HCP" desc="SocialDiabetes is the solution for diabetes care." time="15 MAY, 2019"]


## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://github.com/AdrianVillamayor/Pie-Chart-PHP/blob/master/LICENSE)
