<?php

namespace Fee1dead\EventXmlParser;

use PHPUnit\Framework\TestCase;
use Fee1dead\EventXmlParser\EventXmlParser;
use XMLReader;

class EventXmlParserTests extends TestCase
{
    private function createReader()
    {
        $filepath = __DIR__ . '/base_parser_1.xml';

        $this->assertFileExists($filepath, 'Отсутвует тестовый xml base_parser_1.xml');

        $xml = new XMLReader();
        $xml->open($filepath);

        $this->assertInstanceOf(XMLReader::class, $xml);

        return new EventXmlParser($xml);
    }

    public function testSubscibtionOnStartTag()
    {

        $personsArr = [];

        $this->createReader()->subscribeOnElementStart('person', function($xmlreader) use (&$personsArr) {
            $person = [];
            $attrs = ['firstname', 'lastname', 'city', 'country', 'firstname2', 'lastname2', 'email'];

            foreach ($attrs as $attr) {
                $person[$attr] = $xmlreader->getAttribute($attr);
            }

            $personsArr[] = $person;

            return true;
        })
        ->parse();


        $this->assertCount(6, $personsArr);
        $this->assertContains([
            'firstname' => "Darlleen",
            'lastname' => "Kunin",
            'city' => "Zapopan",
            'country' => "Yemen",
            'firstname2' => "Adore",
            'lastname2' => "Andrel",
            'email' => "Adore.Andrel@yopmail.com"
        ], $personsArr);
        $this->assertContains([
            'firstname' => "Heddie",
            'lastname' => "Camden",
            'city' => "Chicago",
            'country' => "Cameroon",
            'firstname2' => "Adore",
            'lastname2' => "Andrel",
            'email' => "Adore.Andrel@yopmail.com"
        ], $personsArr);
    }

    public function testSubscibtionOnEndTag()
    {
        $randomArr = [];

        $this->createReader()->subscribeOnElementStart('random', function($xmlreader) use (&$randomArr) {
            $oneRandom = [];

            (new EventXmlParser($xmlreader))
                ->subscribeOnElementStart('random_float', function($xmlreader) use (&$oneRandom) {
                    $oneRandom['random_float'] = trim($xmlreader->readInnerXml());

                    return true;
                })
                ->subscribeOnElementStart('date', function($xmlreader) use (&$oneRandom) {
                    $oneRandom['date'] = trim($xmlreader->readInnerXml());

                    return true;
                })
                ->subscribeOnElementEnd('random', function($xmlreader) {
                    /**
                     * To stop reading the next items and return control
                     * to the top parser when element random is end
                     */
                    return false;
                })
                ->parse();

            $randomArr[] = $oneRandom;

            return true;
        })
        ->parse();


        $this->assertCount(8, $randomArr);
        $this->assertContains([], $randomArr);
        $this->assertContains(['random_float' => '7.307', 'date' => '1996-09-07'], $randomArr);
        $this->assertContains(['date' => '1982-03-12'], $randomArr);
        $this->assertContains(['random_float' => '65.403'], $randomArr);
    }
}
