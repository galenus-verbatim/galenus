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
  <xsl:output method="xml" indent="yes" encoding="UTF-8" omit-xml-declaration="yes"/>
  <xsl:include href="galenzot_html.xsl"/>

  <!-- 
    
    <z:Collection rdf:about="opera">
        <dc:title>Galeni et Pseudo-Galeni opera</dc:title>
        <dcterms:hasPart rdf:resource="https://galenus-verbatim.huma-num.fr/tlg0530.tlg012"/>
   -->


  <xsl:template match="/">
    <div>
      <xsl:call-template name="nav_TitLa"/>
      <xsl:call-template name="nav_fichtner"/>
      <xsl:call-template name="nav_kuhn"/>
      <xsl:call-template name="nav_AbbrLa"/>
      <xsl:call-template name="nav_TitGrcCMG"/>
      <xsl:call-template name="nav_TitFrBM"/>
      <xsl:call-template name="nav_TitEnCGT"/>
      <xsl:call-template name="nav_AbbrEnCGT"/>
    </div>
  </xsl:template>
  
  
  <xsl:template name="nav_fichtner">
    <nav id="fichtner" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[@rdf:about = $opera_ids]">
        <xsl:sort select="dc:subject/dcterms:LCC/rdf:value"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="." mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space(dc:title)"/>
          </xsl:attribute>
          <b>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </b>
          <xsl:text> </xsl:text>
          <em class="title">
            <xsl:value-of select="normalize-space(dc:title)"/>
          </em>
        </a>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_kuhn">
    <nav id="kuhn" style="display:none" class="bibl">
      <xsl:apply-templates select="/*/bib:*[bib:editors[contains(., 'Kühn')]]" mode="kuhn">
        <!-- 18a, 18b grrrr. -->
        <xsl:sort select="translate(substring-before(concat(.//prism:volume, '-'), '-'), 'ab', '01')" data-type="number" />
        <xsl:sort select="substring-before(concat(.//bib:pages, '-'), '-')" data-type="number"/>
      </xsl:apply-templates>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_TitLa">
    <nav id="TitLa" class="bibl">
      <xsl:for-each select="/*/bib:*[@rdf:about = $opera_ids]">
        <xsl:sort select="normalize-space(dc:title)"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="." mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space(dc:title)"/>
          </xsl:attribute>
          <em>
            <xsl:value-of select="normalize-space(dc:title)"/>
          </em>
          <xsl:text> </xsl:text>
          <small>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </small>
        </a>
      </xsl:for-each>
    </nav>
    
  </xsl:template>
  
  <xsl:template name="nav_AbbrLa">
    <nav id="AbbrLa" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[@rdf:about = $opera_ids]">
        <xsl:sort select="normalize-space(z:shortTitle)"/>
        <xsl:if test="normalize-space(z:shortTitle) != ''">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:apply-templates select="." mode="id"/>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:value-of select="normalize-space(dc:title)"/>
            </xsl:attribute>
            <em>
              <xsl:value-of select="normalize-space(z:shortTitle)"/>
            </em>
            <xsl:text> </xsl:text>
            <small>
              <xsl:text>[</xsl:text>
              <xsl:value-of select="dc:subject/dcterms:LCC/rdf:value"/>
              <xsl:text>]</xsl:text>
            </small>
          </a>
        </xsl:if>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_TitGrcCMG">
    <nav id="TitGrcCMG" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:Memo[starts-with(normalize-space(rdf:value), '1TitGrcCMG')]">
        <xsl:sort select="normalize-space(rdf:value)"/>
        <xsl:variable name="about" select="@rdf:about"/>
        <xsl:variable name="opus" select="/*/bib:*[dcterms:isReferencedBy/@rdf:resource=$about]"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="$opus" mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space($opus/dc:title)"/>
          </xsl:attribute>
          <em>
            <xsl:value-of select="normalize-space(substring-after(., ':'))"/>
          </em>
          <xsl:text> </xsl:text>
          <small>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="$opus/dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </small>
        </a>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_TitFrBM">
    <nav id="TitFrBM" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:Memo[starts-with(normalize-space(rdf:value), '2TitFrBM')]">
        <xsl:sort select="translate(normalize-space(rdf:value), $idfrom, $idto)"/>
        <xsl:variable name="about" select="@rdf:about"/>
        <xsl:variable name="opus" select="/*/bib:*[dcterms:isReferencedBy/@rdf:resource=$about]"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="$opus" mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space($opus/dc:title)"/>
          </xsl:attribute>
          <em>
            <xsl:value-of select="normalize-space(substring-after(., ':'))"/>
          </em>
          <xsl:text> </xsl:text>
          <small>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="$opus/dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </small>
        </a>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_TitEnCGT">
    <nav id="TitEnCGT" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:Memo[starts-with(normalize-space(rdf:value), '3TitEnCGT')]">
        <xsl:sort select="normalize-space(rdf:value)"/>
        <xsl:variable name="about" select="@rdf:about"/>
        <xsl:variable name="opus" select="/*/bib:*[dcterms:isReferencedBy/@rdf:resource=$about]"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="$opus" mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space($opus/dc:title)"/>
          </xsl:attribute>
          <em>
            <xsl:value-of select="normalize-space(substring-after(., ':'))"/>
          </em>
          <xsl:text> </xsl:text>
          <small>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="$opus/dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </small>
        </a>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
  <xsl:template name="nav_AbbrEnCGT">
    <nav id="AbbrEnCGT" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:Memo[starts-with(normalize-space(rdf:value), '4AbbrEnCGT')]">
        <xsl:sort select="normalize-space(rdf:value)"/>
        <xsl:variable name="about" select="@rdf:about"/>
        <xsl:variable name="opus" select="/*/bib:*[dcterms:isReferencedBy/@rdf:resource=$about]"/>
        <a>
          <xsl:attribute name="href">
            <xsl:text>#</xsl:text>
            <xsl:apply-templates select="$opus" mode="id"/>
          </xsl:attribute>
          <xsl:attribute name="title">
            <xsl:value-of select="normalize-space($opus/dc:title)"/>
          </xsl:attribute>
          <em>
            <xsl:value-of select="normalize-space(substring-after(., ':'))"/>
          </em>
          <xsl:text> </xsl:text>
          <small>
            <xsl:text>[</xsl:text>
            <xsl:value-of select="$opus/dc:subject/dcterms:LCC/rdf:value"/>
            <xsl:text>]</xsl:text>
          </small>
        </a>
      </xsl:for-each>
    </nav>
  </xsl:template>
  
</xsl:transform>