<?php
/**
 * Silk Framework
 *
 * @caetgory    Silk
 * @package     Silk
 * @author      caixw <http://www.caixw.com>
 * @copyright   Copyright (C) 2010, http://www.caixw.com
 * @license     NewBSD License
 */

/**
 * 用于产生sitemap.xml文件。
 */
final class Silk_SitemapFile implements Countable
{
    private $_xml;
    private $_count = 0;

    /**
     * 构造函数。
     *
     * @param string $path 保存的路径。
     * @param string $version XML文件的版本。
     * @param string $encoding XML的编码。
     * @param string $xsl
     */
    public function __construct($path, $version = '1.0', $encoding='utf-8', $xsl = '')
    {
        $this->_xml = new XMLWriter();
        $this->_xml->openURI($path);
        $this->_xml->setIndent(true);
        $this->_xml->setIndentString(' ');

        $this->_xml->startDocument($version, $encoding);
        $this->_xml->writePI('xml-stylesheet', 'type="text/xsl" href="' . $xsl . '"');

        $this->_xml->startElement('urlset');
        $this->_xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * 添加一条记录。
     *
     * @param string $loc URL。
     * @param string $changefreq 可选值为：always,hourly,daily,weekly,monthly,yearly,never。
     * @param string $priority 优先级别：0.1-1.0之间的值。
     * @param int $lastmod 最后更新时间的时间戳。
     * @return void
     */
    public function add($loc, $changefreq, $priority, $lastmod)
    {
        $this->_xml->startElement('url');
        $this->_xml->writeElement('loc', $loc);
        $this->_xml->writeElement('lastmod', date(DATE_W3C, $lastmod));
        $this->_xml->writeElement('changefreq', $changefreq);
        $this->_xml->writeElement('priority', $priority);
        $this->_xml->endElement(); // end url
        $this->_count++;
    }

    /**
     * 保存此文件。
     *
     * @return void
     */
    public function save()
    {
        $this->_xml->endElement(); // end urlset
        $this->_xml->endDocument();
        $this->_xml->flush();
        $this->_count = 0;
    }

    /**
     * 此文件的记录数目。
     *
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }
}


/**
 * 产生一系列sitemap的索引文件。
 *
 * @category    Silk
 * @package     Silk
 */
final class Silk_Sitemap
{
    private $_dir; // sitemap文件存放的路径。
    private $_baseurl; // sitemap文件的URL路径。
    private $_maxCount; // 每个sitemap文件的最大记录个数。
    private $_createIndexFile = false;
    private $_xsl;

    private $_sitemap = null; // 当前的Silk_SitemapFile实例
    private $_indexSitemap; // sitemap_index文件的实例。
    private $_index = 0; // 统计sitemap的文件数。

    /**
     * 构造函数。
     *
     * @param string $dir sitemap文件存放的目录。
     * @param string $baseurl sitemap文件所在目录的URL路径。
     * @param boolean $indexFile 是否创建索引。
     * @param string $maxCount 每个sitemap文件的最大记录数目。
     * @param string $xsl sitemap文件的XSL文件的位置。
     */
    public function __construct($dir, $baseurl, $createIndexFile = false, $maxCount = 1000, $xsl = '')
    {
        /* 尝试创建目录 */
        if(!file_exists($dir) && !mkdir($dir, 0777, true))
        {   throw new exception('目录不存在，并且不能创建！');  }

        if(!is_writeable($dir)) // chmod不一定能行。
        {   throw new exception('目录不可写');   }

        $this->_dir = $dir . DIRECTORY_SEPARATOR; // 保证以／结尾。
        $this->_baseurl = trim($baseurl, '/') . '/';  // 保证以／结尾。
        $this->_maxCount = $maxCount;
        $this->_createIndexFile = $createIndexFile;
        $this->_xsl = $xsl;

        if($this->_createIndexFile)
        {
            $this->_indexSitemap = new XMLWriter();
            $this->_indexSitemap->openURI($this->_dir . 'sitemap.xml');
            $this->_indexSitemap->setIndent(true);
            $this->_indexSitemap->setIndentString(' ');
            $this->_indexSitemap->startDocument();
            $this->_indexSitemap->startElement('sitemapindex');
            $this->_indexSitemap->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        }
    }

    /**
     * 保存内容。
     *
     * 每个sitemap文件会自动保存，此操作只是保存sitemap_index文件。
     *
     * @return void
     */
    public function save()
    {
        $this->_sitemap->save();

        if($this->_createIndexFile)
        {
            $this->_indexSitemap->endElement(); // end sitemapindex
            $this->_indexSitemap->endDocument();
            $this->_indexSitemap->flush();
        }
    }

    /**
     * 添加一条记录。
     *
     * @sa {@link Silk_SitemapFile::add()}
     */
    public function add($loc, $changefreq, $priority, $lastmod)
    {
        if(null === $this->_sitemap)
        {   $this->_initSitemap();  }
        elseif(count($this->_sitemap) >= $this->_maxCount && $this->_createIndexFile)
        {
            $this->_sitemap->save();
            $this->_initSitemap();
        }

        $this->_sitemap->add($loc, $changefreq, $priority, $lastmod);
    }

    /**
     * 创建一个新的sitemapfile实例，并更新索引文件的内容。
     *
     * @return void
     */
    protected function _initSitemap()
    {
        if($this->_createIndexFile)
        {
            $filename = 'sitemap_' . ++$this->_index . '.xml';
            $this->_sitemap = new Silk_SitemapFile($this->_dir . $filename,'1.0', 'utf-8', $this->_xsl);

            $this->_indexSitemap->startElement('sitemap');
            $this->_indexSitemap->writeElement('loc', $this->_baseurl . $filename);
            $this->_indexSitemap->writeElement('lastmod', date(DATE_W3C));
            $this->_indexSitemap->endElement(); // end sitemap
        }else
        {
            $this->_sitemap = new Silk_SitemapFile($this->_dir . 'sitemap.xml', '1.0', 'utf-8', $this->_xsl);
        }
    }
}

