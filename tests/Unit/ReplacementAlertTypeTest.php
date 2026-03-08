<?php

use App\Enums\ReplacementAlertType;

it('has correct string values for each case', function () {
    expect(ReplacementAlertType::TwoYear->value)->toBe('two_year');
    expect(ReplacementAlertType::OneYear->value)->toBe('one_year');
    expect(ReplacementAlertType::Overdue->value)->toBe('overdue');
});

it('returns human-readable labels for each case', function () {
    expect(ReplacementAlertType::TwoYear->label())->toBeString()->not->toBeEmpty();
    expect(ReplacementAlertType::OneYear->label())->toBeString()->not->toBeEmpty();
    expect(ReplacementAlertType::Overdue->label())->toBeString()->not->toBeEmpty();
});
