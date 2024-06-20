{*
  * This template generates a logo link with dynamic parameters.
  *
  * Default (black and white logo) - "https://jcaa.caa-aca.ca/public/journals/1/homepageImage_en_US.png"
  *
  * For colored logo use LogoColorer.php pathway.
  * "https://jcaa.caa-aca.ca/plugins/generic/emailIssueToc/logoColorer/logoColorer.php"
  *}

{assign var="link" value="https://jcaa.caa-aca.ca/"}
{assign var="width" value="100%"}
{assign var="logoSource" value="https://jcaa.caa-aca.ca/public/journals/1/homepageImage_en_US.png"}
{assign var="alt" value="Canadian Acoustics"}

<a href="{$link}"><img style="width: {$width};" src="{$logoSource}" alt="{$alt}"/></a>