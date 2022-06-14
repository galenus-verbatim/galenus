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
  <xsl:strip-space elements="*"/>
  <xsl:variable name="opera_ids" select="/*/z:Collection[contains(dc:title, 'opera')]/dcterms:hasPart/@rdf:resource"/>
  <xsl:variable name="verbatim_ids" select="/*/z:Collection[contains(dc:title, 'verbatim')]/dcterms:hasPart/@rdf:resource"/>
  

  
  

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
          <em class="title">
            <xsl:apply-templates select="dc:title"/>
          </em>
        </xsl:otherwise>
      </xsl:choose>
    </a>
  </xsl:template>
  
  <xsl:template match="*" mode="id" name="id">
    <xsl:choose>
      <xsl:when test="dc:identifier[contains(., 'https://galenus-verbatim.huma-num.fr/')]">
        <!-- urn:cts:greekLit: -->
        <xsl:value-of select="substring-after(normalize-space(dc:identifier), 'https://galenus-verbatim.huma-num.fr/')"/>
      </xsl:when>
      <xsl:when test="dc:identifier[contains(., 'urn:cts:greekLit:')]">
        <!-- urn:cts:greekLit: -->
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
  
  <!-- Should be an opus and not an edition -->
  <xsl:template match="bib:*" mode="opus">
    <xsl:variable name="fichtner_no" select="normalize-space(dc:subject/dcterms:LCC/rdf:value)"/>
    <!--
      Get the right url for Fichtner
      1. loop on each <link:link> element
      2. test url by prefix
    -->
    <section class="opus">
      <xsl:attribute name="id">
        <xsl:call-template name="id"/>
      </xsl:attribute>
      <h1>
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
      </h1>
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
        <xsl:variable name="title" select="normalize-space(.)"/>
        <xsl:choose>
          <xsl:when test="
               starts-with($title, '1TitGrcCMG:') 
            or starts-with($title, '2TitFrBM:')
            or starts-with($title, '3TitEnCGT:')
            or starts-with($title, '4AbbrEnCGT:')
            "/>
          <xsl:otherwise>
            <div class="note">
              <xsl:value-of select="." disable-output-escaping="yes"/>
            </div>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
      <!-- editions -->
      <xsl:for-each select="/*/bib:*[@rdf:about = $verbatim_ids][dc:subject/dcterms:LCC/rdf:value = $fichtner_no]">
        <xsl:sort select="dc:identifier/dcterms:URI/rdf:value"/>
        <div class="edition">
          <xsl:text>— </xsl:text>
          <xsl:apply-templates select="." mode="short"/>
        </div>
      </xsl:for-each>
    </section>
  </xsl:template>
  
  <!-- List alternative titles of an opus -->
  <xsl:template name="opus_tituli">
    <div ass="urn">
      <xsl:text>urn:cts:greekLit:</xsl:text>
      <xsl:value-of select="substring-after(dc:identifier/dcterms:URI/rdf:value, 'tlg')"/>
    </div>
    <xsl:variable name="short" select="z:shortTitle"/>
    <xsl:variable name="notes" select="key('about', dcterms:isReferencedBy/@rdf:resource)"/>
    <xsl:for-each select="$notes">
      <xsl:sort select="normalize-space(.)"/>
      <xsl:variable name="title" select="normalize-space(.)"/>
      <xsl:if test="
        starts-with($title, '1TitGrcCMG:') 
        or starts-with($title, '2TitFrBM:')
        or starts-with($title, '3TitEnCGT:')
        ">
        <!--
          or starts-with($title, '4AbbrEnCGT:')
          <xsl:choose>
            <xsl:when test="position() != 1"> ; </xsl:when>
            <xsl:when test="$short != ''"> ; </xsl:when>
          </xsl:choose>
          -->
        <div>
          <xsl:attribute name="class">
            <xsl:text>titletr </xsl:text>
            <xsl:value-of select="translate(normalize-space(substring-before(., ':')), '0123456789', '')"/>
          </xsl:attribute>
          <xsl:choose>
            <xsl:when test="starts-with($title, '1TitGrcCMG:')">
              <xsl:value-of select="normalize-space(substring-after($title, ':'))"/>
            </xsl:when>
            <xsl:otherwise>
              <em class="title">
                <xsl:value-of select="normalize-space(substring-after($title, ':'))"/>
              </em>
            </xsl:otherwise>
          </xsl:choose>
          <!-- Ugly hack to get english short title -->
          <xsl:if test="starts-with($title, '3TitEnCGT:')">
            <xsl:for-each select="$notes[starts-with(normalize-space(.), '4AbbrEnCGT:')]">
              <xsl:text> (</xsl:text>
              <em class="title short">
                <xsl:value-of select="normalize-space(substring-after(normalize-space(.), ':'))"/>
              </em>
              <xsl:text>)</xsl:text>
            </xsl:for-each>
          </xsl:if>
        </div>
      </xsl:if>
    </xsl:for-each>
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
  
  <!-- Should be an edition 
  
  Galenus. « Adhortatio ad artes addiscendas ». In Opera omnia, édité par Karl Gottlob Kühn, 1:1‑39. Medicorum graecorum opera quae exstant [sic] 1. Lipsiae: in officina C. Cnoblochii, 1821. urn:cts:greekLit:tlg0057.tlg001.1st1K-grc1.

Galenus. « Protrepticus ». édité par Georg Kaibel, 1‑22, 1894. urn:cts:greekLit:tlg0057.tlg001.1st1K-grc2.
  -->
  <xsl:template match="bib:*" mode="short">
    <xsl:variable name="url" select="dc:identifier/dcterms:URI/rdf:value"/>
    <xsl:for-each select="dc:title">
      <xsl:choose>
        <xsl:when test="$url != ''">
          <a class="title opus">
            <xsl:attribute name="href">
              <xsl:value-of select="substring-after($url, 'galenus-verbatim.huma-num.fr/')"/>
            </xsl:attribute>
            <xsl:apply-templates/>
          </a>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
      <!--
      <xsl:if test="position() = last()">. </xsl:if>
      -->
    </xsl:for-each>
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
    <xsl:text> </xsl:text>
    <span class="urn">
      <xsl:text>urn:cts:greekLit:</xsl:text>
      <xsl:value-of select="substring-after($url, 'galenus-verbatim.huma-num.fr/')"/>
    </span>
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

  <xsl:template match="bib:editors//foaf:Person">
    <xsl:value-of select="foaf:surname"/>
  </xsl:template>
  
  
  <xsl:template match="z:shortTitle">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="z:Attachment">
    <xsl:value-of select="normalize-space(dc:identifier)"/>
  </xsl:template>
  
  <xsl:template match="dc:title">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="bib:Memo">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="bib:Memo/rdf:value">
    <xsl:choose>
      <xsl:when test="starts-with(., '1TitGrcCMG:')">
        <xsl:value-of select="normalize-space(substring-after(., '1TitGrcCMG:'))" disable-output-escaping="yes"/>
      </xsl:when>
      <xsl:when test="starts-with(., '2TitFrBM:')">
        <xsl:value-of select="normalize-space(substring-after(., '2TitFrBM:'))" disable-output-escaping="yes"/>
      </xsl:when>
      <xsl:when test="starts-with(., '3TitEnCGT:')">
        <xsl:value-of select="normalize-space(substring-after(., '3TitEnCGT:'))" disable-output-escaping="yes"/>
      </xsl:when>
      <xsl:when test="starts-with(., '4AbbrEnCGT:')">
        <xsl:value-of select="normalize-space(substring-after(., '4AbbrEnCGT:'))" disable-output-escaping="yes"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="." disable-output-escaping="yes"/>
      </xsl:otherwise>
    </xsl:choose>
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