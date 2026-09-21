<?php

use jeremykenedy\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

class UuidCompatibilityTest extends TestCase
{
    public function testDefaultGenerationRemainsVersionOne()
    {
        $uuid = Uuid::generate();

        $this->assertSame(1, $uuid->version);
        $this->assertSame(1, $uuid->variant);
        $this->assertSame(16, strlen($uuid->bytes));
        $this->assertSame(1, hexdec(substr($uuid->node, 0, 2)) & 1);
        $this->assertLessThan(2, abs(microtime(true) - $uuid->time));
    }

    public function testNameBasedGenerationMatchesPublishedVectors()
    {
        $this->assertSame('3d813cbb-47fb-32ba-91df-831e1593ac29', (string) Uuid::generate(3, 'www.widgets.com', Uuid::NS_DNS));
        $this->assertSame('21f7f8de-8051-5b89-8680-0195ef798b6a', (string) Uuid::generate(5, 'www.widgets.com', Uuid::NS_DNS));
    }

    public function testNamespacesRemainUnchanged()
    {
        $this->assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', Uuid::NS_DNS);
        $this->assertSame('6ba7b811-9dad-11d1-80b4-00c04fd430c8', Uuid::NS_URL);
        $this->assertSame('6ba7b812-9dad-11d1-80b4-00c04fd430c8', Uuid::NS_OID);
        $this->assertSame('6ba7b814-9dad-11d1-80b4-00c04fd430c8', Uuid::NS_X500);
    }

    public function testNameBasedGenerationAcceptsEveryNamespaceRepresentation()
    {
        $namespace = Uuid::import(Uuid::NS_DNS);
        $formats = [$namespace, $namespace->bytes, $namespace->hex, $namespace->string, $namespace->urn, strtoupper($namespace->string)];

        foreach ([3, 5] as $version) {
            foreach ($formats as $format) {
                $this->assertSame((string) Uuid::generate($version, 'example.com', Uuid::NS_DNS), (string) Uuid::generate($version, 'example.com', $format));
            }
        }
    }

    public function testImportPreservesAcceptedFormats()
    {
        $string = 'f81d4fae-7dec-11d0-a765-00a0c91e6bf6';
        $bytes = hex2bin(str_replace('-', '', $string));
        $formats = [$string, strtoupper($string), str_replace('-', '', $string), '{'.$string.'}', 'urn:uuid:'.$string, 'URN:UUID:'.strtoupper($string), $bytes, Uuid::import($string)];

        foreach ($formats as $format) {
            $uuid = Uuid::import($format);
            $this->assertSame($string, (string) $uuid);
            $this->assertSame($bytes, $uuid->bytes);
            $this->assertSame(bin2hex($bytes), $uuid->hex);
            $this->assertSame('urn:uuid:'.$string, $uuid->urn);
            $this->assertTrue(Uuid::validate($format));
            $this->assertTrue(Uuid::compare($string, $format));
        }
    }

    public function testStringableValuesRemainAccepted()
    {
        $value = new class {
            public function __toString()
            {
                return Uuid::NS_DNS;
            }
        };

        $this->assertSame(Uuid::NS_DNS, (string) Uuid::import($value));
        $this->assertTrue(Uuid::validate($value));
    }

    public function testNilAndMaximumValuesRemainValid()
    {
        foreach (['00000000-0000-0000-0000-000000000000', 'ffffffff-ffff-ffff-ffff-ffffffffffff'] as $value) {
            $this->assertTrue(Uuid::validate($value));
            $this->assertSame($value, (string) Uuid::import($value));
        }
    }

    public function testValidationRemainsPermissiveAboutVersionAndVariant()
    {
        $this->assertTrue(Uuid::validate('00000000-0000-f000-0000-000000000000'));
        $this->assertTrue(Uuid::validate('abcdefghijklmnop'));
    }

    public function testInvalidValuesReturnFalseWithoutWarnings()
    {
        foreach ([null, '', 'invalid', '1234', [], ['uuid' => Uuid::NS_DNS], new stdClass, false, true, 42] as $value) {
            $this->assertFalse(Uuid::validate($value));
        }
    }

    public function testInvalidImportAndComparisonKeepTheirLegacyResults()
    {
        $uuid = Uuid::import('invalid');

        $this->assertNull($uuid->bytes);
        $this->assertSame('----', $uuid->string);
        $this->assertTrue(Uuid::compare('invalid', 'also invalid'));
        $this->assertFalse(Uuid::compare('invalid', Uuid::NS_DNS));
    }

    public function testNodeAcceptsHexadecimalAndBinaryForms()
    {
        foreach (['00:11:22:33:44:55', '00-11-22-33-44-55', '001122334455', hex2bin('001122334455')] as $node) {
            $this->assertSame('001122334455', Uuid::generate(1, $node)->node);
        }
    }

    public function testInvalidNodeStillFallsBackToRandomMulticastNode()
    {
        foreach ([null, '', 'invalid', []] as $node) {
            $uuid = Uuid::generate(1, $node);
            $this->assertSame(12, strlen($uuid->node));
            $this->assertSame(1, hexdec(substr($uuid->node, 0, 2)) & 1);
        }
    }

    public function testOtherVersionsHaveNoTimeOrNode()
    {
        foreach ([3, 4, 5] as $version) {
            $uuid = Uuid::generate($version, 'example.com', Uuid::NS_DNS);
            $this->assertNull($uuid->time);
            $this->assertNull($uuid->node);
        }
    }

    public function testVariantExtractionCoversAllVariants()
    {
        foreach (['00' => 0, '80' => 1, 'c0' => 2, 'e0' => 3] as $byte => $variant) {
            $uuid = Uuid::import('00000000-0000-4000-'.$byte.'00-000000000000');
            $this->assertSame($variant, $uuid->variant);
        }
    }

    public function testRandomGenerationSetsVersionAndVariantBits()
    {
        $values = [];

        for ($i = 0; $i < 256; $i++) {
            $uuid = Uuid::generate(4);
            $this->assertSame(16, strlen($uuid->bytes));
            $this->assertSame(4, $uuid->version);
            $this->assertSame(1, $uuid->variant);
            $values[] = (string) $uuid;
        }

        $this->assertCount(256, array_unique($values));
    }

    public function testRandomBytesReturnsRequestedLength()
    {
        foreach ([1, 2, 6, 16, 32] as $length) {
            $this->assertSame($length, strlen(Uuid::randomBytes($length)));
        }
    }

    public function testVersionCanStillBePassedAsAString()
    {
        $this->assertSame(4, Uuid::generate('4')->version);
    }

    public function testVersionTwoKeepsItsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Version 2 is unsupported.');

        Uuid::generate(2);
    }

    public function testUnsupportedVersionsKeepTheirException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Selected version is invalid or unsupported.');

        Uuid::generate(7);
    }

    public function testNameBasedGenerationRequiresAName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A name-string is required for Version 3 or 5 UUIDs.');

        Uuid::generate(3, '', Uuid::NS_DNS);
    }

    public function testNameBasedGenerationRequiresANamespace()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A binary namespace is required for Version 3 or 5 UUIDs.');

        Uuid::generate(5, 'example.com');
    }

    public function testPublicPropertiesAndSerializationStayCompatible()
    {
        $uuid = Uuid::import(Uuid::NS_DNS);
        $this->assertSame(['bytes' => $uuid->bytes, 'string' => Uuid::NS_DNS], get_object_vars($uuid));
        $this->assertSame($uuid->bytes, unserialize(serialize($uuid))->bytes);
        $this->assertSame(Uuid::NS_DNS, (string) unserialize(serialize($uuid)));

        $uuid->string = 'custom';
        $uuid->bytes = str_repeat('x', 16);
        $this->assertSame('custom', (string) $uuid);
        $this->assertSame(bin2hex(str_repeat('x', 16)), $uuid->hex);
        $this->assertNull($uuid->unknown);
    }

    public function testSubclassFactoriesKeepLateStaticBinding()
    {
        $prototype = new class (hex2bin(str_replace('-', '', Uuid::NS_DNS))) extends Uuid {
            public function __construct($bytes)
            {
                parent::__construct($bytes);
            }
        };
        $class = get_class($prototype);

        $this->assertInstanceOf($class, $class::generate(4));
        $this->assertInstanceOf($class, $class::import(Uuid::NS_DNS));
    }

    public function testPublicOperationsDoNotEmitPhpWarningsOrDeprecations()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            foreach ([1, 3, 4, 5] as $version) {
                $uuid = Uuid::generate($version, $version === 1 ? null : 'example.com', Uuid::NS_DNS);
                $this->assertTrue(Uuid::validate($uuid));
                $this->assertTrue(Uuid::compare($uuid, $uuid->urn));
            }

            foreach ([null, '', [], new stdClass] as $value) {
                $this->assertFalse(Uuid::validate($value));
            }
        } finally {
            restore_error_handler();
        }
    }
}
