<?php

namespace Fee1dead\EventXmlParser;

use Fee1dead\EventXmlParser\Interfaces\XmlParserInterface;
use XMLReader;

class EventXmlParser implements XmlParserInterface
{
    protected $reader;
    protected $onElementStartSubscibers = [];
    protected $onElementEndSubscibers = [];

    public function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    public function subscribeOnElementStart(string $tagName, callable $handler) : self
    {
        $this->onElementStartSubscibers[$tagName] = $handler;

        return $this;
    }

    public function subscribeOnElementEnd(string $tagName, callable $handler) : self
    {
        $this->onElementEndSubscibers[$tagName] = $handler;

        return $this;
    }

    public function parse() : void
    {
        $endParsing = false;

        while(!$endParsing && $this->reader->read()) {
            $tagName = $this->reader->localName;

            switch($this->reader->nodeType) {
                case XMLReader::ELEMENT: {
                    if(!$this->elementStartEvent($tagName)) $endParsing = true;
                    break;
                }
                case XMLREADER::END_ELEMENT: {
                    if(!$this->elementEndEvent($tagName)) $endParsing = true;
                    break;
                }
            }
        }
    }

    protected function elementStartEvent(string $tagName) : bool
    {
        if (array_key_exists($tagName, $this->onElementStartSubscibers)) {
            return $this->onElementStartSubscibers[$tagName]($this->reader);
        }

        return true;
    }

    protected function elementEndEvent(string $tagName) : bool
    {
        if (array_key_exists($tagName, $this->onElementEndSubscibers)) {
            return $this->onElementEndSubscibers[$tagName]($this->reader);
        }

        return true;
    }
}
