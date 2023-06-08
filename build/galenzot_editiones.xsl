<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  
  xmlns:bib="http://purl.org/net/biblio#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"
  xmlns:link="http://purl.org/rss/1.0/modules/link/"
  xmlns:prism="http://prismstandard.org/namespaces/1.2/basic/"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:z="http://www.zotero.org/namespaces/export#"
  
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="bib dc dcterms foaf link prism rdf z" 
  >
  <xsl:include href="galenzot_html.xsl"/>
  <xsl:output method="xml" indent="yes" encoding="UTF-8" omit-xml-declaration="yes"/>
  
  <!-- 
    
    <z:Collection rdf:about="#collection_19">
        <dc:title>Editiones verbatim</dc:title>
        <dcterms:hasPart rdf:resource="https://galenus-verbatim.huma-num.fr/tlg0057.tlg099.1st1K-grc1"/>
   -->
  
  
  
  <xsl:template match="/">
    <article id="editiones" class="text">
      <!-- loop on all editions to produce a bibliographic record for page -->
      <xsl:for-each select="/*/bib:*[dc:subject = '_verbatim']">
        <!-- Galenus first -->
        <xsl:sort select=".//foaf:surname"/>
        <xsl:sort select="dc:subject/dcterms:LCC/rdf:value"/>
        <xsl:apply-templates select="." mode="verbatim"/>
      </xsl:for-each>
    </article>
  </xsl:template>
  
  <xsl:template match="bib:BookSection | bib:Book" mode="verbatim">
    <xsl:variable name="id">
      <xsl:call-template name="id"/>
    </xsl:variable>
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <xsl:variable name="url" select="dc:identifier/dcterms:URI/rdf:value"/>
    <!-- Legacy
      <xsl:for-each select="key('about', link:link/@rdf:resource)/dc:identifier">
        <xsl:variable name="str" select="normalize-space(.)"/>
        <xsl:choose>
          <xsl:when test="contains($str, 'galenus-verbatim')">
            <xsl:value-of select="$str"/>
          </xsl:when>
        </xsl:choose>
      </xsl:for-each>
    -->
    <section id="{$id}">
      <xsl:call-template name="class">
        <xsl:with-param name="class">editio</xsl:with-param>
      </xsl:call-template>
      <div class="opus_tituli">
        <xsl:apply-templates select="key('opus', $fichtner_no)" mode="cartouche"/>
        <xsl:variable name="self" select="."/>
        <!-- other editions online -->
        <xsl:for-each select="key('verbatim', $fichtner_no)">
          <xsl:if test="count(.|$self) = 2">
            <div class="editionalt">
              <xsl:text>altera editio: </xsl:text>
              <xsl:apply-templates select="." mode="short"/>
            </div>
          </xsl:if>
        </xsl:for-each>
      </div>
      <!-- book info -->
      <h1 class="editio">
        <!--
        <xsl:call-template name="authors"/>
        -->
        <!-- auto exact link -->
        <a class="title" href="{$url}">
          <xsl:apply-templates select="dc:title"/>
        </a>
        <xsl:call-template name="editors"/>
        <xsl:for-each select="dc:date[1]">
          <xsl:text>, </xsl:text>
          <xsl:value-of select="."/>
        </xsl:for-each>
        <span class="scope">
          <xsl:variable name="scope">
            <xsl:for-each select="dcterms:isPartOf/bib:Book/prism:volume">
              <xsl:text>, </xsl:text>
              <xsl:apply-templates select="."/>
            </xsl:for-each>
            <xsl:for-each select="bib:pages">
              <xsl:text>, </xsl:text>
              <xsl:apply-templates select="."/>
            </xsl:for-each>
          </xsl:variable>
          <!-- text only, will be replaced -->
          <xsl:value-of select="$scope"/>
        </span>
        <xsl:text>.</xsl:text>
        <xsl:call-template name="urn-editio"/>
      </h1>
      
    </section>
  </xsl:template>
  
  <!-- book record for a text page -->
  <xsl:template match="bib:Book[not(bib:editors)]" mode="cartouche">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <h4 class="opus_titlemain">
      <xsl:call-template name="authors"/>
      <em class="title">
        <xsl:apply-templates select="dc:title"/>
      </em>
      <xsl:text> </xsl:text>
      
      <xsl:for-each select="z:shortTitle">
        <span class="shortTitle">
          <xsl:text>(</xsl:text>
          <em class="title">
            <xsl:apply-templates/>
          </em>
          <xsl:text>)</xsl:text>
        </span>
      </xsl:for-each>
      
      <xsl:text> </xsl:text>
      <xsl:call-template name="fichtner_link"/>
    </h4>
    <xsl:call-template name="opus_tituli"/>
  </xsl:template>
</xsl:transform>