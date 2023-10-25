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
  <xsl:variable name="idfrom">ABCDEFGHIJKLMNOPQRSTUVWXYZÀÂÄÉÈÊÏÎÔÖÛÜÇàâäéèêëïîöôüû</xsl:variable>
  <xsl:variable name="idto"  >abcdefghijklmnopqrstuvwxyzaaaeeeiioouucaaaeeeeiioouu</xsl:variable>
  
  
  <xsl:key name="about" match="*[@rdf:about]" use="@rdf:about"/>
  <xsl:key name="opus"
    match="/*/bib:*[dc:subject = '_opus']"
    use="normalize-space(dc:subject/dcterms:LCC/rdf:value)"
  />
  <xsl:key name="verbatim_grc" 
    match="/*/bib:*[dc:subject = '_verbatim' and dc:subject = '_grc']"
    use="normalize-space(dc:subject/dcterms:LCC/rdf:value)"
  />
  <xsl:key name="verbatim_lat" 
    match="/*/bib:*[dc:subject = '_verbatim' and dc:subject = '_lat']"
    use="normalize-space(dc:subject/dcterms:LCC/rdf:value)"
  />
  <xsl:key name="edcrit" 
    match="/*/bib:*[dc:subject = '_edcrit']"
    use="normalize-space(dc:subject/dcterms:LCC/rdf:value)"
  />
  <xsl:key name="transl" 
    match="/*/bib:*[dc:subject = '_transl']"
    use="normalize-space(dc:subject/dcterms:LCC/rdf:value)"
  />
  
  <xsl:strip-space elements="*"/>


  <xsl:template match="bib:BookSection" mode="kuhn">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <xsl:variable name="opus" select="/*/bib:Book[not(bib:editors)][dc:subject/dcterms:LCC/rdf:value = $fichtner_no]"/>
    <a>
      <xsl:attribute name="href">
        <xsl:text>#</xsl:text>
        <xsl:apply-templates select="$opus" mode="id"/>
      </xsl:attribute>
      <xsl:attribute name="title">
        <xsl:value-of select="normalize-space(dc:title)"/>
      </xsl:attribute>
      <b>
        <xsl:value-of select=".//prism:volume"/>
        <xsl:text>.</xsl:text>
        <xsl:value-of select="substring-before(concat(.//bib:pages, '-'), '-')"/>
      </b>
      <xsl:text> </xsl:text>
      <small class="fichtner">
        <xsl:text>[</xsl:text>
        <xsl:value-of select="translate($fichtner_no, 'abcdefgh', '')"/>
        <xsl:text> Ficht.]</xsl:text>
      </small>
      <xsl:text> </xsl:text>
      <xsl:choose>
        <xsl:when test="false()">
          <xsl:apply-templates select="$opus/z:shortTitle"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="dc:title"/>
        </xsl:otherwise>
      </xsl:choose>
    </a>
  </xsl:template>
  
  <xsl:template match="*" mode="id" name="id">
    <xsl:choose>
      <!--
      <xsl:when test="dc:identifier[contains(., 'https://galenus-verbatim.huma-num.fr/#')]">
        <xsl:value-of select="substring-after(normalize-space(dc:identifier), 'https://galenus-verbatim.huma-num.fr/')"/>
      </xsl:when>
      -->
      <xsl:when test="dc:identifier[contains(., 'urn:cts:greekLit:')]">
        <xsl:text>urn:cts:greekLit:</xsl:text>
        <xsl:value-of select="substring-after(normalize-space(dc:identifier), 'urn:cts:greekLit:')"/>
      </xsl:when>
      <xsl:when test="@rdf:about">
        <xsl:value-of select="translate(@rdf:about, '#', '')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="generate-id()"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="fichtner_link">
    <xsl:variable name="fichtner_no" select="translate(normalize-space(dc:subject/dcterms:LCC/rdf:value), 'abcdefghijk', '')"/>
    <a target="_blank" rel="noopener" class="fichtner external">
      <xsl:attribute name="href">
        <xsl:text>https://cmg.bbaw.de/epubl/online/Bibl/Galen-Bibliographie_</xsl:text>
        <xsl:value-of select="$fichtner_no"/>
        <xsl:text>.pdf</xsl:text>
      </xsl:attribute>
      <xsl:text>[n° </xsl:text>
      <xsl:value-of select="$fichtner_no"/>
      <xsl:text> Fichtner]</xsl:text>
    </a>
    
  </xsl:template>

  <xsl:template name="gallat_link">
    <xsl:for-each select="link:link">
      <xsl:for-each select="key('about', @rdf:resource)">
        <xsl:if test="contains(dc:title, 'Latino')">
          <a target="_blank" rel="noopener" class="gallat external">
            <xsl:attribute name="href">
              <xsl:value-of select="normalize-space(dc:identifier/dcterms:URI/rdf:value)"/>
            </xsl:attribute>
            <xsl:text>[GalLat]</xsl:text>
          </a>
        </xsl:if>
      </xsl:for-each>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="class">
    <xsl:param name="class"/>
    <xsl:variable name="classes">
      <xsl:value-of select="$class"/>
      <xsl:for-each select="dc:subject">
        <xsl:variable name="value" select="normalize-space(.)"/>
        <xsl:choose>
          <xsl:when test="dcterms:LCC">
            <xsl:text> fichtner</xsl:text>
            <xsl:value-of select="$value"/>
          </xsl:when>
          <xsl:when test="starts-with($value, '_')">
            <xsl:text> </xsl:text>
            <xsl:value-of select="substring-after($value, '_')"/>
          </xsl:when>
        </xsl:choose>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="normalize-space($classes) != ''">
      <xsl:attribute name="class">
        <xsl:value-of select="normalize-space($classes)"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <!-- Should be an opus and not an edition -->
  <xsl:template match="bib:*" mode="opus">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <!--
      Get the right url for Fichtner
      1. loop on each <link:link> element
      2. test url by prefix
    -->
    <section>
      <xsl:attribute name="id">
        <xsl:call-template name="id"/>
      </xsl:attribute>
      <xsl:call-template name="class">
        <xsl:with-param name="class">opus</xsl:with-param>
      </xsl:call-template>
      <h1>
        <xsl:call-template name="authors"/>
        <xsl:apply-templates select="dc:title"/>
        <xsl:variable name="title" select="normalize-space(dc:title)"/>
        <xsl:for-each select="z:shortTitle">
          <xsl:if test="normalize-space(.) != $title">
            <xsl:text> </xsl:text>
            <span class="shortTitle">
              <xsl:text>(</xsl:text>
              <em class="title">
                <xsl:apply-templates/>
              </em>
              <xsl:text>)</xsl:text>
            </span>
          </xsl:if>
        </xsl:for-each>
        
        <xsl:text> </xsl:text>
        <xsl:call-template name="fichtner_link"/>
        <xsl:text> </xsl:text>
        <xsl:call-template name="gallat_link"/>
      </h1>
      <xsl:variable name="urn" select="substring-after(dc:identifier/dcterms:URI/rdf:value, 'urn:cts:')"/>
      <xsl:if test="$urn != ''">
        <div class="urn">
          <xsl:text>urn:cts:</xsl:text>
          <xsl:value-of select="$urn"/>
        </div>
      </xsl:if>
      <xsl:variable name="tituli">
        <xsl:call-template name="opus_tituli"/>
      </xsl:variable>
      <xsl:if test="$tituli != ''">
        <div class="tituli">
          <xsl:copy-of select="$tituli"/>
        </div>
      </xsl:if>
      <!-- Notes -->
      <xsl:for-each select="key('about', dcterms:isReferencedBy/@rdf:resource)">
        <div class="note">
          <!-- Why @eacute ?
          <xsl:value-of select="." disable-output-escaping="yes"/>
          -->
          <xsl:value-of select="." disable-output-escaping="yes"/>
        </div>
      </xsl:for-each>
      <!-- editio grc -->
      <xsl:for-each select="key('verbatim_grc', $fichtner_no)">
        <!-- <xsl:sort select="dc:identifier/dcterms:URI/rdf:value"/> -->
        <xsl:sort select="dc:date"/>
        <xsl:apply-templates select="." mode="short"/>
      </xsl:for-each>
      <xsl:call-template name="edcrit"/>
      <!-- editio lat -->
      <xsl:variable name="verbatim_lat">
        <xsl:for-each select="key('verbatim_lat', $fichtner_no)">
          <!-- <xsl:sort select="dc:identifier/dcterms:URI/rdf:value"/> -->
          <xsl:sort select="dc:date"/>
          <xsl:apply-templates select="." mode="short"/>
        </xsl:for-each>
      </xsl:variable>
      <xsl:if test="$verbatim_lat != ''">
          <label class="editio">translatio Latina: </label>
          <xsl:copy-of select="$verbatim_lat"/>
      </xsl:if>
      <xsl:call-template name="transl"/>
    </section>
  </xsl:template>
  
  <xsl:template name="edcrit">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <xsl:variable name="edcrit">
      <xsl:for-each select="key('edcrit', $fichtner_no)">
        <!-- <xsl:sort select="dc:identifier/dcterms:URI/rdf:value"/> -->
        <xsl:sort select="dc:date"/>
        <xsl:apply-templates select="." mode="edcrit"/>
        <xsl:if test="position() != last()">; </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="$edcrit != ''">
      <div class="editio critica">
        <label>editio critica: </label>
        <xsl:copy-of select="$edcrit"/>
        <xsl:text>.</xsl:text>
      </div>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="transl">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <xsl:variable name="transl">
      <xsl:for-each select="key('transl', $fichtner_no)">
        <!-- <xsl:sort select="dc:identifier/dcterms:URI/rdf:value"/> -->
        <xsl:sort select="dc:date"/>
        <xsl:apply-templates select="." mode="transl"/>
        <xsl:if test="position() != last()">; </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="$transl != ''">
      <div class="editio critica">
        <label>translationes recentiores: </label>
        <xsl:copy-of select="$transl"/>
        <xsl:text>.</xsl:text>
      </div>
    </xsl:if>
  </xsl:template>
  
  <!-- Alt titles in extra field -->
  <xsl:template match="z:original-title | z:french-title | z:english-title">
    <xsl:if test="normalize-space(.) != ''">
      <div class="titletr {local-name()}">
        <em class="title">
          <xsl:value-of select="." disable-output-escaping="yes"/>
        </em>
        <xsl:if test="self::z:english-title and ../z:english-short-title">
          <xsl:text> (</xsl:text>
          <em class="title short">
            <xsl:value-of select="../z:english-short-title"/>
          </em>
          <xsl:text>)</xsl:text>
        </xsl:if>
      </div>
    </xsl:if>
  </xsl:template>
  
  <!-- List alternative titles of an opus -->
  <xsl:template name="opus_tituli">
    <xsl:variable name="short" select="z:shortTitle"/>
    <xsl:variable name="notes" select="key('about', dcterms:isReferencedBy/@rdf:resource)"/>
    <xsl:apply-templates select="dc:description/z:original-title"/>
    <xsl:apply-templates select="dc:description/z:french-title"/>
    <xsl:apply-templates select="dc:description/z:english-title"/>
  </xsl:template>
  
  
  <xsl:template name="authors">
    <span class="authors">
      <xsl:for-each select="bib:authors/rdf:Seq/rdf:li">
        <xsl:apply-templates select="*"/>
        <xsl:choose>
          <xsl:when test="position() = last()">.</xsl:when>
          <xsl:otherwise> ; </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </span>
    <xsl:text> </xsl:text>
  </xsl:template>

  <!-- For _transl and _edcrit -->
  <xsl:template match="bib:*" mode="edcrit">
    <xsl:if test="bib:editors">
      <xsl:variable name="count" select="count(bib:editors/rdf:Seq/rdf:li)"/>
      <xsl:for-each select="bib:editors/rdf:Seq/rdf:li">
        <!-- foaf:Person -->
        <xsl:apply-templates select="foaf:Person"/>
        <xsl:if test="position() != last()">, </xsl:if>
      </xsl:for-each>
    </xsl:if>
      
    <xsl:for-each select="dc:date[1]">
      <xsl:text>, </xsl:text>
      <xsl:value-of select="."/>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="bib:*" mode="transl">
    <xsl:if test="z:translators">
      <xsl:for-each select="z:translators/rdf:Seq/rdf:li">
        <xsl:apply-templates select="foaf:Person"/>
        <xsl:if test="position() != last()">, </xsl:if>
      </xsl:for-each>
    </xsl:if>
    <xsl:for-each select="dc:date[1]">
      <xsl:text>, </xsl:text>
      <xsl:value-of select="."/>
    </xsl:for-each>
    <xsl:text> (</xsl:text>
    <xsl:choose>
      <xsl:when test="contains(z:language, 'grc;')">
        <xsl:value-of select="substring-after(z:language, 'grc;')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="z:language"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>)</xsl:text>
  </xsl:template>
  
  <!-- Should be an edition 
  Galenus. « Adhortatio ad artes addiscendas ». In Opera omnia, édité par Karl Gottlob Kühn, 1:1‑39. Medicorum graecorum opera quae exstant [sic] 1. Lipsiae: in officina C. Cnoblochii, 1821. urn:cts:greekLit:tlg0057.tlg001.1st1K-grc1.

Galenus. « Protrepticus ». édité par Georg Kaibel, 1‑22, 1894. urn:cts:greekLit:tlg0057.tlg001.1st1K-grc2.
  -->
  <xsl:template match="bib:*" mode="short">
    <xsl:param name="label"/>
    <xsl:variable name="url">
      <xsl:choose>
        <xsl:when test="dc:subject = '_legendum'"/>
        <xsl:otherwise>
          <xsl:value-of select="dc:identifier/dcterms:URI/rdf:value"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="el">
      <xsl:choose>
        <xsl:when test="$url = ''">div</xsl:when>
        <xsl:otherwise>a</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$el}">
      <xsl:choose>
        <xsl:when test="$url = ''"/>
        <xsl:when test="contains($url, 'galenus-verbatim.huma-num.fr/')">
          <xsl:attribute name="class">editio verbatim</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:text>./</xsl:text>
            <xsl:value-of select="substring-after($url, 'galenus-verbatim.huma-num.fr/')"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:attribute name="class">editio external</xsl:attribute>
          <xsl:attribute name="target">_blank</xsl:attribute>
          <xsl:attribute name="rel">noopener</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:value-of select="$url"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:copy-of select="$label"/>
      <xsl:apply-templates select="dc:title"/>
    <!-- No
    <xsl:for-each select="dcterms:isPartOf/bib:Book/dc:title[normalize-space(.) != '']">
      <xsl:if test="position() = 1">
        <xsl:text>. </xsl:text>
        <i>In</i>
        <xsl:text> </xsl:text>
      </xsl:if>
      <em class="title">
        <xsl:apply-templates/>
      </em>
    </xsl:for-each>
    -->
      <xsl:call-template name="editors"/>
      <xsl:for-each select="dc:date[1]">
        <xsl:text>, </xsl:text>
        <xsl:value-of select="."/>
      </xsl:for-each>
      <xsl:for-each select="dcterms:isPartOf/bib:Book/prism:volume">
        <xsl:text>, </xsl:text>
        <xsl:apply-templates select="."/>
      </xsl:for-each>
      <xsl:for-each select="bib:pages">
        <xsl:text>, </xsl:text>
        <xsl:apply-templates select="."/>
      </xsl:for-each>
      <xsl:text>.</xsl:text>
      <xsl:call-template name="urn-editio"/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="urn-editio">
    <xsl:variable name="urn" select="substring-after(dc:identifier/dcterms:URI/rdf:value, 'urn:cts:greekLit:')"/>
    <xsl:if test="$urn != ''">
      <xsl:text> </xsl:text>
      <span class="urn">
        <xsl:text>urn:cts:greekLit:</xsl:text>
        <!--
        <xsl:choose>
          <xsl:when test="contains($urn, '.1st1K')">
            <xsl:value-of select="substring-before($urn, '.1st1K')"/>
            <xsl:text>.</xsl:text>
            <b class="publisher">1st1K</b>
            <xsl:value-of select="substring-after($urn, '.1st1K')"/>
          </xsl:when>
          <xsl:when test="contains($urn, '.verbatim')">
            <xsl:value-of select="substring-before($urn, '.verbatim')"/>
            <xsl:text>.</xsl:text>
            <b class="publisher">verbatim</b>
            <xsl:value-of select="substring-after($urn, '.verbatim')"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$urn"/>
          </xsl:otherwise>
        </xsl:choose>
        -->
        <xsl:value-of select="$urn"/>
      </span>
    </xsl:if>    
  </xsl:template>
  
  <xsl:template name="editors">
    <xsl:if test="bib:editors">
      <span class="editors">
        <xsl:if test="position() = 1">, ed. </xsl:if>
        <xsl:variable name="count" select="count(bib:editors/rdf:Seq/rdf:li)"/>
        <xsl:for-each select="bib:editors/rdf:Seq/rdf:li">
          <!-- foaf:Person -->
          <xsl:apply-templates select="*"/>
          <xsl:choose>
            <xsl:when test="position() = last()"/>
            <xsl:when test="position() = (last() - 1)"> et </xsl:when>
            <xsl:otherwise>, </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </span>
    </xsl:if>
    
  </xsl:template>
  
  <xsl:template match="prism:volume">
    <span class="volume">
      <xsl:text>vol. </xsl:text>
      <xsl:value-of select="."/>
    </span>
  </xsl:template>
  
  <xsl:template match="bib:pages">
    <span class="pages">
      <xsl:text>p. </xsl:text>
      <xsl:value-of select="."/>
    </span>
  </xsl:template>
  
  <xsl:template match="bib:authors">
    <xsl:apply-templates select="rdf:Seq/rdf:li/*"/>
  </xsl:template>
  
  <xsl:template match="bib:authors//foaf:Person">
    <xsl:value-of select="foaf:surname"/>
    <xsl:if test="foaf:surname != '' and foaf:givenName != ''">, </xsl:if>
    <xsl:value-of select="foaf:givenName"/>
  </xsl:template>

  <xsl:template match="bib:editors//foaf:Person | z:translators//foaf:Person">
    <xsl:value-of select="foaf:surname"/>
  </xsl:template>
  
  <xsl:template match="z:shortTitle">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="z:Attachment">
    <xsl:value-of select="normalize-space(dc:identifier)"/>
  </xsl:template>
  
  <xsl:template match="dc:title">
    <em class="title">
      <xsl:apply-templates/>
    </em>
  </xsl:template>
  
  <xsl:template match="bib:Memo">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="bib:Memo/rdf:value">
    <xsl:value-of select="." disable-output-escaping="yes"/>
  </xsl:template>
  
  <xsl:template match="*">
    <b>
      <xsl:text>&lt;</xsl:text>
      <xsl:value-of select="name()"/>
      <xsl:text>&gt;</xsl:text>
    </b>
    <xsl:apply-templates/>
    <b>
      <xsl:text>&lt;/</xsl:text>
      <xsl:value-of select="name()"/>
      <xsl:text>&gt;</xsl:text>
    </b>
  </xsl:template>
  
</xsl:transform>