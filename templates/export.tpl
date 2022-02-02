{**
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 *}
{extends file="layouts/backend.tpl"}
	
{block name="page"}
	<h1 class="app__pageHeading">
		{$pageTitle}
	</h1>
<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#importExportTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="importExportTabs">
	<ul>
			<li><a href="#exportSubmissions-tab">{translate key="plugins.importexport.common.export.articles"}</a></li>
	</ul>
			<div id="exportSubmissions-tab">
			<script type="text/javascript">
				$(function() {ldelim}
					// Attach the form handler.
					$('#exportPrereviewForm').pkpHandler('$.pkp.controllers.form.FormHandler');
				{rdelim});
			</script>

<form id="exportPrereviewForm" class="pkp_form" method="POST" action="{plugin_url path="exportDoi"}" >
{csrf}
	
		<div class="app__contentPanel ">
		{if $plugin=="false"}
			<div class="pkp_notification" id="PrereviewConfigurationErrors">
				{include file="controllers/notification/inPlaceNotificationContent.tpl" notificationId=PrereviewConfigurationErrors notificationStyleClass="notifyWarning" notificationTitle="plugins.importexport.prereview.requirements"|translate notificationContents="plugins.importexport.prereview.requirementsMessage"|translate}
				{if $prereviewSettingsLinkAction}
						{include file="linkAction/linkAction.tpl" action=$prereviewSettingsLinkAction}
				{/if}
			</div>
		
		</br>
		{elseif $plugin=="true"}
			
			
		<div class="pkp_controllers_grid ">
			<table class="pkpTable">
				<thead>
					<tr>
						<th>{translate key="plugins.importexport.prereview.selector"}</th>
						<th>ID</th>
						<th>{translate key="plugins.importexport.prereview.title"}</th>
						<th>DOI</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
			
					{foreach $publications as $publication}
						<tr class="gridRow">
							<td><input type="checkbox"  name="selectedSubmissions[]" value={$publication.id} /></td>
							<td >{$publication.id}</td>
							<td><a href="{$publication.url}"> {$publication.name} </a></td>
							<td>{$publication.doi}</td>
							<td>{$publication.status}</td>
							
						</tr>
					{/foreach}
				</tbody>
			</table>
			</br>
		</div>
		</br>
		{fbvFormSection}
			<button class="pkp_button" type="submit">{translate key="plugins.importexport.prereview.action"}</button>
		{/fbvFormSection}
		{/if}
		</form>
</div>
{/block}
