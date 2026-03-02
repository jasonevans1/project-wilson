<x-mail::message>
# Upcoming Maintenance Tasks

Hello {{ $userName }},

You have **{{ $reminderCount }} upcoming maintenance {{ $reminderCount === 1 ? 'task' : 'tasks' }}** that require attention.

<x-mail::table>
| Asset | Task | Due Date |
| ----- | ---- | -------- |
@foreach ($reminders as $reminder)
| {{ $reminder->occurrence->task->asset->name }} | {{ $reminder->occurrence->task->name }} | {{ $reminder->occurrence->due_date->format('M j, Y') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$taskUrl">
View All Maintenance Tasks
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
