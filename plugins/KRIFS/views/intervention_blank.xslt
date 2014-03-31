<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE html [
  <!ENTITY % HTMLlat1 PUBLIC
  "-//W3C/ENTITIES Latin 1 for XHTML//EN"
  "html4-all.ent">
  %HTMLlat1;
]>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">
		
<xsl:import href="../customer/layout_styles.xsl_fo"/>
		
<xsl:variable name="pagewidth" select="21.5"/>
<xsl:variable name="bodywidth" select="19"/>

<!-- The root element in the XML data -->
<xsl:template match="/intervention_report">

<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<!-- Page layout -->
	<fo:layout-master-set>
		<fo:simple-page-master master-name="leftPage"
			page-height="27.9cm"
			page-width="21.5cm"
			margin-left="1cm"
			margin-right="2cm"
			margin-top="2cm"
			margin-bottom="1cm">
			<fo:region-before extent="2cm"/>
			<fo:region-after extent="1cm"/>
			<fo:region-body 
				margin-top="3cm"
				margin-bottom="2cm" />
		</fo:simple-page-master>

		<fo:simple-page-master master-name="rightPage"
			page-height="27.9cm"
			page-width="21.5cm"
			margin-left="2cm"
			margin-right="1cm"
			margin-top="1cm"
			margin-bottom="1cm">
			<fo:region-before extent="2cm"/>
			<fo:region-after extent="1cm"/>
			<fo:region-body 
				margin-top="3cm"
				margin-bottom="2cm" />
		</fo:simple-page-master>
		
		<fo:page-sequence-master master-reference="contents">
			<fo:repeatable-page-master-alternatives>
				<fo:conditional-page-master-reference
					master-reference="leftPage"
					odd-or-even="odd"/>
				<fo:conditional-page-master-reference
					master-reference="rightPage"
					odd-or-even="even"/>
			</fo:repeatable-page-master-alternatives>
		</fo:page-sequence-master>

	</fo:layout-master-set>

	<fo:page-sequence master-name="contents" initial-page-number="1">
		<xsl:apply-templates />
		
		<!-- Page header -->
		<fo:static-content flow-name="xsl-region-before">
			<fo:block text-align="center">
				<xsl:attribute name="border-bottom-color">black</xsl:attribute>
				<xsl:attribute name="border-bottom-width">0.1mm</xsl:attribute>
				<xsl:attribute name="border-bottom-style">solid</xsl:attribute>
	
				<fo:table table-layout="fixed" width="100%">
				<fo:table-column column-width="proportional-column-width(0.5)"/>
				<fo:table-column column-width="proportional-column-width(0.5)"/>
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell text-align="left">
							<fo:external-graphic width="154pt" height="35pt">
								<xsl:attribute name="src">
									<xsl:value-of select="logo"></xsl:value-of>
								</xsl:attribute>							
							</fo:external-graphic>
						</fo:table-cell>
						<fo:table-cell text-align="right" padding-top="10pt">
							<fo:block xsl:use-attribute-sets="header-title">Intervention</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
				</fo:table>
			</fo:block>
		</fo:static-content>
		
		<!-- Page footer -->
		<fo:static-content flow-name="xsl-region-after">
			<fo:block font-size="9pt" text-align="center">
				Page <fo:page-number />
			</fo:block>
		</fo:static-content>
		
		
		<!-- Page content -->
		<fo:flow flow-name="xsl-region-body">
		
			<fo:table table-layout="fixed" width="100%" height="4cm">
				<fo:table-column column-width="proportional-column-width(0.6)"/>
				<fo:table-column column-width="proportional-column-width(0.4)"/>
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
						<fo:block xsl:use-attribute-sets="section-title" space-before="5pt">
						<xsl:attribute name="border-bottom-color">#709D19</xsl:attribute>
						<xsl:attribute name="border-bottom-width">0.1mm</xsl:attribute>
						<xsl:attribute name="border-bottom-style">solid</xsl:attribute>
							Reference: # 
							<xsl:value-of select="intervention/@id"/>
							<fo:block font-style="italic">
							<xsl:value-of select="intervention/subject"/>
							</fo:block>
						</fo:block>
						</fo:table-cell>
						
						<fo:table-cell padding-left="1cm">
						<fo:block-container xsl:use-attribute-sets="border_box_hard" height="4cm" width="6cm">
							<fo:block font-weight="bold">
							Client: <xsl:value-of select="info/customer/name"/> (# <xsl:value-of select="info/customer/@id"/>)
							</fo:block>
							<fo:block>&nbsp;</fo:block>
							<fo:block>&nbsp;</fo:block>
							<fo:block>&nbsp;</fo:block>
							<fo:block>&nbsp;</fo:block>
						</fo:block-container>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
			
			<fo:table table-layout="fixed" width="100%" space-after="1cm">
			<fo:table-column column-width="proportional-column-width(0.25)"/>
			<fo:table-column column-width="proportional-column-width(0.7)"/>
			
			<fo:table-body>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Intervenant</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
							<fo:block><xsl:value-of select="info/user"/></fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Lieu</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
						</fo:table-cell>
					</fo:table-row>					
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Heure de debut</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Heure de fin</fo:block>
						</fo:table-cell>						
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Distance</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Temp total</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
			
			<fo:block font-size="12pt" text-align="left" font-weight="bold">
			Details:
			</fo:block>
			
			
			
			<fo:footnote overflow="paginate">
			<fo:inline baseline-shift="super" font-size="smaller"><fo:block>&nbsp;</fo:block></fo:inline>
			
			<fo:footnote-body keep-together="always">
				<fo:table table-layout="fixed" width="100%" space-before="0cm" space-after="0cm">
					<fo:table-column column-width="proportional-column-width(0.3)"/>
					<fo:table-column column-width="proportional-column-width(0.3)"/>
					<fo:table-column column-width="proportional-column-width(0.1)"/>
					<fo:table-column column-width="proportional-column-width(0.3)"/>
					<fo:table-body>
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
								<fo:block><xsl:value-of select="footer/name"/></fo:block>
								<fo:block><xsl:value-of select="footer/address"/></fo:block>
								<fo:block><xsl:value-of select="footer/city"/></fo:block>
								<fo:block><xsl:value-of select="footer/country"/></fo:block>
								<fo:block><xsl:value-of select="footer/phone"/></fo:block>
								<fo:block><xsl:value-of select="footer/fax"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-left="none">
								<fo:block><xsl:value-of select="footer/email"/></fo:block>
								<fo:block><xsl:value-of select="footer/web"/></fo:block>
								<fo:block><xsl:value-of select="footer/tva"/></fo:block>
								<fo:block><xsl:value-of select="footer/rcb"/></fo:block>
								<fo:block><xsl:value-of select="footer/bbl"/></fo:block>
								<fo:block><xsl:value-of select="footer/fortis"/></fo:block>
							</fo:table-cell>
							<fo:table-cell/>
							<fo:table-cell xsl:use-attribute-sets="border_box_hard">
								<fo:block font-weight="bold">Signature client:</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
			</fo:footnote-body>
			</fo:footnote>
			
			
		</fo:flow>
	</fo:page-sequence>
</fo:root>
</xsl:template>

</xsl:stylesheet>