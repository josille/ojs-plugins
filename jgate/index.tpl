{**
 * index.tpl
 *
 * Copyright (c) 2013 Rodrigo De la garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.jgate.displayName"}
{include file="common/header.tpl"}
{/strip}

<br/>

<h3>{translate key="plugins.importexport.jgate.export"}</h3>
{if $journal->getSetting('doiPrefix')}
<ul class="plain">
	<li>&#187; <a href="{plugin_url path="issues"}">{translate key="plugins.importexport.jgate.export.issues"}</a></li>
	<li>&#187; <a href="{plugin_url path="articles"}">{translate key="plugins.importexport.jgate.export.articles"}</a></li>
</ul>
{else}
	{translate key="plugins.importexport.jgate.errors.noDOIprefix"} <br /><br />
	{translate key="manager.setup.doiPrefixDescription"}
{/if}

{include file="common/footer.tpl"}
