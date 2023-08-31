<?php

namespace Fee1dead\EventXmlParser\Interfaces;

use XMLReader;

interface XmlParserInterface
{
    public function subscribeOnElementStart(string $tagName, callable $handler) : self;
    public function subscribeOnElementEnd(string $tagName, callable $handler) : self;
    public function parse() : void;
}