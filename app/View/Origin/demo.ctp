<!-- <div ng:include src="demo.template"></div> -->
<script type="text/javascript">
	var _config			= '<?php echo $demo;?>';
	var origin_embed	= '<?php echo urlencode($this->element('origin_embed'));?>';
</script>

<div ng:cloak>
	<div ng:include src="demo.template" onload="demoAdTags()"></div>
	<?php
		//$config		= json_decode($demo['OriginDemo']['config']);
		//echo $this->element('sites/'.$config->templateAlias);
	?>
</div>
