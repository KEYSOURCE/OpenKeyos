<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html [
<!ENTITY % HTMLlat1 PUBLIC
"-//W3C/ENTITIES Latin 1 for XHTML//EN"
"html4-all.ent">
%HTMLlat1;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:variable name="pagewidth" select="21.5"/>
    <xsl:variable name="bodywidth" select="19"/>
    
    <xsl:template name="page_layout">        
        <fo:layout-master-set>            
            <fo:simple-page-master master-name="cover" page-height="27.9cm" page-width="21.5cm" margin-left="2cm" margin-right="2cm" margin-top="3.5cm" margin-bottom="2cm" maximum-repeats="1">
                <fo:region-body margin-top="3.5cm"/>                
            </fo:simple-page-master>
            <fo:simple-page-master master-name="leftPage" page-height="27.9cm" page-width="21.5cm" margin-left="2cm" margin-right="2cm" margin-top="0.5cm" margin-bottom="0.5cm">
                <fo:region-body margin-top="3.5cm" margin-bottom="1cm"/>
                <fo:region-before extent="2cm"/>
                <fo:region-after extent="1cm"/>                
            </fo:simple-page-master>
            <fo:simple-page-master master-name="rightPage" page-height="27.9cm" page-width="21.5cm" margin-left="2cm" margin-right="2cm" margin-top="0.5cm" margin-bottom="0.25cm">
                <fo:region-body margin-top="3.5cm" margin-bottom="3.5cm"/>
                <fo:region-before extent="2cm"/>
                <fo:region-after extent="1cm"/>
            </fo:simple-page-master>
            <fo:page-sequence-master master-name="contents">
                <fo:single-page-master-reference master-reference="cover"/>
                <fo:repeatable-page-master-reference master-reference="rightPage"/>
            </fo:page-sequence-master>
        </fo:layout-master-set>
    </xsl:template>    
    
    
    <xsl:template name="page_header">
        <fo:static-content flow-name="xsl-region-before">
            <fo:block text-align="right">
                <xsl:attribute name="border-bottom-color">black</xsl:attribute>
                <xsl:attribute name="border-bottom-width">0.1mm</xsl:attribute>
                <xsl:attribute name="border-bottom-style">solid</xsl:attribute>
                <fo:table table-layout="fixed" width="100%">
                    <fo:table-column column-width="proportional-column-width(0.5)" />
                    <fo:table-column column-width="proportional-column-width(0.5)" />
                    <fo:table-body>
                        <fo:table-row>
                        	<fo:table-cell text-align="left">
			   <!-- width=200pt height=46pt -->
			   <fo:block>
                    			   <fo:external-graphic src="file:images/logo.gif" width="154pt" height="35pt"/>
			   </fo:block>
			</fo:table-cell>
			<fo:table-cell text-align="right" padding-top="20pt">
			    <fo:block>
				<xsl:value-of select="/reports/document_title"/>			
			     </fo:block>
			     <fo:block>				
				 <xsl:value-of select="/reports/period"/>				
			     </fo:block>
			</fo:table-cell>
		   </fo:table-row>
                    </fo:table-body>
                </fo:table>
            </fo:block>
        </fo:static-content>        
    </xsl:template>
    <xsl:template name="page_footer">
	<fo:static-content flow-name="xsl-region-after">
		<fo:block font-size="10pt" text-align="center">
			Page <fo:page-number />
		</fo:block>
	</fo:static-content>
    </xsl:template>
</xsl:stylesheet>
