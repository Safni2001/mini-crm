@component('mail::message')
# New Company Created

Hello {{ $notifiable->name }},

A new company has been added to the CRM system.

## Company Details

**Name:** {{ $company->name }}  
**Email:** {{ $company->email ?? 'Not provided' }}  
**Website:** {{ $company->website ?? 'Not provided' }}  
**Created by:** {{ $createdBy->name }}  
**Created at:** {{ $company->created_at->format('F j, Y \a\t g:i A') }}

@if($company->logo)
## Company Logo
The company has uploaded a logo which you can view in the system.
@endif

@component('mail::button', ['url' => url('/companies/' . $company->id)])
View Company Details
@endcomponent

@component('mail::panel')
**Recent Activity Summary:**
- Total Companies: {{ \App\Models\Company::count() }}
- Total Employees: {{ \App\Models\Employee::count() }}
- Companies added today: {{ \App\Models\Company::whereDate('created_at', today())->count() }}
@endcomponent

Thanks for using {{ config('app.name') }}!

@component('mail::subcopy')
If you're having trouble clicking the "View Company Details" button, copy and paste the URL below into your web browser:
{{ url('/companies/' . $company->id) }}
@endcomponent
@endcomponent