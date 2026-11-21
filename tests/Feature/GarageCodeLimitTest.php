<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GarageCodeLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_type_can_use_999_distinct_codes(): void
    {
        foreach (['sepeda' => 'S', 'mobil' => 'C', 'ev' => 'E'] as $type => $prefix) {
            DB::table('garages')->insert(array_map(
                fn (int $number): array => [
                    'code' => sprintf('%s%03d', $prefix, $number),
                    'type' => $type,
                    'daily_rate' => 10000,
                ],
                range(1, 999),
            ));

            $this->assertSame(999, DB::table('garages')->where('type', $type)->count());
        }
    }

    #[DataProvider('invalidCodes')]
    public function test_invalid_codes_are_rejected(string $code): void
    {
        $this->expectException(QueryException::class);

        DB::table('garages')->insert(['code' => $code, 'type' => 'mobil', 'daily_rate' => 10000]);
    }

    public static function invalidCodes(): array
    {
        return [
            'zero' => ['C000'],
            'over limit' => ['C1000'],
            'wrong type' => ['S001'],
            'old car prefix' => ['M001'],
            'lowercase' => ['c001'],
        ];
    }

    public function test_existing_code_must_match_the_updated_type(): void
    {
        DB::table('garages')->insert(['code' => 'E001', 'type' => 'ev', 'daily_rate' => 10000]);

        $this->expectException(QueryException::class);

        DB::table('garages')->where('code', 'E001')->update(['type' => 'mobil']);
    }
}
