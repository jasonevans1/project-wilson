<?php

use App\Enums\ServiceType;

test('ServiceType Maintenance label returns Maintenance', function () {
    expect(ServiceType::Maintenance->label())->toBe('Maintenance');
});

test('ServiceType Repair label returns Repair', function () {
    expect(ServiceType::Repair->label())->toBe('Repair');
});

test('ServiceType Inspection label returns Inspection', function () {
    expect(ServiceType::Inspection->label())->toBe('Inspection');
});

test('ServiceType Replacement label returns Replacement', function () {
    expect(ServiceType::Replacement->label())->toBe('Replacement');
});

test('all ServiceType cases have a non-empty label', function () {
    foreach (ServiceType::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});
