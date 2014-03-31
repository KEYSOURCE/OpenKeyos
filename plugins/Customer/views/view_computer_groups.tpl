{assign var="paging_titles" value="Customer, Customer computer groups"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<style type="text/css">
	@import "./kskb.css";
</style>

<h1>Computer groups for (#{$customer->id}) {$customer->name}</h1>
<p class="error">{$error_msg}</p>
{literal}
<script type="text/javascript">
//<![CDATA[
	function showContents(group_id)
	{
		var card_elm = document.getElementById("group_"+group_id+"_card");
		var lbl_elm = document.getElementById("group_"+group_id+"_Lbl");
		var lblName_elm = document.getElementById("group_"+group_id+"_LblName");
		if(card_elm.style.display == 'none')
		{
			card_elm.style.display = "block";
			lbl_elm.innerHTML = "Hide";
			lblName_elm.style.display = "none";
		}
		else
		{
			card_elm.style.display = "none";
			lbl_elm.innerHTML = "Show";
			lblName_elm.style.display = "block";
		}
	}
	
//]]>
</script>
{/literal}
<form name="ccgViewFrm" action="" method="post">
{$form_redir}
<table width="95%">
	<thead>
		<tr>
			<td>
				<div id="_searchBox" class="SearchBox" style="float: right; ">
					<label style="font-weight:bold;"><strong>Search: </strong></label><input style="font-weight: normal; font-style:italic; color: #AAA;" size="60" type="text" name="searchBox" id="searchBox">
				</div>	
			</td>
		</tr>
	</thead>
	{foreach from=$groups item="group"}
	<tr>		
		<td>
			<ul class="ArticleListingContainer_info">								
				<li>									
					<div class="BostonPostCard"> 
						<label id="group_{$group->id}_LblName" style="display: none; float: left; font-weight: bold;">(#{$group->id}) {$group->title|escape}</label>	
						<label id="group_{$group->id}_Lbl" style="float: right; font-weight: bold;" onclick="showContents({$group->id})">Hide</label><p />
						<table class="borderCollapse" id="group_{$group->id}_card">
							<tr>
								<td class="imageColumn">
									{*
									{if $category->hasImage}
									<img alt={$group->title} src="yahoo or skype avatar">
									{/if}
									*}
								</td>
								<td class="textColumn" style="width: 60%; border-right: 1px solid #ccc;">
									<a href="/?cl=customer&op=edit_computer_group&id={$group->id}">
									<b><span name="title">(#{$group->id}) {$group->title|escape}</span></b>
									</a>
									<p />
									<ul>
										<li><b>Description: </b><span name="description">{$group->description}</span></li>
										{assign var="country_id" value=$group->country}
										<li><b>Country:     </b><span name="country">{$countries_list.$country_id}</li>
										<li><b>Address:     </b><span name="address"><a target="_blank" href="{$BASE_URL}/customer_map/index.php?from={$end_locations_strings.0}&to={$group->address}">{$group->address}</a></span></li>
										<li><b>Email:       </b><span name="email"><a href="mailto:{$group->email}">{$group->email}</a></span></li>
										<li><b>Phone(s):    </b><span name="phone">{$group->phone1}{if $group->phone2 != ""}, {$group->phone2}{/if}</span></li>
										<li><b>Fax:    	    </b><span name="fax">{$group->fax}</span></li>
										{if $group->language}
										<li><b>Language:    </b><span name="language">{$group->language}</span></li>
										{/if}
										<li><b>Yahoo! ID:   </b><span name="yahoo_id"><a href="ymsgr:sendim?{$group->yim}">{$group->yim} <img src="/images/icons/ym.png" style="width: 30px; height: 30px; border: 0;"></a></span></li>
										<li><b>Skype ID:    </b><span name="skype_id"><a href="skype:{$group->skype_im}?chat">{$group->skype_im} <img src="/images/icons/skype.png" style="width: 30px; height: 30px; border: 0;"></a></span></li>
									</ul>
									<div class="more">
										<span><a href="/?cl=customer&op=edit_computer_group&id={$group->id}">Edit ...</a></span>
									</div>
									<br />
								</td>
								<td class="textColumn" style="width: 40%; padding-left: 20px;">
									<b><span>Computers in this group</span></b>
									<p />
									<ul>
										{assign var="computers_list" value=$group->computers_list}
										{foreach from=$computers_list item="comp_id"}
										<li item><span itemprop="id"><a href="/?cl=kawacs&op=computer_view&id={$comp_id}">#{$comp_id}</a></span>  <span itemprop="name"><a href="/?cl=kawacs&op=computer_view&id={$comp_id}">{$customer_computers.$comp_id}</a></span>
										{/foreach}
									</ul>
								</td>
							</tr>
						</table>
					</div>									
				</li>
			</ul>
		</td>
	</tr>
	{/foreach}
</table>

</form>