<?php

use App\Enums\ReminderType;

test('ReminderType has exactly three cases', function () {
    expect(ReminderType::cases())->toHaveCount(3);
});

test('ReminderType enum values are correct', function (ReminderType $type, string $value) {
    expect($type->value)->toBe($value);
})->with([
    'ThirtyDay' => [ReminderType::ThirtyDay, '30_day'],
    'SevenDay' => [ReminderType::SevenDay, '7_day'],
    'OneDay' => [ReminderType::OneDay, '1_day'],
]);

test('ReminderType labels are correct', function (ReminderType $type, string $label) {
    expect($type->label())->toBe($label);
})->with([
    'ThirtyDay' => [ReminderType::ThirtyDay, '30-Day Reminder'],
    'SevenDay' => [ReminderType::SevenDay, '7-Day Reminder'],
    'OneDay' => [ReminderType::OneDay, '1-Day Reminder'],
]);

test('ReminderType daysBeforeDue returns correct values', function (ReminderType $type, int $days) {
    expect($type->daysBeforeDue())->toBe($days);
})->with([
    'ThirtyDay' => [ReminderType::ThirtyDay, 30],
    'SevenDay' => [ReminderType::SevenDay, 7],
    'OneDay' => [ReminderType::OneDay, 1],
]);
