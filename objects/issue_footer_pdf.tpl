{*
  *
  * This template generates a footer with about us links.
  *}

{assign var="aboutLink" value="https://jcaa.caa-aca.ca/index.php/jcaa/about"}
{assign var="editorialTeamLink" value="https://jcaa.caa-aca.ca/index.php/jcaa/about/editorialTeam"}
{assign var="boardMembersLink" value="https://caa-aca.ca/contacts/"}

{assign var="aboutText" value="About Canadian Acoustics | À propos de Acoustique Canadienne"}
{assign var="editorialTeamText" value="Canadian Acoustics Editorial Team | Équipe éditoriale de l'acoustique canadienne"}
{assign var="boardMembersText" value="Canadian Acoustical Association Board Members | Membres du conseil d’administration de l’Association canadienne d’acoustique"}

<h2 style="font-size: 20px; font-weight: normal; font-family: Lato, sans-serif; letter-spacing: 0.6px;" >About us | À propos de nous</h2>
<a href="{$aboutLink}">{$aboutText}</a><br/>
<a href="{$editorialTeamLink}">{$editorialTeamText}</a><br/>
<a href="{$boardMembersLink}">{$boardMembersText}</a>