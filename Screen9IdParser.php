<?php

class Screen9IdParser
{

    var $xml_parser;

    var $tag_handlers = array( 'object' => array('startObject', 'endObject'),
                               'param'  => array('startParam', 'endParam'),
                               'embed'  => array('startEmbed', 'endEmbed') );

    var $objectId;

    var $objectClassId;

    var $objectWidth;

    var $objectHeight;

    var $params = array();

    var $embedSrc;

    var $embedName;

    var $embedType;

    var $embedAllowScriptAccess;

    var $embedAllowFullscreen;

    var $embedWmode;

    var $embedWidth;

    var $embedHeight;

    var $embedFlashvars;

    public function __construct() {
        $this->xml_parser = xml_parser_create();
        xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object( $this->xml_parser, $this );
        xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    }

    public function __destruct() {
        xml_parser_free( $this->xml_parser );
    }

    public function startElement($parser, $name, $attrs) 
    {
        if ( !isset($this->tag_handlers[$name]) ) {
            throw new Exception("Invalid tag: [$name]");
        }

        $handler = $this->tag_handlers[$name];
        $this->{$handler[0]}($attrs);
    }

    public function endElement($parser, $name) 
    {
        if ( !isset($this->tag_handlers[$name]) ) {
            throw new Exception("Invalid tag: [$name]");
        }

        $this->{$this->tag_handlers[$name][1]}();
    }

    public function startObject($attrs)
    {
        if ($this->objectId != null) {
            throw new Exception("Unexpected <object>.");
        }
        $this->objectId = self::attr($attrs['id']);
        $this->objectClassId = self::attr($attrs['classid']);
        $this->width = self::attr($attrs['width']);
        $this->height = self::attr($attrs['height']);
    }

    public function endObject()
    {
    }

    public function startParam($attrs)
    {
        if ($this->objectId == null) {
            throw new Exception("Unexpected <param>.");
        }

        if ( ! isset($attrs['name']) ) {
            throw new Exception("Missing name on param tag!");
        }

        if ( ! isset($attrs['value']) ) {
            throw new Exception("Missing value on param tag!");
        }

        $this->params[$attrs['name']] = self::attr($attrs['value']);
    }

    public function endParam()
    {
    }

    private static function attr($attr)
    {
        if (preg_match( '/[<>"&]/', $attr) ) {
            throw new Exception("Invalid attribute value: " . htmlspecialchars($attr));
        }
        return $attr;
    }

    public function startEmbed($attrs)
    {
        if ($this->objectId == null) {
            throw new Exception("Unexpected <embed>.");
        }

        if ( ! isset($attrs['src']) ) {
            throw new Exception("Missing src attribute on embed tag!");
        }

        if ( ! isset($attrs['name']) ) {
            throw new Exception("Missing name attribute on embed tag!");
        }

        if ( ! isset($attrs['flashvars']) ) {
            throw new Exception("missing flashvars attribute on embed tag!");
        }

        $this->embedSrc = self::attr($attrs['src']);
        $this->embedName = self::attr($attrs['name']);
        $this->embedType = self::attr($attrs['type']);
        $this->embedFlashvars = self::attr($attrs['flashvars']);
        $this->embedAllowScriptAccess = self::attr($attrs['allowscriptaccess']);
        $this->embedAllowFullscreen = self::attr($attrs['allowfullscreen']);
        $this->embedWmode = self::attr($attrs['wmode']);
        $this->embedWidth = self::attr($attrs['width']);
        $this->embedHeight = self::attr($attrs['height']);
    }

    public function endEmbed()
    {
    }

    public function parse( $id )
    {
        try {
            if ( !xml_parse( $this->xml_parser, '<?xml version="1.0" ?>' . $id, true ) ) {
                return false;
            }
        } catch ( Exception $e ) {
            return false;
        }

        if ( !isset($this->embedSrc) ) {
            return false;
        }

        if ( !isset($this->embedType) ) {
            $this->embedType = 'application/x-shockwave-flash';
        }

        if ( !isset($this->embedAllowScriptAccess) ) {
            $this->embedAllowScriptAccess = 'always';
        }

        if ( !isset($this->embedAllowFullscreen) ) {
            $this->embedAllowFullscreen = 'true';
        }

        if ( !isset($this->embedWmode) ) {
            $this->embedWmode = 'transparent';
        }

        return true;
    }

    public function setWidth( $width )
    {
        $this->objectWidth = $width;
        $this->embedWidth = $width;
    }

    public function setHeight( $height )
    {
        $this->objectHeight = $height;
        $this->embedHeight = $height;
    }

    private static function paramTag( $name, $value )
    {
        return "<param name=\"$name\" value=\"$value\"></param>";
    }

    public function toString()
    {
        $result = "<object id=\"{$this->objectId}\" classid=\"{$this->objectClassId}\" width=\"{$this->objectWidth}\" height=\"{$this->objectHeight}\" >";

        foreach ( $this->params as $name => $value ) {
            $result .= self::paramTag($name, $value);
        }

        $result .= "<embed src=\"{$this->embedSrc}\" name=\"{$this->embedName}\" type=\"{$this->embedType}\" allowscriptaccess=\"{$this->embedAllowScriptAccess}\" allowfullscreen=\"{$this->embedAllowFullscreen}\" wmode=\"{$this->embedWmode}\" width=\"{$this->embedWidth}\" height=\"{$this->embedHeight}\" flashvars=\"{$this->embedFlashvars}\" >";

        return $result . '</embed></object>';
    }
}
