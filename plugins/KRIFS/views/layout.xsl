<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html [
<!ENTITY % HTMLlat1 PUBLIC
"-//W3C/ENTITIES Latin 1 for XHTML//EN"
"html4-all.ent">
%HTMLlat1;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="2.0" xmlns:fo="http://www.w3.org/1999/XSL/Format">

    <!-- now set some styles to use in the document -->
    <xsl:attribute-set name="document-title">
        <xsl:attribute name="space-before">5cm</xsl:attribute>
        <xsl:attribute name="space-after">18pt</xsl:attribute>
        <xsl:attribute name="text-align">right</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-size">28pt</xsl:attribute>
        <xsl:attribute name="color">#709D19</xsl:attribute>
    </xsl:attribute-set>
     <xsl:attribute-set name="document-title1">
        <xsl:attribute name="space-before">1cm</xsl:attribute>
        <xsl:attribute name="space-after">18pt</xsl:attribute>
        <xsl:attribute name="text-align">right</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-size">20pt</xsl:attribute>
        <xsl:attribute name="color">#709D19</xsl:attribute>
    </xsl:attribute-set>
    
    <xsl:attribute-set name="page-title">
        <xsl:attribute name="space-before">6pt</xsl:attribute>
        <xsl:attribute name="space-after">18pt</xsl:attribute>
        <xsl:attribute name="text-align">left</xsl:attribute>
        <xsl:attribute name="font-size">20pt</xsl:attribute>
        <xsl:attribute name="border-bottom-color">#709D19</xsl:attribute>
        <xsl:attribute name="border-bottom-width">0.1mm</xsl:attribute>
        <xsl:attribute name="border-bottom-style">solid</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="color">#709D19</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="table-head-list">
	<xsl:attribute name="padding">1mm</xsl:attribute>
	<xsl:attribute name="font-size">9pt</xsl:attribute>	
	<xsl:attribute name="font-weight">bold</xsl:attribute>
	<xsl:attribute name="background-color">#DDDDDD</xsl:attribute> 
	<xsl:attribute name="border-top-color">grey</xsl:attribute> 
	<xsl:attribute name="border-top-width">0.1mm</xsl:attribute>
	<xsl:attribute name="border-top-style">solid</xsl:attribute>
</xsl:attribute-set>
<xsl:attribute-set name="table-head-list1">
	<xsl:attribute name="padding">10mm</xsl:attribute>
	<xsl:attribute name="font-size">10pt</xsl:attribute>	
	<xsl:attribute name="font-weight">bold</xsl:attribute>
	<xsl:attribute name="color">#000000</xsl:attribute>
	<xsl:attribute name="background-color">#FFFFFF</xsl:attribute> 	
	<xsl:attribute name="border-top-color">grey</xsl:attribute> 
	<xsl:attribute name="border-top-width">0.1mm</xsl:attribute>
	<xsl:attribute name="border-top-style">solid</xsl:attribute>
</xsl:attribute-set>
<xsl:attribute-set name="pre-like">
	<xsl:attribute name="white-space-collapse">false</xsl:attribute>
	<xsl:attribute name="linefeed-treatment">preserve</xsl:attribute>
	<xsl:attribute name="white-space-treatment">preserve</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="table-cell-list">
	<xsl:attribute name="padding">1mm</xsl:attribute>
	<xsl:attribute name="padding-top">0.5mm</xsl:attribute>
	<xsl:attribute name="padding-bottom">0.2mm</xsl:attribute>
	<xsl:attribute name="font-size">8pt</xsl:attribute>
	<xsl:attribute name="color">#000000</xsl:attribute>
	<xsl:attribute name="font-weight">normal</xsl:attribute>
	<xsl:attribute name="border-top-color">grey</xsl:attribute> 
	<xsl:attribute name="border-top-width">0.1mm</xsl:attribute>
	<xsl:attribute name="border-top-style">solid</xsl:attribute>	
</xsl:attribute-set>

<xsl:attribute-set name="table-cell-grid">
	<xsl:attribute name="padding">1pt</xsl:attribute>
	<xsl:attribute name="padding-bottom">0.5pt</xsl:attribute>
	<xsl:attribute name="font-size">7pt</xsl:attribute>
	<xsl:attribute name="border-color">grey</xsl:attribute> 
	<xsl:attribute name="border-width">0.01mm</xsl:attribute>
	<xsl:attribute name="border-style">solid</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="table-cell-grid-small">
	<xsl:attribute name="padding">0pt</xsl:attribute>
	<xsl:attribute name="padding-top">1pt</xsl:attribute>
	<xsl:attribute name="font-size">6pt</xsl:attribute>
	<xsl:attribute name="border-color">grey</xsl:attribute> 
	<xsl:attribute name="border-width">0.01mm</xsl:attribute>
	<xsl:attribute name="border-style">solid</xsl:attribute>
	<xsl:attribute name="text-align">center</xsl:attribute>
	<!--<xsl:attribute name="alignment-baseline">bottom</xsl:attribute>-->
</xsl:attribute-set>

<xsl:attribute-set name="table-head-grid">
	<xsl:attribute name="padding">0pt</xsl:attribute>
	<xsl:attribute name="padding-top">1pt</xsl:attribute>
	<xsl:attribute name="font-size">7pt</xsl:attribute>	
	<xsl:attribute name="background-color">#DDDDDD</xsl:attribute> 
	<xsl:attribute name="border-color">grey</xsl:attribute> 
	<xsl:attribute name="border-width">0.01mm</xsl:attribute>
	<xsl:attribute name="border-style">solid</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="table-head-grid-small">
	<xsl:attribute name="padding">0.5pt</xsl:attribute>
	<xsl:attribute name="font-size">3pt</xsl:attribute>	
	<xsl:attribute name="background-color">#DDDDDD</xsl:attribute> 
	<xsl:attribute name="border-color">grey</xsl:attribute> 
	<xsl:attribute name="border-width">0.01mm</xsl:attribute>
	<xsl:attribute name="border-style">solid</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="border_box_hard">
	<xsl:attribute name="padding">2mm</xsl:attribute>
	<xsl:attribute name="font-size">9pt</xsl:attribute>
	<xsl:attribute name="border-color">grey</xsl:attribute> 
	<xsl:attribute name="border-width">0.1mm</xsl:attribute>
	<xsl:attribute name="border-style">solid</xsl:attribute>
</xsl:attribute-set>

<xsl:template name="left_border_black">
	<xsl:attribute name="border-left-width">0.8pt</xsl:attribute> 
	<xsl:attribute name="border-left-color">#000000</xsl:attribute> 
</xsl:template>
</xsl:stylesheet>
