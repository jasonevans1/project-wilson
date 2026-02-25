# Data Model: Service Record Tracking

**Branch**: `004-service-record-tracking` | **Date**: 2026-02-23

## New Table: `service_records`

### Migration Blueprint

```php
Schema::create('service_records', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
    $table->foreignIdFor(\App\Models\Asset::class)->constrained()->cascadeOnDelete();
    $table->date('service_date');
    $table->string('service_type');
    $table->text('description');
    $table->string('provider_name')->nullable();
    $table->decimal('cost', 10, 2)->nullable();
    $table->boolean('under_warranty')->default(false);
    $table->date('warranty_expires_on')->nullable();
    $table->timestamps();
});
```

### Column Details

| Column              | Type                | Nullable | Default | Notes                                              |
|---------------------|---------------------|----------|---------|----------------------------------------------------|
| `id`                | bigint (PK)         | No       | —       | Auto-increment                                     |
| `user_id`           | bigint (FK)         | No       | —       | → `users.id`, cascade delete                       |
| `asset_id`          | bigint (FK)         | No       | —       | → `assets.id`, cascade delete                      |
| `service_date`      | date                | No       | —       | Required; may be past or future                    |
| `service_type`      | string              | No       | —       | Enum: `maintenance`, `repair`, `inspection`, `replacement` |
| `description`       | text                | No       | —       | Max 5,000 chars enforced at application layer      |
| `provider_name`     | string(255)         | Yes      | NULL    | Free-text service provider name                    |
| `cost`              | decimal(10,2)       | Yes      | NULL    | NULL = cost unknown; 0.00 = no cost incurred       |
| `under_warranty`    | boolean             | No       | false   | True if work was covered under warranty            |
| `warranty_expires_on` | date              | Yes      | NULL    | Only meaningful when `under_warranty = true`       |
| `created_at`        | timestamp           | Yes      | NULL    | Auto-managed by Eloquent                           |
| `updated_at`        | timestamp           | Yes      | NULL    | Auto-managed by Eloquent                           |

---

## New Enum: `App\Enums\ServiceType`

```php
enum ServiceType: string
{
    case Maintenance  = 'maintenance';
    case Repair       = 'repair';
    case Inspection   = 'inspection';
    case Replacement  = 'replacement';

    public function label(): string
    {
        return match($this) {
            self::Maintenance  => 'Maintenance',
            self::Repair       => 'Repair',
            self::Inspection   => 'Inspection',
            self::Replacement  => 'Replacement',
        };
    }
}
```

---

## New Model: `App\Models\ServiceRecord`

```php
class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'service_date',
        'service_type',
        'description',
        'provider_name',
        'cost',
        'under_warranty',
        'warranty_expires_on',
    ];

    protected function casts(): array
    {
        return [
            'service_date'       => 'date',
            'service_type'       => ServiceType::class,
            'cost'               => 'decimal:2',
            'under_warranty'     => 'boolean',
            'warranty_expires_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
```

---

## Relationship Additions

### `App\Models\Asset`

```php
public function serviceRecords(): HasMany
{
    return $this->hasMany(ServiceRecord::class);
}
```

### `App\Models\User`

```php
public function serviceRecords(): HasMany
{
    return $this->hasMany(ServiceRecord::class);
}
```

---

## New Concern: `App\Concerns\ServiceRecordValidationRules`

```php
trait ServiceRecordValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function serviceRecordRules(): array
    {
        return [
            'serviceDate'       => ['required', 'date'],
            'serviceType'       => ['required', Rule::enum(ServiceType::class)],
            'description'       => ['required', 'string', 'max:5000'],
            'providerName'      => ['nullable', 'string', 'max:255'],
            'cost'              => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'underWarranty'     => ['boolean'],
            'warrantyExpiresOn' => ['nullable', 'date', 'required_if:underWarranty,true'],
        ];
    }
}
```

---

## Entity Relationship Overview

```text
users
  └── has many ──► assets
                      └── has many ──► service_records ◄─── belongs to ── users
                      └── has many ──► maintenance_tasks
```

- `service_records.user_id` is denormalized from `assets.user_id` for efficient ownership lookups without joining through `assets`.
- `service_records.asset_id` links a record to its single asset (1:M). The column naming leaves room for a future `service_record_assets` pivot table if M:M is later needed.

---

## Factory Reference

```php
class ServiceRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'asset_id'           => Asset::factory(),
            'service_date'       => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'service_type'       => fake()->randomElement(ServiceType::cases())->value,
            'description'        => fake()->paragraph(),
            'provider_name'      => fake()->optional()->company(),
            'cost'               => fake()->optional()->randomFloat(2, 0, 5000),
            'under_warranty'     => false,
            'warranty_expires_on' => null,
        ];
    }

    public function underWarranty(): static
    {
        return $this->state([
            'under_warranty'      => true,
            'warranty_expires_on' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
        ]);
    }
}
```
