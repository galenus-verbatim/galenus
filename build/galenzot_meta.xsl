<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0"
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
  <xsl:output method="text" indent="yes" encoding="UTF-8" omit-xml-declaration="yes"/>
  
  <!-- 
Convert Zotero RDF as colums to feed a database
    cts     TEXT UNIQUE NOT NULL, 
    title                   TEXT, 
    date                    TEXT,  
    authors                 TEXT,  Surname, Given Name; Smith, Jane
    editors                 TEXT,  Surname, Given Name; Smith, Jane
    language                TEXT,
    book_title              TEXT,
    volume                  TEXT,
    series                  TEXT,
    page_start              TEXT,
    page_end                TEXT,
    publisher               TEXT,

   -->
  
  
  
  <xsl:template match="/">
    <xsl:text>cts&#9;title&#9;date&#9;authors&#9;editors&#9;language&#9;book_title&#9;volume&#9;series&#9;page_start&#9;page_end&#9;publisher&#10;</xsl:text>
  </xsl:template>


  <xsl:template match="bib:BookSection | bib:Book" mode="meta">
    <xsl:text>&#10;</xsl:text>
  </xsl:template>

  <xsl:template match="bib:authors | bib:editors" mode="meta">
    <xsl:for-each select="rdf:Seq/rdf:li">
      <xsl:apply-templates select="*"/>
      <xsl:if test="position() != last()">; </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="foaf:Person" mode="meta">
    <xsl:value-of select="normalize-space(foaf:surname)"/>
    <xsl:if test="foaf:givenName">
      <xsl:text>, </xsl:text>
      <xsl:value-of select="normalize-space(foaf:givenName)"/>
    </xsl:if>
  </xsl:template>
  

</xsl:transform>