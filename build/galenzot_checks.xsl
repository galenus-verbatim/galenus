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

  <xsl:template match="/">
    <xsl:variable name="opera_ids" select="/*/z:Collection[contains(dc:title, 'opera')]/dcterms:hasPart/@rdf:resource"/>
    <article>
      <section>
        <h1>“Galeni et Pseudo-Galeni opera” sans tag _opus</h1>
        <xsl:for-each select="/*/bib:*[@rdf:about = $opera_ids]">
          <!-- Galenus first -->
          <xsl:sort select=".//foaf:surname"/>
          <xsl:sort select="dc:subject/dcterms:LCC/rdf:value"/>
          <xsl:if test="not(dc:subject = '_opus')">
            <div>
              <b>
                <xsl:value-of select="dc:subject/dcterms:LCC/rdf:value"/>
              </b>
              <xsl:text>. </xsl:text>
              <xsl:value-of select=".//foaf:surname"/>
              <xsl:text>, </xsl:text>
              <xsl:value-of select="dc:title" disable-output-escaping="yes"/>
              <xsl:text> : </xsl:text>
              <xsl:for-each select="dc:subject">
                <xsl:choose>
                  <xsl:when test="position() = 1"/>
                  <xsl:otherwise>, </xsl:otherwise>
                </xsl:choose>
                <xsl:choose>
                  <xsl:when test=". = '_opus'">
                    <b style="color: red">
                      <xsl:value-of select="normalize-space(.)"/>
                    </b>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="normalize-space(.)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:for-each>
              <xsl:text>.</xsl:text>
            </div>
          </xsl:if>
        </xsl:for-each>
      </section>
      <section>
        <h1>Opus avec titres traduits manquants</h1>
        <xsl:for-each select="/*/bib:*[dc:subject = '_opus']">
          <xsl:sort select=".//foaf:surname"/>
          <xsl:sort select="dc:subject/dcterms:LCC/rdf:value"/>
          <!--
<dc:description>
<z:original-title>Περὶ νευρῶν ἀνατομῆς</z:original-title>
<z:french-title>Anatomie des nerfs</z:french-title>
<z:english-title>The Anatomy of the Nerves</z:english-title>
<z:english-short-title>Nerv. Diss.</z:english-short-title>
<z:cts-urn>urn:cts:greekLit:tlg0057.tlg014</z:cts-urn>
</dc:description>
          -->
          <xsl:variable name="lacks">
            <xsl:if test="not(z:shortTitle) or z:shortTitle = ''"><br/>pas de titre latin abrégé</xsl:if>
            <xsl:if test="not(dc:description/z:original-title) or dc:description/z:original-title = ''"><br/>pas de titre grec</xsl:if>
            <xsl:if test="not(dc:description/z:french-title) or dc:description/z:french-title = ''"><br/>pas de titre français</xsl:if>
            <xsl:if test="not(dc:description/z:english-title) or dc:description/z:english-title = ''"><br/>pas de titre anglais</xsl:if>
            <xsl:if test="not(dc:description/z:english-short-title) or dc:description/z:english-short-title = ''"><br/>pas de titre anglais abrégé</xsl:if>
          </xsl:variable>
          <xsl:if test="$lacks != ''">
            <p>
              <b>
                <xsl:value-of select="dc:subject/dcterms:LCC/rdf:value"/>
              </b>
              <xsl:text>. </xsl:text>
              <xsl:value-of select=".//foaf:surname"/>
              <xsl:text>, </xsl:text>
              <xsl:value-of select="dc:title" disable-output-escaping="yes"/>
              <xsl:copy-of select="$lacks"/>
            </p>
          </xsl:if>
        </xsl:for-each>
      </section>
    </article>
  </xsl:template>
  
  
</xsl:transform>
