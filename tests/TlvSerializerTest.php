<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\data_element\DataElement;
use alcamo\dom\schema\SchemaFactory;
use alcamo\rdf_literal\{NonNegativeIntegerLiteral, StringLiteral};
use alcamo\rdf_literal_serializer\{
    AbstractSerializer,
    NonNegativeIntegerSerializer,
    StringSerializer
};

class TlvSerializerTest extends TestCase
{
    public const XSD_NS = AbstractTlvDataElement::XSD_NS;

    /**
     * @dataProvider serializeLengthProvider
     */
    public function testSerializeLength($length, $expectedHexResult): void
    {
        $serializer = new TlvSerializer();

        $this->assertSame(
            hex2bin($expectedHexResult),
            $serializer->serializeLength($length)
        );
    }

    public function serializeLengthProvider(): array
    {
        return [
            [ -1, '80' ],
            [ 0, '00' ],
            [ 1, '01' ],
            [ 127, '7F' ],
            [ 128, '8180' ],
            [ 435, '8201B3' ],
            [ 197001, '83030189' ],
            [ 19700101, '84012C9985' ]
        ];
    }

    public function testSerializeTlvDeInstances(): void
    {
        $deInstancesData = [
            [
                'C0',
                self::XSD_NS . ' nonNegativeInteger',
                [
                    'class' => NonNegativeIntegerSerializer::class,
                    'encoding' => 'BCD',
                    'lengthRange' => [ 3, 10 ]
                ],
                new NonNegativeIntegerLiteral(42)
            ],
            [
                'C1',
                self::XSD_NS . ' token',
                [
                    'class' => StringSerializer::class,
                    'lengthRange' => [ 6, 12 ]
                ],
                new StringLiteral('Foo')
            ]
        ];

        $expectedHexData = 'C0020042'
            . 'C106466F6F202020';

        $schema = (new SchemaFactory())->getMainSchema();

        $serializer = new TlvSerializer();

        $deInstances = [];

        foreach ($deInstancesData as $deInstanceData) {
            [ $tag, $typeXName, $serializerProps, $literal ] = $deInstanceData;

            $deInstances[] = PrimitiveTlvDeInstance::newFromLiteral(
                new PrimitiveTlvDataElement(
                    Tag::newFromHex($tag),
                    new DataElement($schema->getGlobalType($typeXName)),
                    AbstractSerializer::createFromProps($serializerProps)
                ),
                $literal
            );
        }

        $this->assertSame(
            hex2bin($expectedHexData),
            $serializer->serializeTlvDeInstances($deInstances)
        );
    }
}
