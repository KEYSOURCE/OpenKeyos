<?xml version="1.0" encoding="utf-8"?>
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
		
		<!--
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
				margin-bottom="1cm" />
		</fo:simple-page-master>

		<fo:simple-page-master master-name="rightPage"
			page-height="27.9cm"
			page-width="21.5cm"
			margin-left="2cm"
			margin-right="1cm"
			margin-top="1cm"
			margin-bottom="0.5cm">
			<fo:region-before extent="2cm"/>
			<fo:region-after extent="1cm"/>
			<fo:region-body 
				margin-top="3cm"
				margin-bottom="1cm" />
		</fo:simple-page-master> -->

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
							<fo:block xsl:use-attribute-sets="header-title">Intervention Report</fo:block>
							<fo:block xsl:use-attribute-sets="header-title" font-style="italic">
								<xsl:if test="info/filter/show = 'detailed'">Detailed Report</xsl:if>
								<xsl:if test="info/filter/show = 'summary'">Summary</xsl:if>
								<xsl:if test="info/filter/view != 'customer'"> - Keysource</xsl:if>
							</fo:block>
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
							Customer: <xsl:value-of select="info/customer/name"/> (# <xsl:value-of select="info/customer/@id"/>)
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
			
			
			{if $filter.show == 'summary'}
			<xsl:if test="info/filter/show = 'summary'">
			<!-- Show a summary version of the intervention report -->

			<fo:table table-layout="fixed" width="100%" space-after="1cm">
				<fo:table-column column-width="proportional-column-width(0.1)"/>
				<xsl:if test="info/filter/view = 'customer'">
					<fo:table-column column-width="proportional-column-width(0.56)"/>
					<fo:table-column column-width="proportional-column-width(0.17)"/>
					<fo:table-column column-width="proportional-column-width(0.17)"/>
				</xsl:if>
				<xsl:if test="info/filter/view = 'keysource'">
					<fo:table-column column-width="proportional-column-width(0.46)"/>
					<fo:table-column column-width="proportional-column-width(0.17)"/>
					<fo:table-column column-width="proportional-column-width(0.17)"/>
					<fo:table-column column-width="proportional-column-width(0.1)"/>
				</xsl:if>
				
				<fo:table-header>
					<fo:table-row>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Ticket #</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Subject</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>First intervention</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Last intervention</fo:block>
						</fo:table-cell>
						<xsl:if test="info/filter/view = 'keysource'">
							<fo:table-cell xsl:use-attribute-sets="table-head-list" text-align="right">
								<fo:block>Work time</fo:block>
							</fo:table-cell>
						</xsl:if>
					</fo:table-row>
				</fo:table-header>
				<xsl:if test="info/filter/view = 'keysource'">
					<fo:table-footer>
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>TOTAL:</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list" number-columns-spanned="3" />
							<fo:table-cell xsl:use-attribute-sets="table-head-list" text-align="right">
								<fo:block><xsl:value-of select="/intervention_report/intervention/work_time"/></fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-footer>
				</xsl:if>
				<fo:table-body>
					<xsl:for-each select="tickets/ticket">
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="@id"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="subject"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="time_in"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="time_out"/></fo:block>
							</fo:table-cell>
							<xsl:if test="/intervention_report/info/filter/view = 'keysource'">
								<fo:table-cell xsl:use-attribute-sets="table-cell-list" text-align="right">
									<fo:block><xsl:value-of select="work_time"/></fo:block>
								</fo:table-cell>
							</xsl:if>
						</fo:table-row>
					</xsl:for-each>
				</fo:table-body>
				
			</fo:table>
			</xsl:if>
			
			
			<xsl:if test="info/filter/show = 'detailed'">
			<!-- Show a detailed version of the intervention report -->
			<fo:table table-layout="fixed" width="100%" space-after="1cm">
				<fo:table-column column-width="proportional-column-width(0.25)"/>
				<fo:table-column column-width="proportional-column-width(0.10)"/>
				<fo:table-column column-width="proportional-column-width(0.30)"/>
				<xsl:if test="info/filter/view = 'customer'">
					<fo:table-column column-width="proportional-column-width(0.15)"/>
					<fo:table-column column-width="proportional-column-width(0.15)"/>
					<fo:table-column column-width="proportional-column-width(0.05)"/>
				</xsl:if>
				<xsl:if test="info/filter/view = 'keysource'">
					<fo:table-column column-width="proportional-column-width(0.15)"/>
					<fo:table-column column-width="proportional-column-width(0.13)"/>
					<fo:table-column column-width="proportional-column-width(0.12)"/>
				</xsl:if>
				
				<fo:table-header>
					<fo:table-row>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Intervenant</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Location</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="table-head-list">
							<fo:block>Action type</fo:block>
						</fo:table-cell>
						<xsl:if test="info/filter/view = 'customer'">
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>Time in</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>Time out</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>Pricing</fo:block>
							</fo:table-cell>
						</xsl:if>
						<xsl:if test="info/filter/view = 'keysource'">
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>Time in</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list">
								<fo:block>Billable</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list" text-align="right">
								<fo:block>Work time</fo:block>
							</fo:table-cell>
						</xsl:if>
					</fo:table-row>
				</fo:table-header>
				<xsl:if test="info/filter/view = 'keysource'">
					<fo:table-footer>
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="table-head-list" number-columns-spanned="5">
								<fo:block>TOTAL:</fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-head-list" text-align="right">
								<fo:block><xsl:value-of select="intervention/work_time"/></fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-footer>
				</xsl:if>
				<fo:table-body>
					<xsl:for-each select="details/detail">
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="user"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="location"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="action_type"/></fo:block>
							</fo:table-cell>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list">
								<fo:block><xsl:value-of select="time_in"/></fo:block>
							</fo:table-cell>
							<xsl:if test="/intervention_report/info/filter/view = 'customer'">
								<fo:table-cell xsl:use-attribute-sets="table-cell-list">
									<fo:block><xsl:value-of select="time_out"/></fo:block>
								</fo:table-cell>
								<fo:table-cell xsl:use-attribute-sets="table-cell-list" text-align="right">
									<fo:block><xsl:value-of select="pricing"/></fo:block>
								</fo:table-cell>
							</xsl:if>
							<xsl:if test="/intervention_report/info/filter/view = 'keysource'">
								<fo:table-cell xsl:use-attribute-sets="table-cell-list">
									<fo:block><xsl:value-of select="billable"/>, <xsl:value-of select="pricing"/></fo:block>
								</fo:table-cell>
								<fo:table-cell xsl:use-attribute-sets="table-cell-list" text-align="right">
									<fo:block><xsl:value-of select="work_time"/></fo:block>
								</fo:table-cell>
							</xsl:if>
						</fo:table-row>
						
						<fo:table-row>
							<fo:table-cell xsl:use-attribute-sets="table-cell-list" number-columns-spanned="6"
							padding-bottom="2mm" padding-left="1cm" >
								<fo:block font-style="italic">
									Ticket #<xsl:value-of select="ticket/@id"/>: <xsl:value-of select="ticket/subject"/>
								</fo:block>
								<fo:block xsl:use-attribute-sets="pre-like">
									<xsl:if test="info/filter/view != 'customer'">
									<xsl:if test="private = 'yes'"><fo:inline font-weight="bold">[Private] </fo:inline></xsl:if>
									</xsl:if>
									<xsl:value-of select="ticket/comments"/>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</xsl:for-each>
				</fo:table-body>
			</fo:table>
			</xsl:if>
			
			<!-- Footnote address and signature -->
			<!-- 
			<fo:table table-layout="fixed" width="100%" space-before="1cm" space-after="0.5cm" keep-together="always">
				<fo:table-column column-width="proportional-column-width(0.3)"/>
				<fo:table-column column-width="proportional-column-width(0.3)"/>
				<fo:table-column column-width="proportional-column-width(0.1)"/>
				<fo:table-column column-width="proportional-column-width(0.3)"/>
				<fo:table-body>
					<fo:table-row keep-together="always">
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-right="none">
							<fo:block>Keysource scrl</fo:block>
							<fo:block>Av. de la Couronne 480</fo:block>
							<fo:block>1050 Brussels</fo:block>
							<fo:block>Belgium</fo:block>
							<fo:block>T +32-2-644.96.53</fo:block>
							<fo:block>F +32-3-649.18.11</fo:block>
						</fo:table-cell>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard" border-left="none">
							<fo:block>info@keysource.be</fo:block>
							<fo:block>www.keysource.be</fo:block>
							<fo:block>TVA: BE 435 019 363</fo:block>
							<fo:block>RCB: 508.360</fo:block>
							<fo:block>BBL: 310-0808309-94</fo:block>
							<fo:block>FORTIS: 210-0533549-04</fo:block>
						</fo:table-cell>
						<fo:table-cell/>
						<fo:table-cell xsl:use-attribute-sets="border_box_hard">
							<fo:block font-weight="bold">Customer signature:</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
			
			
			<fo:block overflow="paginate" space-after="1cm">
			 -->
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
			<!-- </fo:block>&nbsp;
			<fo:block>&nbsp;</fo:block>
			 -->
		</fo:flow>
	</fo:page-sequence>

</fo:root>
</xsl:template>

</xsl:stylesheet>