<x-mail::message>
# Maintenance Reminder

Hello {{ $userName }},

This is a reminder that the following maintenance task is coming up soon.

**Asset:** {{ $assetName }}
**Task:** {{ $taskName }}
**Due Date:** {{ $dueDate->format('M j, Y') }}

@if ($taskDescription)
**Description:** {{ $taskDescription }}
@endif

@if ($reminderType === \App\Enums\ReminderType::OneDay)
This task is due **tomorrow**. Please take action soon.
@elseif ($reminderType === \App\Enums\ReminderType::SevenDay)
This task is due in **7 days**.
@else
This task is coming up in **30 days**.
@endif

<x-mail::button :url="$taskUrl">
View Maintenance Tasks
</x-mail::button>

**Need more time?** Snooze this reminder:
[Snooze 3 days]({{ $snooze3Url }}) · [Snooze 7 days]({{ $snooze7Url }})

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
