<div id="site-manager" ng:controller="adSitesController" ng:cloak>
	<h2 class="originUI-header"><a href="/administrator/settings" class="originUI-back originUI-hover">Site Demo Templates</a></h2>
	<form id="siteManager-create" name="siteManagerCreateForm" class="originUI-tileLeft originUI-bgColorSecondary originUI-shadow" novalidate>
		<input type="hidden" name="uploadDir" value="/assets/components/"/>
		<h3 id="siteManager-createHeader" class="originUI-tileHeader originUI-borderColor originUI-textColor">Create</h3>
		<div class="originUI-tileContent">
			<?php echo $this->element('platform/form_site', array('view'=>'left', 'editor' => 'editor'));?>
			<?php echo $this->element('platform/form_site', array('view'=>'right', 'editor' => 'editor'));?>
		</div>
		<div class="originUI-tileFooter">
			<button class="originUI-tileFooterCenter originUI-hover" ng:click="siteCreate()" ng-disabled="siteManagerCreateForm.$invalid">Create</button>
		</div>
	</form><!--
	--><div id="siteManager-list" class="originUI-tileRight originUI-bgColor originUI-shadow">
		<h3 id="siteManager-listHeader" class="originUI-tileHeader originUI-borderColor originUI-textColor">Site List</h3>
		<table id="siteManager-table" class="originUI-table" cellspacing="0" cellpadding="0" width="100%" border="0">
			<thead class="originUI-tableHead originUI-noSelect">
				<th class="originUI-tableHeadStatus">&nbsp;</th>
				<th class="originUI-tableHeadName" ng:click="siteFilter='OriginSite.name';reverse=!reverse">Name</th>
				<th class="originUI-tableHeadDescription">Description</th>
			</thead>
			<tbody class="originUI-tableBody">
				<tr class="originUI-tableRow originUI-hover" ng:repeat="site in sites|orderBy:siteFilter:reverse|filter:searchOrigin" ng:animate="'originUI-fade'" ng:class="(site.OriginSite.status !== '1')? 'inactive': ''">
					<td class="originUI-tableStatus originUI-tableCell" ng:show="site.OriginSite.status == '1'" class="userList-status">
						<img src="/img/icon-check-small.png" alt="Active" ng:click="toggleStatus('OriginSite', site.OriginSite.id, 'disable')"/>
					</td>
					<td class="originUI-tableStatus originUI-tableCell" ng:show="site.OriginSite.status != '1'" class="userList-status">
						<img src="/img/icon-cross-small.png" alt="Inactive" ng:click="toggleStatus('OriginSite', site.OriginSite.id, 'enable')"/>
					</td>
					<td class="originUI-tableName originUI-tableCell" ng:click="siteEdit(site)">{{site.OriginSite.name}} ({{site.OriginSite.alias}})</td>
					<td class="originUI-tableDescription originUI-tableCell" ng:click="siteEdit(site)">{{site.OriginSite.content.description}}</td>
				</tr>
			</tbody>
		</table> 
	</div>
	
	<div modal="originModal" close="siteClose()" options="{backdropClick: false, backdropFade: true}">
		<form id="siteManager-edit" name="siteManagerEdit" class="originUI-bgColorSecondary originUI-modal" novalidate>
			<input type="hidden" ng:model="editorModal.id"/>
			<h3 id="siteManager-editHeader" class="originUI-tileHeader originUI-borderColor originUI-textColor">Edit Site</h3>
			
			<a href="javascript:void(0)" class="originUI-modalRemove originUI-hover originUI-iconHover" ng:click="siteRemove()">remove</a>
			
			<div class="originUI-modalContent">
				<div class="originUI-modalLeft">
					<?php echo $this->element('platform/form_site', array('view'=>'left', 'editor' => 'editorModal'));?>
				</div><!--
				--><div class="originUI-modalRight">
				<?php echo $this->element('platform/form_site', array('view'=>'right', 'editor' => 'editorModal'));?>
				</div>
				<div class="clear"></div>		
			</div>
			<div class="originUI-tileFooter">
				<button class="originUI-tileFooterLeft originUI-hover" ng:click="siteClose()">Cancel</button>
				<button class="originUI-tileFooterRight originUI-hover" ng:click="siteSave()" ng-disabled="siteManagerEdit.$invalid">Save</button>
			</div>
		</form>
	</div>
</div>

<?php
	echo $this->Minify->css(array('platform/platformSettings'));
	echo $this->Minify->script(array('platform/adSitesController'));