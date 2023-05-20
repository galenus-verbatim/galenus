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
  <xsl:output method="xml" indent="yes" encoding="UTF-8" omit-xml-declaration="yes"/>
  <xsl:key name="about" match="*[@rdf:about]" use="@rdf:about"/>

  
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  
  
  <xsl:template match="bib:*[dc:subject = '_opera']">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates select="node()[not(self::dc:description)]"/>
      <dc:description>
        <xsl:variable name="url" select="normalize-space(dc:identifier)"/>
        <xsl:variable name="cts" select="substring-after($url, '#')"/>
        <xsl:if test="$url = ''">
          <xsl:message>
            <xsl:value-of select="@rdf:about"/>
            <xsl:text> identifier ?</xsl:text>
          </xsl:message>
        </xsl:if>
        <xsl:value-of select="dc:description"/>
        <xsl:text>&#10;CTS URN: </xsl:text>
        <xsl:value-of select="$cts"/>
        <xsl:text>&#10;</xsl:text>
      </dc:description>
    </xsl:copy>
  </xsl:template>
    <xsl:template match="bib:*[dc:subject = '_verbatim']">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates select="node()[not(self::dc:description)]"/>
      <dc:description>
        <xsl:variable name="url" select="normalize-space(dc:identifier)"/>
        <xsl:variable name="cts" select="substring-after($url, 'galenus-verbatim.huma-num.fr/')"/>
        <xsl:choose>
          <xsl:when test="$url = '' or $cts = ''">
            <xsl:message>
              <xsl:value-of select="@rdf:about"/>
              <xsl:text> identifier ?</xsl:text>
            </xsl:message>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>CTS URN: </xsl:text>
            <xsl:value-of select="$cts"/>
            <xsl:if test="normalize-space(dc:description) != ''">
              <xsl:text>&#10;&#10;</xsl:text>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="normalize-space(dc:description) != ''">
          <xsl:value-of select="dc:description"/>
          <xsl:text>&#10;</xsl:text>
        </xsl:if>
      </dc:description>
    </xsl:copy>
  </xsl:template>
  <!--
  <xsl:template match="/*/z:Attachment">
    <xsl:variable name="url" select="normalize-space(dc:identifier)"/>
    <xsl:choose>
      <xsl:when test="contains($url, 'galenus-verbatim')">
        <xsl:variable name="about" select="@rdf:about"/>
        <xsl:for-each select="/*/*[link:link[@rdf:resource= $about]]">
          <xsl:variable name="identifier" select="normalize-space(dc:identifier)"/>
            <xsl:if test="$identifier != $url">
            <xsl:value-of select="$about"/>
            <xsl:text>&#9;</xsl:text>
            <xsl:value-of select="$url"/>
            <xsl:text>&#9;!!</xsl:text>
            <xsl:value-of select="$identifier"/>
            <xsl:text>&#10;</xsl:text>
          </xsl:if>
        </xsl:for-each>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  -->

  <!--
  <xsl:template match="bib:*[dc:subject = '_opera']">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates select="node()[not(self::dc:description)]"/>
      <dc:description>
        <xsl:variable name="notes" select="key('about', dcterms:isReferencedBy/@rdf:resource)"/>
        <xsl:for-each select="$notes">
          <xsl:sort select="normalize-space(.)"/>
          <xsl:if test="contains(rdf:value, '1TitGrcCMG') 
    or contains(rdf:value, '2TitFrBM') 
    or contains(rdf:value, '3TitEnCGT') 
    or contains(rdf:value, '4AbbrEnCGT')">
            <xsl:copy-of select="normalize-space(.)"/>
            <xsl:text>&#10;</xsl:text>
          </xsl:if>
        </xsl:for-each>
        <xsl:if test="dc:description != ''">
          <xsl:text>&#10;&#10;</xsl:text>
          <xsl:value-of select="dc:description"/>
        </xsl:if>
      </dc:description>
    </xsl:copy>
  </xsl:template>
  
  <xsl:template match="bib:Memo[
    contains(rdf:value, '1TitGrcCMG') 
    or contains(rdf:value, '2TitFrBM') 
    or contains(rdf:value, '3TitEnCGT') 
    or contains(rdf:value, '4AbbrEnCGT') 
    ]"/>
  -->
</xsl:transform>
