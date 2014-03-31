<?xml version="1.0" encoding="ISO-8859-1"?>

<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml"
xmlns:w10="urn:schemas-microsoft-com:office:word"
xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core"
xmlns:aml="http://schemas.microsoft.com/aml/2001/core"
xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882"
xmlns:msxsl="urn:schemas-microsoft-com:xslt" 
xmlns:v="urn:schemas-microsoft-com:vml" 
w:macrosPresent="no"
w:embeddedObjPresent="yes"
w:ocxPresent="no" >
<!--
xml:space="preserve">
-->

<!-- Import style definitions -->
<xsl:import href="layout_styles_wordml.xslt" />

<!-- Import individual report sections -->
<xsl:import href="report_computers_wordml.xslt" />
<xsl:import href="report_peripherals_wordml.xslt" />
<xsl:import href="report_warranties_wordml.xslt" />
<xsl:import href="report_software_wordml.xslt" />
<xsl:import href="report_licenses_wordml.xslt" />
<xsl:import href="report_users_wordml.xslt" />
<xsl:import href="report_free_space_wordml.xslt" />
<xsl:import href="report_backups_wordml.xslt" />
<xsl:import href="report_av_status_wordml.xslt" />
<xsl:import href="report_av_hist_wordml.xslt" />

<!--<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>-->
<xsl:output indent="no" encoding="ISO-8859-1" method="xml"/>

<!-- Selector/loader for what report sections to include -->
<xsl:template name="template_selector">
	<w:p>
		<w:pPr>
			<w:pStyle w:val="Heading2"/>
			<!-- If section cover pages are not requested and if this is the first report in the section, then supress the page break from the header style -->
			<xsl:if test="/reports_set/show_section_cover_pages = 'no'">
				<xsl:if test="not (preceding-sibling::report[last()]/type)">
					<w:pageBreakBefore w:val="off"/>
				</xsl:if>
			</xsl:if>
		</w:pPr>
		<w:r><w:t><xsl:value-of select="title"/></w:t></w:r>
	</w:p>
	
	<xsl:choose>
		<xsl:when test="type='computers'">
			<xsl:call-template name="computers"/>
		</xsl:when>
		
		<xsl:when test="type='outstanding_tickets'">
			<xsl:call-template name="outstanding_tickets"/>
		</xsl:when>
		
		<xsl:when test="type='peripherals'">
			<xsl:call-template name="peripherals"/>
		</xsl:when>
		
		<xsl:when test="type='warranties'">
			<xsl:call-template name="warranties"/>
		</xsl:when>
		
		<xsl:when test="type='software'">
			<xsl:call-template name="software"/>
		</xsl:when>
		
		<xsl:when test="type='licenses'">
			<xsl:call-template name="licenses"/>
		</xsl:when>
		
		<xsl:when test="type='users'">
			<xsl:call-template name="users"/>
		</xsl:when>
		
		<xsl:when test="type='free_space'">
			<xsl:call-template name="free_space"/>
		</xsl:when>
		
		<xsl:when test="type='backups'">
			<xsl:call-template name="backups"/>
		</xsl:when>
		
		<xsl:when test="type='av_status'">
			<xsl:call-template name="av_status"/>
		</xsl:when>
		
		<xsl:when test="type='av_hist'">
			<xsl:call-template name="av_hist"/>
		</xsl:when>
	</xsl:choose>
</xsl:template>

<xsl:template match="/">
	<xsl:processing-instruction name="mso-application">progid="Word.Document"</xsl:processing-instruction>
	<w:wordDocument>
	<o:DocumentProperties>
	<o:Title>
		<xsl:value-of select="/reports_set/main_title"/>
	</o:Title>
	</o:DocumentProperties>
	
	<w:fonts>
		<w:defaultFonts w:ascii="Arial" w:fareast="Arial" w:h-ansi="Arial" w:cs="Arial"/>
	</w:fonts>
	
	<w:styles>
		<xsl:call-template name="layout_styles"/>
	</w:styles>
	
	<w:lists>
		<w:listDef w:listDefId="1">
			<w:lsid w:val="554A3CE8"/>
			<w:plt w:val="Multilevel"/>
			<w:tmpl w:val="773EE3E6"/>
			
			<w:lvl w:ilvl="0">
				<w:start w:val="1"/>
				<w:pStyle w:val="Heading1"/>
				<w:lvlText w:val="%1."/>
				<w:lvlJc w:val="left"/>
			</w:lvl>
			
			<w:lvl w:ilvl="1">
				<w:start w:val="1"/>
				<w:pStyle w:val="Heading2"/>
				<w:lvlText w:val="%1.%2."/>
				<w:lvlJc w:val="left"/>
			</w:lvl>
			
			<w:lvl w:ilvl="2">
				<w:start w:val="1"/>
				<w:pStyle w:val="Heading3"/>
				<w:lvlText w:val="%1.%2.%3."/>
				<w:lvlJc w:val="left"/>
			</w:lvl>
			
			<w:lvl w:ilvl="3">
				<w:start w:val="1"/>
				<w:pStyle w:val="Heading4"/>
				<w:lvlText w:val="%1.%2.%3.%4."/>
				<w:lvlJc w:val="left"/>
			</w:lvl>
		</w:listDef>
		
		
		<w:list w:ilfo="1">
			<w:ilst w:val="1"/>
		</w:list>
	</w:lists>
	
	<w:docPr>
		<w:view w:val="print"/>
		<w:zoom w:percent="100"/>
		<w:doNotEmbedSystemFonts/>
		<w:proofState w:spelling="clean" w:grammar="clean"/>
		<w:attachedTemplate w:val=""/>
		<w:defaultTabStop w:val="720"/>
		<w:punctuationKerning/>
		<w:characterSpacingControl w:val="DontCompress"/>
		<w:optimizeForBrowser/>
		<w:validateAgainstSchema/>
		<w:saveInvalidXML/>
		<w:ignoreMixedContent/>
		<w:alwaysShowPlaceholderText/>
		<w:compat>
			<w:breakWrappedTables/>
			<w:snapToGridInCell/>
			<w:wrapTextWithPunct/>
			<w:useAsianBreakRules/>
			<w:dontGrowAutofit/>
		</w:compat>
	</w:docPr>

	
	<w:body>
	<wx:sect>
	<wx:sub-section>
	
		<!-- Cover page, if requested -->
		<xsl:if test="/reports_set/show_cover_page = 'yes'">
			<xsl:call-template name="title_page"/>
		</xsl:if>
	
		<!--
		<xsl:apply-templates/>
		-->
		
		<!-- Parse each individual report -->
		<xsl:for-each select="/reports_set/section">
			<!--<xsl:if test="/reports_set/show_section_cover_pages = 'yes'"> -->
				<xsl:call-template name="section_cover_page"/>
			<!-- </xsl:if> -->
			
			<xsl:for-each select="report">
				<xsl:call-template name="template_selector"/>
			</xsl:for-each>
		</xsl:for-each>
		
		<!-- Set page dimensions, header, footer - WordprocessingML requires this to be at the end of the section -->
		<w:sectPr>
			<!-- Page size -->
			<w:pgSz w:w="11909" w:h="16834" w:code="9"/>
			<w:pgMar w:top="1160" w:right="1440" w:bottom="1440" w:left="1440" w:header="288" w:footer="288" w:gutter="0"/>
			<w:cols w:space="720"/>
			<w:docGrid w:line-pitch="360"/>
			
			<!-- Header (empty) for the first page, if a cover page is used -->
			<xsl:if test="/reports_set/show_cover_page = 'yes'">
				<w:hdr w:type="first"> 
					<w:p/>
				</w:hdr>
				<w:titlePg/>
			</xsl:if>
			
			<!-- Header for all pages -->
			<w:hdr w:type="odd">
			<w:tbl>
				<w:tblPr>
					<w:tblW w:w="9500" w:type="dxa"/>
					<w:tblInd w:w="0" w:type="dxa"/>
					<w:tblBorders>
						<w:top w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/>
						<w:left w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/>
						<w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/>
						<w:right w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/>
						<w:insideH w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/>
						<w:insideV w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/>
					</w:tblBorders>
					<w:tblLook w:val="01E0"/>
				</w:tblPr>
				<w:tblGrid>
					<w:gridCol w:w="3000"/>
					<w:gridCol w:w="6500"/>
				</w:tblGrid>
	
				<w:tr>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="3000" w:type="dxa"/>
							<w:vAlign w:val="bottom"/>
						</w:tcPr>
						<w:p>
							<w:r>
<!-- No indentation in order not to mess up BASE64 data -->
<w:pict>
<w:binData w:name="wordml://ks_logo.gif">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</w:binData>
<v:shape id="_x0000_i1025" type="#_x0000_t75" style="width:125.85pt;height:28.8pt">
<v:imagedata src="wordml://ks_logo.gif" o:title="Keysource_logo"/>
</v:shape>
</w:pict>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="6500" w:type="dxa"/>
							<w:vAlign w:val="bottom"/>
						</w:tcPr>
						<w:p>
							<w:pPr><w:pStyle w:val="Header"/><w:jc w:val="right"/></w:pPr>
							<w:r>
							<w:t><xsl:value-of select="/reports_set/main_title"/></w:t>
							</w:r>
						</w:p>
					</w:tc>
				</w:tr>
			</w:tbl>
			</w:hdr>
			<!-- End header definition -->

			<!-- Footer for all pages -->
			<w:ftr w:type="odd">
				<w:p>
					<w:pPr><w:pStyle w:val="Footer"/><w:jc w:val="center"/></w:pPr>
					<w:r><w:t xml:space="preserve">Page </w:t></w:r>
					<w:r>
						<w:rPr><w:rStyle w:val="PageNumber"/></w:rPr>
						<w:fldChar w:fldCharType="begin"/>
					</w:r>
					<w:r>
						<w:rPr><w:rStyle w:val="PageNumber"/></w:rPr>
						<w:instrText> PAGE </w:instrText>
					</w:r>
					<w:r>
						<w:rPr><w:rStyle w:val="PageNumber"/></w:rPr>
						<w:fldChar w:fldCharType="separate"/>
					</w:r>
					<w:r>
						<w:rPr><w:rStyle w:val="PageNumber"/><w:noProof/></w:rPr>
						<w:t> 2</w:t>
					</w:r>
					<w:r>
						<w:rPr><w:rStyle w:val="PageNumber"/></w:rPr>
						<w:fldChar w:fldCharType="end"/>
					</w:r>
				</w:p>
			</w:ftr>
			<!-- End footer definition -->
			
		</w:sectPr>
		
	</wx:sub-section>
	</wx:sect>
	</w:body>

	</w:wordDocument>
</xsl:template>

<!-- To be used when needed to preseve line breaks ('<br>') -->
<xsl:template match="br"><w:br w:type="text-wrapping"/></xsl:template>

<!-- Report main title page -->
<xsl:template name="title_page">
	<w:p>
		<w:pPr><w:pStyle w:val="Title"/></w:pPr>
		<w:r>
			<w:t xml:space="preserve"><xsl:value-of select="/reports_set/main_title"/></w:t>
		</w:r>
	</w:p>
</xsl:template>

<!-- Sections cover pages -->
<xsl:template name="section_cover_page">
	<w:p>
		<w:pPr><w:pStyle w:val="Heading1"/></w:pPr>
		<w:r>
			<w:t xml:space="preserve"><xsl:value-of select="title"/></w:t>
		</w:r>
	</w:p>
</xsl:template>


<!--
<xsl:template match="text()"/>
-->
</xsl:stylesheet>