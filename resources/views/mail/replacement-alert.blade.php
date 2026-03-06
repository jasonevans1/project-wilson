<x-mail::message>
# {{ $asset->name }}

Hello,

@if ($alertType === \App\Enums\ReplacementAlertType::Overdue)
This asset is **past its expected replacement date** by {{ $yearsRemaining }} {{ $yearsRemaining == 1 ? 'year' : 'years' }}.
@elseif ($alertType === \App\Enums\ReplacementAlertType::OneYear)
This asset is approaching its expected replacement date. It is due for replacement in approximately **{{ $yearsRemaining }} {{ $yearsRemaining == 1 ? 'year' : 'years' }}**.
@else
This asset is approaching its expected replacement date. It is due for replacement in approximately **{{ $yearsRemaining }} {{ $yearsRemaining == 1 ? 'year' : 'years' }}**.
@endif

**Expected Replacement Date:** {{ $replacementDate->format('F Y') }}

<x-mail::button :url="$dashboardUrl">
View Replacement Tracking
</x-mail::button>

@if ($dismissUrl)
If you have already replaced this asset or want to dismiss this alert, [click here to dismiss]({{ $dismissUrl }}).
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
