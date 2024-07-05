# Email Issue JCAA Logo, Toc, Ads, Subs, Footer
This plugin was built from the EmailIssueToc plugin - https://github.com/ulsdevteam/pkp-emailIssueToc
It's designed to add additional content to published issue notifications. Including a logo, Advertisers and sustaining subscribers.

**Installation instructions:**

1. Manually install via the filesystem, extract the contents of this archive to a "jcaaEmailIssueToc" directory under "plugins/generic" in your OJS root.
2. In the workflow section of your OJS, locate the email template "notification"
3. ensure the template body is empty except for `{$notificationContents}`.
4. Set the email template subject as desired. Recommended: `Your digital version of {$siteTitle}`

**Adjustments:**

1. To adjust image sizes, modify line 56, 58 and 125 in `Ads_Subs.php`. Recommended values are 77 and 153, which are 60% of 128 and 256 respectively.
Default: 77, 153
2. To change the logo to either the default black or colored logo, navigate to `templates/issue_logo.tpl`.
Default: Black and white logo

## To do:
- Find a way to pull the RGB from logoColorer and apply it as a color style to the links in issue_toc_ads.tpl

---------------

## Goal
CAA Journal is being digitalized. As such the Journal needs an improved plugin to display
- CAA Logo
- Table of contents
- Advertisers
- Sustaining Members
- Footer
[EmailIssueToc plugin](https://github.com/ulsdevteam/pkp-emailIssueToc) already exists and is able to dynamically pull and display the Table of contents from the Issue published.
Advertisers and Sustaining Member must also be dynamically pulled and displayed. Only active advertisers and active long-term sustaining members must be displayed

## Process
Using the EmailIssueToc as a base, we can add to the '$message' variable.
#### Issue_logo.tpl
This is a very simple tpl. It adds a single `<a>` Hyperlink, with an `<img>` Image. The four variables 'link', 'width', 'logoSource' and 'alt' can all be adjusted.

#### Issue_toc_ads.tpl & articles_summary_ads.tpl
These are copied from the origianal Toc plugin. Slightly Modified to suit our needs.
- Removed Published date section
- replaced article_summary with article_summary_ads
- Removed Gallery Links Section
#### Ads & Subs
To display our advertisers and sustaining members, we'll need to use sql and connect to the database. `Ads_Subs.php`, `ads.sql`, `subs.sql` are all based off files in the JCAA_Tools
The SQL files are simplified versions of the JCAA tools SQL files.
- ads.sql pulls from the subscriptions table
	- Subscription Type as 'Type', reference_number as 'Filename'
- and from the Institutional_subscriptions
	- institutional name
- subs.sql pulls from subscriptions table
    - reference_number as 'Logo_File'
    - date_end as 'Completion date'
- institutional_subscriptions table
    - domain as 'Domain'
    - institution_name as 'Company'
- Filters include active users, active subscriptions, English locale, and type ID 4
- Orders by company name and filters by completion date greater than the current date
Ads_Subs.php uses the following 3 functions to display those sql pulls
1. **load_file_content**
    - Reads the content of a file and appends each line to a string.
    - Logs an error if the file is not readable or fails to open.
2. **print_ads**
    - Connects to a MySQL database and executes a given SQL query.
    - Iterates through the result set and generates HTML content for displaying advertisements.
    - Constructs an HTML structure with the advertisement image, filename, width, and institution name.
    - Adds headers and links for the advertisers section in both English and French.
    - Returns the generated HTML content.
3. **print_subs**
    - Connects to a MySQL database and executes a given SQL query.
    - Iterates through the result set and generates HTML content for displaying sustaining subscribers.
    - Constructs an HTML structure with the domain, logo, and company name.
    - Adds headers and links for the sustaining members section in both English and French.
    - Returns the generated HTML content.
#### Issue_footer.tpl
Last our footer adds in 3 `<a>` hyperlink elements with the french and english text. Set as variable for easy changes.
