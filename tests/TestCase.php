<?php

namespace Tests;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    public function setUp():void
    {
        ini_set('memory_limit', '500M');
        parent::setUp();
    }

    public function createApplication()
    {
        global $app;

        if (is_null($app)) {
            $app = require __DIR__.'/../bootstrap/app.php';
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        }

        return $app;
    }

    /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function assertArraysEqual(array $a, array $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            $this->assertTrue(false);
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);
    }

    public function assertJsonStructure(string $json, array $structure)
    {
        $this->assertTrue(Helpers::isJson($json));

        $responseData = Helpers::toArray($json);

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                $this->assertInternalType('array', $responseData);
                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                $this->assertArrayHasKey($key, $responseData);
                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                $this->assertArrayHasKey($value, $responseData);
            }
        }
    }

    public function faker($min, $val = null)
    {
        $faker = app(\Faker\Generator::class);

        regenerate:
        if (is_string($min)) {
            $result = $faker->$min;
        } else {
            $result = $faker->$val;
            if (strlen($result) < $min) {
                $diff = $min - strlen($result);
                $result .= str_repeat("a", $diff);
            }
        }

        return $result;
    }
}
