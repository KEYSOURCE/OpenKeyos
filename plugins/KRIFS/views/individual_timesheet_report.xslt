<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html [
<!ENTITY % HTMLlat1 PUBLIC
"-//W3C/ENTITIES Latin 1 for XHTML//EN"
"html4-all.ent">
%HTMLlat1;
]>
<!-- New document created with EditiX at Thu Jul 03 13:11:04 EEST 2008 -->

<xsl:stylesheet 
	version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:output method="xml" indent="yes"/>
	<xsl:template name="individual_report">
	<fo:block break-before="page" xsl:use-attribute-sets="page-title" >
   	        Timesheet for (#<xsl:value-of select="user_id"/>) <xsl:value-of select="user_name"/>       			  
           <fo:block height="2cm" >
           </fo:block>
           <fo:table table-layout="fixed" width="100%"  empty-cells="show">
            <fo:table-column column-width="proportional-column-width(0.15)" />
            <fo:table-column column-width="proportional-column-width(0.25)" />
	 <fo:table-column column-width="proportional-column-width(0.15)" />
            <fo:table-column column-width="proportional-column-width(0.25)" />
            <fo:table-column column-width="proportional-column-width(0.1)" />
	 <fo:table-column column-width="proportional-column-width(0.1)" />
	 <fo:table-header>
	        <fo:table-row>
		 <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Day</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Date</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
		  	 <fo:block>Id</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
		            <fo:block>Status</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Defined time</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Total time</fo:block>
		  </fo:table-cell>
	        </fo:table-row>
	</fo:table-header>   
	<fo:table-body>
	    <xsl:for-each select="timesheets/timesheet">
	         <fo:table-row keep-together="always">
		    <fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
			      <xsl:value-of select="day"/>
			</fo:block>
		    </fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="date"/>
			</fo:block>			
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="id"/>
			</fo:block>
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block xsl:use-attribute-sets="pre-like">
				<xsl:value-of select="status"/>
			</fo:block>
		</fo:table-cell>						
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="defined_time"/>
			</fo:block>
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="total_time"/>
			</fo:block>
		</fo:table-cell>
	         </fo:table-row>
	     </xsl:for-each>
	   </fo:table-body> 
           </fo:table> 
           
           <fo:block height="1cm"></fo:block>           
          
            <xsl:for-each select="timesheets/timesheet">
           <fo:table table-layout="fixed" width="100%"  empty-cells="show">
            <fo:table-column column-width="proportional-column-width(0.15)" />
            <fo:table-column column-width="proportional-column-width(0.25)" />
	 <fo:table-column column-width="proportional-column-width(0.15)" />
            <fo:table-column column-width="proportional-column-width(0.15)" />
	 <fo:table-column column-width="proportional-column-width(0.3)" />	
	 <fo:table-header>
	         <fo:table-row xsl:use-attribute-sets="table-head-list1" >
	          <fo:table-cell xsl:use-attribute-sets="table-head-list1" number-columns-spanned="5" >
			<fo:block><xsl:value-of select="day"/> - <xsl:value-of select="date"/></fo:block>
		  </fo:table-cell>
	         </fo:table-row>
	        <fo:table-row>
		 <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Hour</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Actvity / Action type</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
		  	 <fo:block>Location</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
		            <fo:block>Customer</fo:block>
		  </fo:table-cell>
		  <fo:table-cell xsl:use-attribute-sets="table-head-list">
			<fo:block>Ticket detail / Comments</fo:block>
		  </fo:table-cell>
	      </fo:table-row>		 
	</fo:table-header>   
	<fo:table-body>
	    <xsl:for-each select="details/detail">
	         <fo:table-row keep-together="always">
		    <fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
			      <xsl:value-of select="hour"/>
			</fo:block>
		    </fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="activity"/>
			</fo:block>			
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="location"/>
			</fo:block>
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block>
				<xsl:value-of select="customer"/>
			</fo:block>
		</fo:table-cell>
		<fo:table-cell xsl:use-attribute-sets="table-cell-list">
			<fo:block xsl:use-attribute-sets="pre-like">
				<xsl:value-of select="ticket_detail"/>
			</fo:block>
		</fo:table-cell>								
	         </fo:table-row>
	     </xsl:for-each>
	   </fo:table-body> 
           </fo:table>   
           </xsl:for-each>
           <fo:block height="0.5cm"></fo:block>
           
                     
           </fo:block>
           
	</xsl:template>
</xsl:stylesheet>
