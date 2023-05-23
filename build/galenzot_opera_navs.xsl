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
      <xsl:call-template name="titLat"/>
      <xsl:call-template name="fichtner"/>
      <xsl:call-template name="kuhn"/>
      <xsl:call-template name="titLatAbbr"/>
      <xsl:call-template name="titGrc"/>
      <xsl:call-template name="titFra"/>
      <xsl:call-template name="titEng"/>
      <xsl:call-template name="titEngAbbr"/>
    </div>
  </xsl:template>
  
  
  <xsl:template name="fichtner">
    <nav id="fichtner" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
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
  
  <xsl:template name="kuhn">
    <nav id="kuhn" style="display:none" class="bibl">
      <xsl:apply-templates select="/*/bib:*[bib:editors[contains(., 'Kühn')]]" mode="kuhn">
        <!-- 18a, 18b grrrr. -->
        <xsl:sort select="translate(substring-before(concat(.//prism:volume, '-'), '-'), 'ab', '01')" data-type="number" />
        <xsl:sort select="substring-before(concat(.//bib:pages, '-'), '-')" data-type="number"/>
      </xsl:apply-templates>
    </nav>
  </xsl:template>
  
  
  <xsl:template name="titLat">
    <nav id="titLat" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
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
  
  <xsl:template name="titLatAbbr">
    <nav id="titLatAbbr" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
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
  
  
  <xsl:template name="titGrc">
    <nav id="titGrc" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
        <xsl:sort select="normalize-space(dc:description/z:original-title)"/>
        <xsl:variable name="value" select="normalize-space(dc:description/z:original-title)"/>
        <xsl:if test="$value != ''">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:apply-templates select="." mode="id"/>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:value-of select="$value"/>
            </xsl:attribute>
            <em>
              <xsl:value-of select="$value"/>
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
  
  <xsl:template name="titFra">
    <nav id="titFra" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
        <xsl:sort select="normalize-space(dc:description/z:french-title)"/>
        <xsl:variable name="value" select="normalize-space(dc:description/z:french-title)"/>
        <xsl:if test="$value != ''">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:apply-templates select="." mode="id"/>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:value-of select="$value"/>
            </xsl:attribute>
            <em>
              <xsl:value-of select="$value"/>
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
  
  <xsl:template name="titEng">
    <nav id="titEng" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
        <xsl:sort select="normalize-space(dc:description/z:english-title)"/>
        <xsl:variable name="value" select="normalize-space(dc:description/z:english-title)"/>
        <xsl:if test="$value != ''">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:apply-templates select="." mode="id"/>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:value-of select="$value"/>
            </xsl:attribute>
            <em>
              <xsl:value-of select="$value"/>
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
  
  <xsl:template name="titEngAbbr">
    <nav id="titEngAbbr" style="display:none" class="bibl">
      <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
        <xsl:sort select="normalize-space(dc:description/z:english-short-title)"/>
        <xsl:variable name="value" select="normalize-space(dc:description/z:english-short-title)"/>
        <xsl:if test="$value != ''">
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:apply-templates select="." mode="id"/>
            </xsl:attribute>
            <xsl:attribute name="title">
              <xsl:value-of select="$value"/>
            </xsl:attribute>
            <em>
              <xsl:value-of select="$value"/>
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
  
</xsl:transform>