# Email Issue JCAA Logo, Toc, Ads, Subs, Footer
This plugin was built from the EmailIssueToc plugin - https://github.com/ulsdevteam/pkp-emailIssueToc
It's designed to add additional content to published issue notifications. Including a logo, Advertisers and sustaining subscribers.

**Installation instructions:**

1. Manually install via the filesystem, extract the contents of this archive to a "emailIssueToc" directory under "plugins/generic" in your OJS root.
2. Move the contents of `objects` folder to the `/templates/frontend/objects` directory.
3. In the workflow section of your OJS, locate the email template "notification"
4. ensure the template body is empty except for `{$notificationContents}`.
5. Set the email template subject as desired. Recommended: `Your digital version of {$siteTitle}`

**Adjustments:**

1. To adjust image sizes, modify line 56, 58 and 125 in `Ads_Subs.php`. Recommended values are 77 and 153, which are 60% of 128 and 256 respectively.
Default: 77, 153
2. To change the logo to either the default black or colored logo, navigate to `templates/frontend/objects/issue_logo.tpl`.
Default: Black and white logo

## To do:
- Find a way to pull the RGB from logoColorer and apply it as a color style to the links in issue_toc_ads.tpl